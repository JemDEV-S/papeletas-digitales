<?php

namespace ZktecoAgent\Config;

class AgentConfig
{
    private static array $config = [];

    public static function load(string $envPath = null): void
    {
        $envPath = $envPath ?: __DIR__ . '/../../.env';
        
        if (file_exists($envPath)) {
            $dotenv = \Dotenv\Dotenv::createImmutable(dirname($envPath));
            $dotenv->load();
        }

        self::$config = [
            'agent' => [
                'id' => (int) ($_ENV['AGENT_ID'] ?? 1),
                'name' => $_ENV['AGENT_NAME'] ?? 'Agente ZKTeco',
            ],
            'zkteco' => [
                'ip' => $_ENV['ZKTECO_IP'] ?? '192.168.1.100',
                'port' => (int) ($_ENV['ZKTECO_PORT'] ?? 4370),
                'password' => (int) ($_ENV['ZKTECO_PASSWORD'] ?? 0),
            ],
            'server' => [
                'url' => $_ENV['SERVER_URL'] ?? 'http://localhost:8000',
                'api_token' => $_ENV['API_TOKEN'] ?? '',
            ],
            'database' => [
                'path' => $_ENV['DB_PATH'] ?? './database/agent.db',
            ],
            'logging' => [
                'level' => $_ENV['LOG_LEVEL'] ?? 'info',
                'file' => $_ENV['LOG_FILE'] ?? './logs/agent.log',
            ],
            'sync' => [
                'interval' => (int) ($_ENV['SYNC_INTERVAL'] ?? 30),
                'max_retry_attempts' => (int) ($_ENV['MAX_RETRY_ATTEMPTS'] ?? 3),
                'retry_delay' => (int) ($_ENV['RETRY_DELAY'] ?? 5),
            ],
        ];
    }

    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function getAll(): array
    {
        return self::$config;
    }
}