<?php

namespace ZktecoAgent\Services;

use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use ZktecoAgent\Config\AgentConfig;
use ZktecoAgent\Database\AgentDatabase;
use Psr\Log\LoggerInterface;

class ZktecoService
{
    private Zkteco $zkteco;
    private AgentDatabase $db;
    private LoggerInterface $logger;
    private bool $connected = false;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->db = AgentDatabase::getInstance();
        $this->initializeDevice();
    }

    private function initializeDevice(): void
    {
        $ip = AgentConfig::get('zkteco.ip');
        $port = AgentConfig::get('zkteco.port');
        $password = AgentConfig::get('zkteco.password');

        $this->zkteco = new Zkteco($ip, $port, $password);
    }

    public function connect(): bool
    {
        try {
            $this->connected = $this->zkteco->connect();
            
            if ($this->connected) {
                $this->logger->info('Conectado exitosamente al dispositivo ZKTeco', [
                    'ip' => AgentConfig::get('zkteco.ip'),
                    'port' => AgentConfig::get('zkteco.port')
                ]);
                
                $this->db->addSyncLog('zkteco_connect', 'success', 'Conexión establecida');
            } else {
                $this->logger->error('Error al conectar con el dispositivo ZKTeco');
                $this->db->addSyncLog('zkteco_connect', 'error', 'Error de conexión');
            }

            return $this->connected;
        } catch (\Exception $e) {
            $this->logger->error('Excepción al conectar con ZKTeco: ' . $e->getMessage());
            $this->db->addSyncLog('zkteco_connect', 'error', $e->getMessage());
            return false;
        }
    }

    public function disconnect(): void
    {
        if ($this->connected) {
            $this->zkteco->disconnect();
            $this->connected = false;
            $this->logger->info('Desconectado del dispositivo ZKTeco');
        }
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function syncEmployeesToDevice(): bool
    {
        if (!$this->connected) {
            return false;
        }

        try {
            // Obtener empleados activos de la base local
            $employees = $this->getActiveEmployees();
            $successCount = 0;
            $errorCount = 0;

            foreach ($employees as $employee) {
                if ($this->addUserToDevice($employee)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }

            $this->logger->info("Sincronización de empleados completada", [
                'success' => $successCount,
                'errors' => $errorCount,
                'total' => count($employees)
            ]);

            $this->db->addSyncLog(
                'sync_employees_to_device', 
                $errorCount > 0 ? 'partial' : 'success',
                "Éxito: $successCount, Errores: $errorCount"
            );

            return $errorCount === 0;
        } catch (\Exception $e) {
            $this->logger->error('Error en sincronización de empleados: ' . $e->getMessage());
            $this->db->addSyncLog('sync_employees_to_device', 'error', $e->getMessage());
            return false;
        }
    }

    private function addUserToDevice(array $employee): bool
{
    try {
        $zktecoUserId = $employee['zkteco_user_id'] ?? $this->generateZktecoUserId($employee['dni']);

        if (!$employee['zkteco_user_id']) {
            $this->db->updateEmployeeZktecoId($employee['dni'], $zktecoUserId);
            $employee['zkteco_user_id'] = $zktecoUserId;
        }

        // Verificar primero si el usuario ya existe
        $existingUsers = $this->zkteco->getUsers();
        $userExists = false;
        
        foreach ($existingUsers as $existingUser) {
            if (isset($existingUser['uid']) && $existingUser['uid'] == $zktecoUserId) {
                $userExists = true;
                $this->logger->info("Usuario ya existe en el dispositivo", [
                    'dni' => $employee['dni'],
                    'zkteco_id' => $zktecoUserId,
                    'existing_name' => $existingUser['name'] ?? 'N/A'
                ]);
                break;
            }
            // También verificar por userid (DNI)
            if (isset($existingUser['userid']) && $existingUser['userid'] == $employee['dni']) {
                $userExists = true;
                $this->logger->info("Usuario encontrado por DNI en el dispositivo", [
                    'dni' => $employee['dni'],
                    'existing_uid' => $existingUser['uid'] ?? 'N/A',
                    'existing_name' => $existingUser['name'] ?? 'N/A'
                ]);
                break;
            }
        }

        $userData = [
            'uid'      => $zktecoUserId,
            'userid'   => $employee['dni'],
            'name'     => trim($employee['first_name'] . ' ' . $employee['last_name']),
            'password' => '',
            'role'     => 0,
        ];

        $this->logger->debug("Intentando agregar/actualizar usuario", [
            'dni' => $employee['dni'],
            'zkteco_id' => $zktecoUserId,
            'name' => $userData['name'],
            'user_exists' => $userExists
        ]);

        $result = false;
        $lastError = null;

        // Método 1: Intentar con setUser usando parámetros separados
        try {
            $result = $this->zkteco->setUser(
                (int) $userData['uid'],
                $userData['userid'], // DNI como string
                $userData['name'],
                $userData['password'],
                (int) $userData['role']
            );
            
            $this->logger->debug("Resultado método 1 (string userid)", [
                'dni' => $employee['dni'],
                'result' => $result ? 'true' : 'false',
                'result_type' => gettype($result)
            ]);
            
        } catch (\Exception $e1) {
            $lastError = $e1->getMessage();
            $this->logger->warning("Error en método 1 (string userid)", [
                'dni' => $employee['dni'],
                'error' => $e1->getMessage()
            ]);

            // Método 2: Intentar con userid como entero si el DNI es numérico
            if (is_numeric($userData['userid'])) {
                try {
                    $result = $this->zkteco->setUser(
                        (int) $userData['uid'],
                        (int) $userData['userid'],
                        $userData['name'],
                        $userData['password'],
                        (int) $userData['role']
                    );
                    
                    $this->logger->debug("Resultado método 2 (int userid)", [
                        'dni' => $employee['dni'],
                        'result' => $result ? 'true' : 'false',
                        'result_type' => gettype($result)
                    ]);
                    
                } catch (\Exception $e2) {
                    $lastError = $e2->getMessage();
                    $this->logger->error("Error con ambos métodos de setUser", [
                        'dni' => $employee['dni'],
                        'error1' => $e1->getMessage(),
                        'error2' => $e2->getMessage()
                    ]);
                    return false;
                }
            }
        }

        // Verificar si el usuario realmente se agregó/actualizó
        if ($result === false && $userExists) {
            // Si el resultado es false pero el usuario ya existía, 
            // podría ser normal en algunos dispositivos
            $this->logger->info("Usuario ya existía en el dispositivo, considerando como éxito", [
                'dni' => $employee['dni'],
                'zkteco_id' => $zktecoUserId
            ]);
            return true;
        }

        if ($result) {
            $this->logger->info("Usuario agregado/actualizado exitosamente", [
                'dni' => $employee['dni'],
                'zkteco_id' => $zktecoUserId,
                'name' => $userData['name']
            ]);
            return true;
        } else {
            // Verificar post-ejecución si el usuario realmente existe
            $postUsers = $this->zkteco->getUsers();
            $userFoundAfter = false;
            
            foreach ($postUsers as $postUser) {
                if ((isset($postUser['uid']) && $postUser['uid'] == $zktecoUserId) ||
                    (isset($postUser['userid']) && $postUser['userid'] == $employee['dni'])) {
                    $userFoundAfter = true;
                    break;
                }
            }
            
            if ($userFoundAfter) {
                $this->logger->info("Usuario encontrado en dispositivo después de setUser (considerando éxito)", [
                    'dni' => $employee['dni'],
                    'zkteco_id' => $zktecoUserId
                ]);
                return true;
            } else {
                $this->logger->error("Error al agregar usuario - no encontrado después de setUser", [
                    'dni' => $employee['dni'],
                    'zkteco_id' => $zktecoUserId,
                    'last_error' => $lastError
                ]);
                return false;
            }
        }

    } catch (\Exception $e) {
        $this->logger->error("Excepción general al agregar usuario al dispositivo", [
            'dni' => $employee['dni'],
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return false;
    }
}

    private function generateZktecoUserId(string $dni): int
    {
        // Generar un ID único basado en el DNI pero dentro del rango válido para ZKTeco
        $hash = crc32($dni);
        return abs($hash) % 65535 + 1; // Asegurar que esté en rango válido
    }

    public function getAttendanceRecords(): array
    {
        if (!$this->connected) {
            return [];
        }

        try {
            $records = $this->zkteco->getAttendances();
            
            if ($records) {
                $this->logger->info('Registros de asistencia obtenidos', ['count' => count($records)]);
                return $records;
            }

            return [];
        } catch (\Exception $e) {
            $this->logger->error('Error al obtener registros de asistencia: ' . $e->getMessage());
            return [];
        }
    }

    public function processNewAttendanceRecords(): int
{
    $records = $this->getAttendanceRecords();
    $processedCount = 0;

    foreach ($records as $record) {
        if ($this->processAttendanceRecord($record)) {
            $processedCount++;
        }
    }

    // NUEVO: Limpiar registros procesados del dispositivo
    if ($processedCount > 0) {
        $this->clearAttendanceRecords();
        $this->logger->info("Limpiados $processedCount registros del dispositivo");
    }

    return $processedCount;
}

    private function findEmployeeByAnyId($userId): ?array
    {
        // Buscar empleado que pueda tener este userId como zkteco_user_id
        $stmt = $this->db->getConnection()->prepare("
            SELECT * FROM employees 
            WHERE (zkteco_user_id = ? OR dni = ?) 
            AND is_active = 1 
            LIMIT 1
        ");
        $stmt->bindValue(1, is_numeric($userId) ? (int)$userId : 0, SQLITE3_INTEGER);
        $stmt->bindValue(2, (string)$userId, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    private function determineEventType(array $employee, int $timestamp): string
    {
        // Obtener el último evento de este empleado EN LAS ÚLTIMAS 24 HORAS
        $stmt = $this->db->getConnection()->prepare("
            SELECT event_type FROM access_events 
            WHERE employee_id = ? 
            AND event_datetime > datetime('now', '-24 hours')
            ORDER BY event_datetime DESC 
            LIMIT 1
        ");
        $stmt->bindValue(1, $employee['id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $lastEvent = $result->fetchArray(SQLITE3_ASSOC);

        // LÓGICA PARA SISTEMA DE PAPELETAS:
        // Si no hay eventos recientes o el último fue entrada (regreso), este es salida (por permiso)
        if (!$lastEvent || $lastEvent['event_type'] === 'entry') {
            return 'exit';
        }

        // Si el último fue salida (por permiso), este es entrada (regreso)
        return 'entry';
    }

    private function getActiveEmployees(): array
    {
        $result = $this->db->getConnection()->query("
            SELECT * FROM employees WHERE is_active = 1
        ");

        $employees = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $employees[] = $row;
        }

        return $employees;
    }

    public function getDeviceInfo(): array
    {
        if (!$this->connected) {
            return [];
        }

        try {
            return [
                'platform' => $this->zkteco->platform(),
                'firmware_version' => $this->zkteco->version(),
                'serial_number' => $this->zkteco->serialNumber(),
                'device_name' => $this->zkteco->deviceName(),
                'user_count' => count($this->zkteco->getUsers()),
                'attendance_count' => count($this->zkteco->getAttendances()),
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error al obtener información del dispositivo: ' . $e->getMessage());
            return [];
        }
    }

    public function clearAttendanceRecords(): bool
    {
        if (!$this->connected) {
            return false;
        }

        try {
            $result = $this->zkteco->clearAttendance();
            
            if ($result) {
                $this->logger->info('Registros de asistencia limpiados del dispositivo');
                $this->db->addSyncLog('clear_attendance', 'success', 'Registros limpiados');
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error al limpiar registros de asistencia: ' . $e->getMessage());
            $this->db->addSyncLog('clear_attendance', 'error', $e->getMessage());
            return false;
        }
    }

    // Método para debug - ver qué usuarios existen en el dispositivo
    public function debugDeviceUsers(): array
    {
        if (!$this->connected) {
            return [];
        }

        try {
            $users = $this->zkteco->getUsers();
            $this->logger->info('Usuarios en el dispositivo:', [
                'count' => count($users),
                'users' => array_slice($users, 0, 5) // Solo mostrar los primeros 5 para debug
            ]);
            return $users;
        } catch (\Exception $e) {
            $this->logger->error('Error al obtener usuarios del dispositivo: ' . $e->getMessage());
            return [];
        }
    }

private function processAttendanceRecord(array $record): bool
{
    try {
        // Debug: mostrar la estructura completa del registro
        $this->logger->debug('Procesando registro de asistencia', [
            'record' => $record,
            'keys' => array_keys($record)
        ]);

        // Intentar obtener el identificador del usuario del registro
        // Adaptarse a diferentes formatos de la librería ZKTeco
        $dniOrUserId = null;
        
        // Posibles campos que pueden contener el identificador del usuario
        $possibleUserFields = ['userid', 'user_id', 'uid', 'id'];
        
        foreach ($possibleUserFields as $field) {
            if (isset($record[$field]) && !empty($record[$field])) {
                $dniOrUserId = $record[$field];
                $this->logger->debug("Identificador encontrado en campo '$field'", [
                    'value' => $dniOrUserId,
                    'type' => gettype($dniOrUserId)
                ]);
                break;
            }
        }

        if ($dniOrUserId === null) {
            $this->logger->warning('No se encontró campo válido de usuario en el registro', [
                'record' => $record,
                'available_fields' => array_keys($record)
            ]);
            return false;
        }

        // Buscar empleado por diferentes métodos
        $employee = $this->findEmployeeForAttendance($dniOrUserId);
        
        if (!$employee) {
            $this->logger->warning('Registro de empleado no encontrado para asistencia', [
                'search_value' => $dniOrUserId,
                'record_keys' => array_keys($record),
                'full_record' => $record
            ]);
            return false;
        }

        // Obtener timestamp del registro
        $timestamp = $this->extractTimestamp($record);
        if (!$timestamp) {
            $this->logger->warning('No se pudo extraer timestamp del registro', [
                'record' => $record,
                'employee_dni' => $employee['dni']
            ]);
            return false;
        }

        // Determinar tipo de evento
        $eventType = $this->determineEventType($employee, $timestamp);

        $eventData = [
            'employee_id' => $employee['id'],
            'dni' => $employee['dni'],
            'event_type' => $eventType,
            'event_datetime' => date('Y-m-d H:i:s', $timestamp),
            'zkteco_event_id' => $record['uid'] ?? $record['id'] ?? null,
            'permission_tracking_id' => null
        ];

        // Verificar si el evento ya existe para evitar duplicados
        if ($this->eventAlreadyExists($eventData)) {
            $this->logger->info('Evento de asistencia ya existe, omitiendo', [
                'dni' => $employee['dni'],
                'datetime' => $eventData['event_datetime'],
                'type' => $eventType
            ]);
            return true; // Considerarlo como procesado exitosamente
        }

        $success = $this->db->saveAccessEvent($eventData);

        if ($success) {
            $this->logger->info('Evento de acceso registrado', [
                'dni' => $employee['dni'],
                'name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'type' => $eventType,
                'datetime' => $eventData['event_datetime'],
                'zkteco_event_id' => $eventData['zkteco_event_id']
            ]);
        } else {
            $this->logger->error('Error al guardar evento de acceso', [
                'dni' => $employee['dni'],
                'event_data' => $eventData
            ]);
        }

        return $success;
    } catch (\Exception $e) {
        $this->logger->error('Error al procesar registro de asistencia: ' . $e->getMessage(), [
            'record' => $record,
            'trace' => $e->getTraceAsString()
        ]);
        return false;
    }
}

private function findEmployeeForAttendance($searchValue): ?array
{
    // Método 1: Buscar por DNI directamente
    $employee = $this->db->getEmployeeByDni((string) $searchValue);
    if ($employee) {
        $this->logger->debug('Empleado encontrado por DNI', [
            'search_value' => $searchValue,
            'employee' => $employee['dni'] . ' - ' . $employee['first_name']
        ]);
        return $employee;
    }

    // Método 2: Buscar por zkteco_user_id si es numérico
    if (is_numeric($searchValue)) {
        $employee = $this->db->getEmployeeByZktecoId((int) $searchValue);
        if ($employee) {
            $this->logger->debug('Empleado encontrado por zkteco_user_id', [
                'search_value' => $searchValue,
                'employee' => $employee['dni'] . ' - ' . $employee['first_name']
            ]);
            return $employee;
        }
    }

    // Método 3: Buscar en todos los empleados con query personalizada
    $employee = $this->findEmployeeByAnyId($searchValue);
    if ($employee) {
        $this->logger->debug('Empleado encontrado por búsqueda amplia', [
            'search_value' => $searchValue,
            'employee' => $employee['dni'] . ' - ' . $employee['first_name']
        ]);
        return $employee;
    }

    // Método 4: Listar todos los empleados para debug
    $allEmployees = $this->getActiveEmployees();
    $this->logger->debug('Empleados activos disponibles', [
        'search_value' => $searchValue,
        'total_employees' => count($allEmployees),
        'employee_sample' => array_slice(array_map(function($emp) {
            return [
                'dni' => $emp['dni'],
                'zkteco_user_id' => $emp['zkteco_user_id'],
                'name' => $emp['first_name'] . ' ' . $emp['last_name']
            ];
        }, $allEmployees), 0, 5)
    ]);

    return null;
}

private function extractTimestamp(array $record): ?int
{
    // Posibles campos que pueden contener el timestamp
    $timestampFields = ['timestamp', 'record_time', 'time', 'datetime'];
    
    foreach ($timestampFields as $field) {
        if (isset($record[$field])) {
            $value = $record[$field];
            
            // Si ya es un timestamp unix
            if (is_numeric($value) && $value > 1000000000) {
                return (int) $value;
            }
            
            // Si es una fecha string, convertirla
            if (is_string($value)) {
                $timestamp = strtotime($value);
                if ($timestamp !== false) {
                    return $timestamp;
                }
            }
        }
    }
    
    // Si no se encuentra timestamp, usar el actual
    $this->logger->warning('No se pudo extraer timestamp, usando tiempo actual', [
        'record' => $record
    ]);
    return time();
}

private function eventAlreadyExists(array $eventData): bool
{
    $stmt = $this->db->getConnection()->prepare("
        SELECT COUNT(*) as count FROM access_events 
        WHERE employee_id = ? 
        AND event_datetime = ? 
        AND event_type = ?
    ");
    
    $stmt->bindValue(1, $eventData['employee_id'], SQLITE3_INTEGER);
    $stmt->bindValue(2, $eventData['event_datetime'], SQLITE3_TEXT);
    $stmt->bindValue(3, $eventData['event_type'], SQLITE3_TEXT);
    
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    
    return ($row && $row['count'] > 0);
}
}