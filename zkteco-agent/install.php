<?php

echo "=== Instalador del Agente ZKTeco ===\n\n";

// Verificar requisitos de PHP
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    echo "Error: Se require PHP 8.1 o superior. Versión actual: " . PHP_VERSION . "\n";
    exit(1);
}

// Verificar extensiones requeridas
$requiredExtensions = ['sqlite3', 'curl', 'json', 'mbstring'];
$missingExtensions = [];

foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        $missingExtensions[] = $extension;
    }
}

if (!empty($missingExtensions)) {
    echo "Error: Faltan las siguientes extensiones de PHP:\n";
    foreach ($missingExtensions as $extension) {
        echo "  - $extension\n";
    }
    exit(1);
}

echo "✓ Requisitos de PHP verificados\n";

// Crear directorios necesarios
$directories = [
    'database',
    'logs',
    'vendor'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✓ Directorio '$dir' creado\n";
    }
}

// Verificar si composer está instalado
if (!file_exists('composer.phar') && !shell_exec('which composer')) {
    echo "Advertencia: Composer no encontrado. Descargando...\n";
    
    $composerInstaller = file_get_contents('https://getcomposer.org/installer');
    if ($composerInstaller) {
        file_put_contents('composer-setup.php', $composerInstaller);
        shell_exec('php composer-setup.php');
        unlink('composer-setup.php');
        echo "✓ Composer descargado\n";
    } else {
        echo "Error: No se pudo descargar Composer. Instálelo manualmente.\n";
        exit(1);
    }
}

// Configuración interactiva
echo "\n=== Configuración del Agente ===\n";

$config = [];

// ID del agente
$config['AGENT_ID'] = readline("ID del Agente (1-5) [1]: ") ?: '1';
if (!is_numeric($config['AGENT_ID']) || $config['AGENT_ID'] < 1 || $config['AGENT_ID'] > 5) {
    echo "Error: ID del agente debe ser un número entre 1 y 5\n";
    exit(1);
}

// Nombre del agente
$config['AGENT_NAME'] = readline("Nombre del Agente [Agente RRHH #{$config['AGENT_ID']}]: ") 
    ?: "Agente RRHH #{$config['AGENT_ID']}";

// Configuración ZKTeco
echo "\n--- Configuración del Dispositivo ZKTeco ---\n";
$config['ZKTECO_IP'] = readline("IP del dispositivo ZKTeco [192.168.1.100]: ") ?: '192.168.1.100';
$config['ZKTECO_PORT'] = readline("Puerto del dispositivo [4370]: ") ?: '4370';
$config['ZKTECO_PASSWORD'] = readline("Password del dispositivo [0]: ") ?: '0';

// Configuración del servidor
echo "\n--- Configuración del Servidor Laravel ---\n";
$config['SERVER_URL'] = readline("URL del servidor [http://localhost:8000]: ") ?: 'http://localhost:8000';

// Generar token API único
$config['API_TOKEN'] = bin2hex(random_bytes(32));

// Configuración de base de datos
$config['DB_PATH'] = './database/agent_' . $config['AGENT_ID'] . '.db';

// Configuración de logs
$config['LOG_FILE'] = './logs/agent_' . $config['AGENT_ID'] . '.log';

// Crear archivo .env
$envContent = '';
foreach ($config as $key => $value) {
    $envContent .= "$key=$value\n";
}

file_put_contents('.env', $envContent);
echo "✓ Archivo .env creado\n";

// Instalar dependencias
echo "\n=== Instalando dependencias ===\n";

$composerCommand = file_exists('composer.phar') ? 'php composer.phar' : 'composer';
$output = shell_exec("$composerCommand install --no-dev --optimize-autoloader 2>&1");

if ($output) {
    echo $output;
}

if (!is_dir('vendor')) {
    echo "Error: Las dependencias no se instalaron correctamente\n";
    exit(1);
}

echo "✓ Dependencias instaladas\n";

// Probar configuración
echo "\n=== Probando configuración ===\n";

// Incluir autoloader
require_once 'vendor/autoload.php';

// Cargar configuración
\ZktecoAgent\Config\AgentConfig::load();

// Probar base de datos
try {
    $db = \ZktecoAgent\Database\AgentDatabase::getInstance();
    echo "✓ Base de datos SQLite inicializada\n";
} catch (Exception $e) {
    echo "Error: No se pudo inicializar la base de datos: " . $e->getMessage() . "\n";
    exit(1);
}

// Crear script de servicio Windows (opcional)
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    createWindowsService($config['AGENT_ID']);
}

// Crear script de inicio Linux (opcional)
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    createLinuxService($config['AGENT_ID']);
}

echo "\n=== Instalación Completada ===\n";
echo "Configuración guardada en: .env\n";
echo "Base de datos: {$config['DB_PATH']}\n";
echo "Logs: {$config['LOG_FILE']}\n";
echo "\nPara iniciar el agente ejecute:\n";
echo "  php agent.php start\n";
echo "\nPara probar las conexiones:\n";
echo "  php agent.php test\n";

echo "\n¡IMPORTANTE!\n";
echo "1. Configure el token API '{$config['API_TOKEN']}' en el servidor Laravel\n";
echo "2. Asegúrese de que el dispositivo ZKTeco esté accesible en {$config['ZKTECO_IP']}:{$config['ZKTECO_PORT']}\n";
echo "3. Configure las rutas API en el servidor Laravel\n";

function createWindowsService($agentId) {
    $serviceName = "ZktecoAgent$agentId";
    $batContent = "@echo off\n";
    $batContent .= "cd /d \"" . __DIR__ . "\"\n";
    $batContent .= "php agent.php start\n";
    $batContent .= "pause\n";
    
    file_put_contents("start_agent_$agentId.bat", $batContent);
    echo "✓ Script de inicio Windows creado: start_agent_$agentId.bat\n";
}

function createLinuxService($agentId) {
    $serviceContent = "[Unit]\n";
    $serviceContent .= "Description=ZKTeco Agent $agentId\n";
    $serviceContent .= "After=network.target\n\n";
    $serviceContent .= "[Service]\n";
    $serviceContent .= "Type=simple\n";
    $serviceContent .= "User=www-data\n";
    $serviceContent .= "WorkingDirectory=" . __DIR__ . "\n";
    $serviceContent .= "ExecStart=/usr/bin/php " . __DIR__ . "/agent.php start\n";
    $serviceContent .= "Restart=always\n";
    $serviceContent .= "RestartSec=5\n\n";
    $serviceContent .= "[Install]\n";
    $serviceContent .= "WantedBy=multi-user.target\n";
    
    file_put_contents("zkteco-agent-$agentId.service", $serviceContent);
    echo "✓ Archivo de servicio systemd creado: zkteco-agent-$agentId.service\n";
    echo "  Para instalarlo: sudo cp zkteco-agent-$agentId.service /etc/systemd/system/\n";
    echo "  Para habilitarlo: sudo systemctl enable zkteco-agent-$agentId\n";
}