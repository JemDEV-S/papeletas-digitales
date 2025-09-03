<?php

namespace ZktecoAgent\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use ZktecoAgent\Config\AgentConfig;
use ZktecoAgent\Database\AgentDatabase;
use Psr\Log\LoggerInterface;

class ServerSyncService
{
    private Client $httpClient;
    private AgentDatabase $db;
    private LoggerInterface $logger;
    private string $serverUrl;
    private string $apiToken;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->db = AgentDatabase::getInstance();
        $this->serverUrl = AgentConfig::get('server.url');
        $this->apiToken = AgentConfig::get('server.api_token');

        $this->httpClient = new Client([
            'base_uri' => $this->serverUrl,
            'timeout' => 30,
            'verify' => false, // Desactivar verificación SSL (ajustar según necesidad)
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
                'X-Agent-ID' => AgentConfig::get('agent.id'),
            ]
        ]);
    }

    public function syncEmployeesFromServer(): bool
    {
        try {
            $response = $this->httpClient->get('api/agent/employees');
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Error en respuesta del servidor: ' . $response->getStatusCode());
            }

            $data = json_decode($response->getBody(), true);
            
            if (!isset($data['data'])) {
                throw new \Exception('Formato de respuesta inválido');
            }

            $employees = $data['data'];
            $syncedCount = 0;

            foreach ($employees as $employee) {
                if ($this->db->saveEmployee($employee)) {
                    $syncedCount++;
                }
            }

            $this->logger->info("Empleados sincronizados desde el servidor", [
                'total' => count($employees),
                'synced' => $syncedCount
            ]);

            $this->db->addSyncLog(
                'sync_employees_from_server', 
                'success',
                "Sincronizados: $syncedCount de " . count($employees)
            );

            // Actualizar timestamp de última sincronización
            $this->db->setConfig('last_employee_sync', date('Y-m-d H:i:s'));

            return true;
        } catch (RequestException $e) {
            $this->logger->error('Error de conexión al sincronizar empleados: ' . $e->getMessage());
            $this->db->addSyncLog('sync_employees_from_server', 'error', 'Error de conexión: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Error al sincronizar empleados: ' . $e->getMessage());
            $this->db->addSyncLog('sync_employees_from_server', 'error', $e->getMessage());
            return false;
        }
    }

    public function sendAccessEventsToServer(): bool
    {
        $pendingEvents = $this->db->getPendingSyncEvents();
        
        if (empty($pendingEvents)) {
            return true; // No hay eventos pendientes
        }

        $this->logger->info("Enviando eventos de acceso al servidor", ['count' => count($pendingEvents)]);

        $successCount = 0;
        $errorCount = 0;

        foreach ($pendingEvents as $event) {
            if ($this->sendSingleAccessEvent($event)) {
                $this->db->markEventAsSynced($event['id']);
                $successCount++;
            } else {
                $this->db->incrementEventRetryCount($event['id']);
                $errorCount++;
                
                // Verificar si excede el máximo de reintentos DESPUÉS del incremento
                $newRetryCount = $event['retry_count'] + 1;
                if ($newRetryCount >= AgentConfig::get('sync.max_retry_attempts')) {
                    $this->logger->warning('Evento excede máximo de reintentos, marcando como descartado', [
                        'event_id' => $event['id'],
                        'dni' => $event['dni'],
                        'retry_count' => $newRetryCount
                    ]);
                    
                    // Marcar como sincronizado para evitar futuros reintentos, aunque haya fallado
                    $this->db->markEventAsSynced($event['id']);
                }
            }
        }

        $this->logger->info("Sincronización de eventos completada", [
            'success' => $successCount,
            'errors' => $errorCount
        ]);

        $this->db->addSyncLog(
            'sync_access_events',
            $errorCount > 0 ? 'partial' : 'success',
            "Éxito: $successCount, Errores: $errorCount"
        );

        return $errorCount === 0;
    }

    private function sendSingleAccessEvent(array $event): bool
    {
        try {
            $payload = [
                'agent_id' => AgentConfig::get('agent.id'),
                'dni' => $event['dni'],
                'event_type' => $event['event_type'],
                'event_datetime' => $event['event_datetime'],
                'zkteco_event_id' => $event['zkteco_event_id'],
            ];

            $response = $this->httpClient->post('api/agent/access-events', [
                'json' => $payload
            ]);

            if ($response->getStatusCode() === 200) {
                $responseData = json_decode($response->getBody(), true);
                
                // Actualizar el permission_tracking_id si se proporciona
                if (isset($responseData['permission_tracking_id'])) {
                    $stmt = $this->db->getConnection()->prepare("
                        UPDATE access_events 
                        SET permission_tracking_id = ? 
                        WHERE id = ?
                    ");
                    $stmt->bindValue(1, $responseData['permission_tracking_id'], SQLITE3_INTEGER);
                    $stmt->bindValue(2, $event['id'], SQLITE3_INTEGER);
                    $stmt->execute();
                }

                return true;
            }

            return false;
        } catch (RequestException $e) {
            $this->logger->warning('Error al enviar evento de acceso', [
                'event_id' => $event['id'],
                'error' => $e->getMessage()
            ]);
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Excepción al enviar evento de acceso', [
                'event_id' => $event['id'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function registerAgentWithServer(): bool
    {
        try {
            $payload = [
                'agent_id' => AgentConfig::get('agent.id'),
                'agent_name' => AgentConfig::get('agent.name'),
                'zkteco_ip' => AgentConfig::get('zkteco.ip'),
                'status' => 'online',
                'last_sync' => date('Y-m-d H:i:s'),
            ];

            $response = $this->httpClient->post('api/agent/register', [
                'json' => $payload
            ]);

            if ($response->getStatusCode() === 200) {
                $this->logger->info('Agente registrado exitosamente en el servidor');
                $this->db->addSyncLog('agent_registration', 'success', 'Agente registrado');
                return true;
            }

            return false;
        } catch (RequestException $e) {
            $this->logger->error('Error al registrar agente: ' . $e->getMessage());
            $this->db->addSyncLog('agent_registration', 'error', $e->getMessage());
            return false;
        }
    }

    public function sendHeartbeat(): bool
    {
        try {
            $payload = [
                'agent_id' => AgentConfig::get('agent.id'),
                'status' => 'online',
                'timestamp' => date('Y-m-d H:i:s'),
                'stats' => $this->getAgentStats(),
            ];

            $response = $this->httpClient->post('api/agent/heartbeat', [
                'json' => $payload
            ]);

            if ($response->getStatusCode() === 200) {
                $this->db->setConfig('last_heartbeat', date('Y-m-d H:i:s'));
                return true;
            }

            return false;
        } catch (RequestException $e) {
            $this->logger->warning('Error en heartbeat: ' . $e->getMessage());
            return false;
        }
    }

    private function getAgentStats(): array
    {
        $db = $this->db->getConnection();
        
        // Contar empleados activos
        $employeeCount = $db->querySingle("SELECT COUNT(*) FROM employees WHERE is_active = 1");
        
        // Contar eventos pendientes de sincronización
        $pendingEvents = $db->querySingle("SELECT COUNT(*) FROM access_events WHERE synced = 0");
        
        // Contar eventos de hoy
        $todayEvents = $db->querySingle("
            SELECT COUNT(*) FROM access_events 
            WHERE DATE(event_datetime) = DATE('now')
        ");

        return [
            'employee_count' => (int) $employeeCount,
            'pending_sync_events' => (int) $pendingEvents,
            'today_events' => (int) $todayEvents,
        ];
    }

    public function checkServerConnection(): bool
    {
        try {
            $response = $this->httpClient->get('api/agent/ping');
            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            $this->logger->warning('Servidor no disponible: ' . $e->getMessage());
            return false;
        }
    }

    public function getServerStatus(): array
    {
        try {
            $response = $this->httpClient->get('api/agent/status');
            
            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody(), true);
            }

            return ['status' => 'error', 'message' => 'Server error'];
        } catch (RequestException $e) {
            return [
                'status' => 'connection_error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function getPermissionTrackingsForDni(string $dni): array
    {
        try {
            $response = $this->httpClient->get("api/agent/permission-trackings/{$dni}");
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                return $data['data'] ?? [];
            }

            return [];
        } catch (RequestException $e) {
            $this->logger->warning("Error al obtener trackings para DNI {$dni}: " . $e->getMessage());
            return [];
        }
    }
}