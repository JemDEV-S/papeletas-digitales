<?php

namespace ZktecoAgent;

use ZktecoAgent\Config\AgentConfig;
use ZktecoAgent\Services\ZktecoService;
use ZktecoAgent\Services\ServerSyncService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class AgentRunner
{
    private Logger $logger;
    private ZktecoService $zktecoService;
    private ServerSyncService $syncService;
    private bool $running = false;
    private int $cycleCount = 0;

    public function __construct()
    {
        $this->initializeLogger();
        $this->zktecoService = new ZktecoService($this->logger);
        $this->syncService = new ServerSyncService($this->logger);
        
        // Configurar manejadores de señales para parada elegante
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleShutdown']);
            pcntl_signal(SIGINT, [$this, 'handleShutdown']);
        }
    }

    private function initializeLogger(): void
    {
        $this->logger = new Logger('ZktecoAgent');
        
        $logFile = AgentConfig::get('logging.file');
        $logLevel = $this->getLogLevel(AgentConfig::get('logging.level'));
        
        // Crear directorio de logs si no existe
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Handler para archivo rotativo
        $fileHandler = new RotatingFileHandler($logFile, 0, $logLevel);
        $this->logger->pushHandler($fileHandler);

        // Handler para consola
        $consoleHandler = new StreamHandler('php://stdout', $logLevel);
        $this->logger->pushHandler($consoleHandler);
    }

    private function getLogLevel(string $level): int
    {
        return match(strtolower($level)) {
            'debug' => Logger::DEBUG,
            'info' => Logger::INFO,
            'warning', 'warn' => Logger::WARNING,
            'error' => Logger::ERROR,
            'critical' => Logger::CRITICAL,
            default => Logger::INFO,
        };
    }

    public function start(): void
    {
        $this->logger->info('Iniciando agente ZKTeco', [
            'agent_id' => AgentConfig::get('agent.id'),
            'agent_name' => AgentConfig::get('agent.name'),
            'zkteco_ip' => AgentConfig::get('zkteco.ip')
        ]);

        $this->running = true;

        // Registro inicial con el servidor
        if (!$this->syncService->registerAgentWithServer()) {
            $this->logger->warning('No se pudo registrar el agente con el servidor, continuando de todas formas...');
        }

        // Intentar conectar con el dispositivo ZKTeco
        if (!$this->zktecoService->connect()) {
            $this->logger->error('No se pudo conectar con el dispositivo ZKTeco');
            return;
        }

        // Sincronización inicial
        $this->performInitialSync();

        $this->logger->info('Agente iniciado correctamente, entrando en bucle principal...');

        // Bucle principal
        while ($this->running) {
            $this->runCycle();
            $this->sleep();
            
            // Procesar señales si está disponible
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
        }

        $this->shutdown();
    }

    private function performInitialSync(): void
    {
        $this->logger->info('Realizando sincronización inicial...');

        // Sincronizar empleados desde el servidor
        if ($this->syncService->syncEmployeesFromServer()) {
            // Sincronizar empleados al dispositivo
            $this->zktecoService->syncEmployeesToDevice();
        }

        // Procesar registros de asistencia existentes
        $this->zktecoService->processNewAttendanceRecords();

        $this->logger->info('Sincronización inicial completada');
    }

    private function runCycle(): void
    {
        $this->cycleCount++;
        $startTime = microtime(true);

        $this->logger->debug("Iniciando ciclo #{$this->cycleCount}");

        try {
            // 1. Verificar conexión con ZKTeco
            if (!$this->zktecoService->isConnected()) {
                $this->logger->warning('Conexión con ZKTeco perdida, reintentando...');
                if (!$this->zktecoService->connect()) {
                    $this->logger->error('No se pudo reconectar con ZKTeco');
                    return;
                }
            }

            // 2. Procesar nuevos registros de asistencia
            $newRecords = $this->zktecoService->processNewAttendanceRecords();
            
            if ($newRecords > 0) {
                $this->logger->info("Procesados $newRecords nuevos registros de asistencia");
            }

            // 3. Sincronizar eventos pendientes con el servidor
            if ($this->syncService->checkServerConnection()) {
                $this->syncService->sendAccessEventsToServer();
                
                // Enviar heartbeat cada 10 ciclos
                if ($this->cycleCount % 10 === 0) {
                    $this->syncService->sendHeartbeat();
                }
                
                // Sincronizar empleados cada 100 ciclos (aprox. cada 50 minutos)
                if ($this->cycleCount % 100 === 0) {
                    if ($this->syncService->syncEmployeesFromServer()) {
                        $this->zktecoService->syncEmployeesToDevice();
                    }
                }
            } else {
                $this->logger->warning('Servidor no disponible, saltando sincronización');
            }

        } catch (\Exception $e) {
            $this->logger->error('Error en ciclo de ejecución: ' . $e->getMessage(), [
                'cycle' => $this->cycleCount,
                'trace' => $e->getTraceAsString()
            ]);
        }

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $this->logger->debug("Ciclo #{$this->cycleCount} completado en {$executionTime}ms");
    }

    private function sleep(): void
    {
        $interval = AgentConfig::get('sync.interval');
        sleep($interval);
    }

    public function handleShutdown(int $signal): void
    {
        $this->logger->info("Señal de parada recibida (Signal: $signal)");
        $this->running = false;
    }

    private function shutdown(): void
    {
        $this->logger->info('Cerrando agente ZKTeco...');

        // Desconectar del dispositivo
        $this->zktecoService->disconnect();

        // Sincronizar eventos pendientes finales
        if ($this->syncService->checkServerConnection()) {
            $this->syncService->sendAccessEventsToServer();
        }

        $this->logger->info('Agente cerrado correctamente');
    }

    public function getStatus(): array
    {
        return [
            'running' => $this->running,
            'cycles_completed' => $this->cycleCount,
            'zkteco_connected' => $this->zktecoService->isConnected(),
            'server_connected' => $this->syncService->checkServerConnection(),
            'agent_info' => [
                'id' => AgentConfig::get('agent.id'),
                'name' => AgentConfig::get('agent.name'),
            ],
            'device_info' => $this->zktecoService->getDeviceInfo(),
        ];
    }

    public function runSingleCycle(): array
    {
        $startTime = microtime(true);
        
        $this->runCycle();
        
        $executionTime = microtime(true) - $startTime;
        
        return [
            'success' => true,
            'execution_time' => round($executionTime * 1000, 2),
            'cycle_count' => $this->cycleCount,
            'status' => $this->getStatus()
        ];
    }

    public function syncNow(): array
    {
        $results = [];
        
        try {
            // Sincronizar empleados
            $results['employees_sync'] = $this->syncService->syncEmployeesFromServer();
            
            if ($results['employees_sync']) {
                $results['device_sync'] = $this->zktecoService->syncEmployeesToDevice();
            }
            
            // Procesar registros
            $results['new_records'] = $this->zktecoService->processNewAttendanceRecords();
            
            // Enviar eventos
            $results['events_sync'] = $this->syncService->sendAccessEventsToServer();
            
            return [
                'success' => true,
                'results' => $results
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}