<?php

require_once __DIR__ . '/vendor/autoload.php';

use ZktecoAgent\Config\AgentConfig;
use ZktecoAgent\AgentRunner;

// Cargar configuración
AgentConfig::load();

// Verificar que existe el archivo .env
if (!file_exists(__DIR__ . '/.env')) {
    echo "Error: Archivo .env no encontrado. Ejecute primero 'php install.php'\n";
    exit(1);
}

// Función para mostrar ayuda
function showHelp() {
    echo "Agente ZKTeco para Sistema de Papeletas Digitales\n";
    echo "Uso: php agent.php [comando]\n\n";
    echo "Comandos disponibles:\n";
    echo "  start          Iniciar el agente (por defecto)\n";
    echo "  status         Mostrar estado del agente\n";
    echo "  test           Probar conexiones\n";
    echo "  sync           Ejecutar sincronización manual\n";
    echo "  cycle          Ejecutar un solo ciclo\n";
    echo "  help           Mostrar esta ayuda\n\n";
}

// Obtener comando de argumentos
$command = $argv[1] ?? 'start';

// Crear instancia del runner
$agent = new AgentRunner();

try {
    switch ($command) {
        case 'start':
            echo "Iniciando agente ZKTeco...\n";
            $agent->start();
            break;

        case 'status':
            echo "Estado del agente:\n";
            $status = $agent->getStatus();
            echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            break;

        case 'test':
            echo "Probando conexiones...\n";
            testConnections();
            break;

        case 'sync':
            echo "Ejecutando sincronización manual...\n";
            $result = $agent->syncNow();
            echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            break;

        case 'cycle':
            echo "Ejecutando ciclo único...\n";
            $result = $agent->runSingleCycle();
            echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            break;

        case 'help':
        case '--help':
        case '-h':
            showHelp();
            break;

        default:
            echo "Comando desconocido: $command\n\n";
            showHelp();
            exit(1);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

function testConnections() {
    echo "Probando conexión con ZKTeco...\n";
    
    try {
        $zktecoService = new \ZktecoAgent\Services\ZktecoService(
            new \Monolog\Logger('test')
        );
        
        if ($zktecoService->connect()) {
            echo "✓ Conexión con ZKTeco exitosa\n";
            
            $deviceInfo = $zktecoService->getDeviceInfo();
            if (!empty($deviceInfo)) {
                echo "  - Plataforma: " . ($deviceInfo['platform'] ?? 'N/A') . "\n";
                echo "  - Versión: " . ($deviceInfo['firmware_version'] ?? 'N/A') . "\n";
                echo "  - Serie: " . ($deviceInfo['serial_number'] ?? 'N/A') . "\n";
                echo "  - Usuarios: " . ($deviceInfo['user_count'] ?? 'N/A') . "\n";
            }
            
            $zktecoService->disconnect();
        } else {
            echo "✗ Error al conectar con ZKTeco\n";
        }
    } catch (Exception $e) {
        echo "✗ Error en conexión ZKTeco: " . $e->getMessage() . "\n";
    }

    echo "\nProbando conexión con servidor...\n";
    
    try {
        $syncService = new \ZktecoAgent\Services\ServerSyncService(
            new \Monolog\Logger('test')
        );
        
        if ($syncService->checkServerConnection()) {
            echo "✓ Conexión con servidor exitosa\n";
            
            $serverStatus = $syncService->getServerStatus();
            echo "  - Estado: " . ($serverStatus['status'] ?? 'N/A') . "\n";
        } else {
            echo "✗ Error al conectar con servidor\n";
        }
    } catch (Exception $e) {
        echo "✗ Error en conexión servidor: " . $e->getMessage() . "\n";
    }
}