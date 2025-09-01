<?php

namespace ZktecoAgent\Database;

use SQLite3;
use ZktecoAgent\Config\AgentConfig;

class AgentDatabase
{
    private SQLite3 $db;
    private static ?AgentDatabase $instance = null;

    private function __construct()
    {
        $dbPath = AgentConfig::get('database.path');
        $dbDir = dirname($dbPath);
        
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        $this->db = new SQLite3($dbPath);
        $this->db->exec('PRAGMA foreign_keys = ON;');
        $this->createTables();
    }

    public static function getInstance(): AgentDatabase
    {
        if (self::$instance === null) {
            self::$instance = new AgentDatabase();
        }
        return self::$instance;
    }

    private function createTables(): void
    {
        // Tabla de empleados locales
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS employees (
                id INTEGER PRIMARY KEY,
                server_user_id INTEGER UNIQUE NOT NULL,
                dni TEXT UNIQUE NOT NULL,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                department TEXT,
                zkteco_user_id INTEGER UNIQUE,
                is_active BOOLEAN DEFAULT 1,
                last_sync DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Tabla de eventos de acceso
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS access_events (
                id INTEGER PRIMARY KEY,
                employee_id INTEGER NOT NULL,
                dni TEXT NOT NULL,
                event_type TEXT NOT NULL, -- 'entry' or 'exit'
                event_datetime DATETIME NOT NULL,
                zkteco_event_id INTEGER,
                permission_tracking_id INTEGER,
                synced BOOLEAN DEFAULT 0,
                retry_count INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (employee_id) REFERENCES employees(id)
            )
        ");

        // Tabla de configuración del agente
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS agent_config (
                key TEXT PRIMARY KEY,
                value TEXT NOT NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Tabla de logs de sincronización
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS sync_logs (
                id INTEGER PRIMARY KEY,
                operation TEXT NOT NULL,
                status TEXT NOT NULL, -- 'success', 'error', 'pending'
                details TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Índices para mejor rendimiento
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_employees_dni ON employees(dni)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_employees_zkteco_id ON employees(zkteco_user_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_access_events_synced ON access_events(synced)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_access_events_datetime ON access_events(event_datetime)");
    }

    public function getConnection(): SQLite3
    {
        return $this->db;
    }

    // Métodos para empleados
    public function saveEmployee(array $employee): bool
    {
        $stmt = $this->db->prepare("
            INSERT OR REPLACE INTO employees 
            (server_user_id, dni, first_name, last_name, department, is_active, last_sync, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");

        $stmt->bindValue(1, $employee['id'], SQLITE3_INTEGER);
        $stmt->bindValue(2, $employee['dni'], SQLITE3_TEXT);
        $stmt->bindValue(3, $employee['first_name'], SQLITE3_TEXT);
        $stmt->bindValue(4, $employee['last_name'], SQLITE3_TEXT);
        $stmt->bindValue(5, $employee['department'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(6, $employee['is_active'] ? 1 : 0, SQLITE3_INTEGER);

        return $stmt->execute() !== false;
    }

    public function getEmployeeByDni(string $dni): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM employees WHERE dni = ? AND is_active = 1");
        $stmt->bindValue(1, $dni, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    public function getEmployeeByZktecoId(int $zktecoId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM employees WHERE zkteco_user_id = ? AND is_active = 1");
        $stmt->bindValue(1, $zktecoId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    public function updateEmployeeZktecoId(string $dni, int $zktecoId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE employees 
            SET zkteco_user_id = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE dni = ?
        ");
        $stmt->bindValue(1, $zktecoId, SQLITE3_INTEGER);
        $stmt->bindValue(2, $dni, SQLITE3_TEXT);
        
        return $stmt->execute() !== false;
    }

    // Métodos para eventos de acceso
    public function saveAccessEvent(array $event): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO access_events 
            (employee_id, dni, event_type, event_datetime, zkteco_event_id, permission_tracking_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bindValue(1, $event['employee_id'], SQLITE3_INTEGER);
        $stmt->bindValue(2, $event['dni'], SQLITE3_TEXT);
        $stmt->bindValue(3, $event['event_type'], SQLITE3_TEXT);
        $stmt->bindValue(4, $event['event_datetime'], SQLITE3_TEXT);
        $stmt->bindValue(5, $event['zkteco_event_id'] ?? null, SQLITE3_INTEGER);
        $stmt->bindValue(6, $event['permission_tracking_id'] ?? null, SQLITE3_INTEGER);

        return $stmt->execute() !== false;
    }

    public function getPendingSyncEvents(): array
    {
        $result = $this->db->query("
            SELECT * FROM access_events 
            WHERE synced = 0 
            ORDER BY event_datetime ASC
        ");

        $events = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $events[] = $row;
        }

        return $events;
    }

    public function markEventAsSynced(int $eventId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE access_events 
            SET synced = 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->bindValue(1, $eventId, SQLITE3_INTEGER);
        
        return $stmt->execute() !== false;
    }

    public function incrementEventRetryCount(int $eventId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE access_events 
            SET retry_count = retry_count + 1 
            WHERE id = ?
        ");
        $stmt->bindValue(1, $eventId, SQLITE3_INTEGER);
        
        return $stmt->execute() !== false;
    }

    // Métodos para configuración
    public function setConfig(string $key, string $value): bool
    {
        $stmt = $this->db->prepare("
            INSERT OR REPLACE INTO agent_config (key, value, updated_at)
            VALUES (?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt->bindValue(1, $key, SQLITE3_TEXT);
        $stmt->bindValue(2, $value, SQLITE3_TEXT);
        
        return $stmt->execute() !== false;
    }

    public function getConfig(string $key, string $default = null): ?string
    {
        $stmt = $this->db->prepare("SELECT value FROM agent_config WHERE key = ?");
        $stmt->bindValue(1, $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? $row['value'] : $default;
    }

    // Métodos para logs
    public function addSyncLog(string $operation, string $status, string $details = ''): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO sync_logs (operation, status, details)
            VALUES (?, ?, ?)
        ");
        $stmt->bindValue(1, $operation, SQLITE3_TEXT);
        $stmt->bindValue(2, $status, SQLITE3_TEXT);
        $stmt->bindValue(3, $details, SQLITE3_TEXT);
        
        return $stmt->execute() !== false;
    }

    public function getRecentLogs(int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM sync_logs 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $logs = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $logs[] = $row;
        }

        return $logs;
    }
}