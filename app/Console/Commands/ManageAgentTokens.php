<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AgentToken;

class ManageAgentTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:token 
                           {action : Acción a realizar (create, list, revoke, cleanup)}
                           {--agent-id= : ID del agente (1-5)}
                           {--name= : Nombre del token}
                           {--token= : Token a revocar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestionar tokens de autenticación para agentes ZKTeco';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'create':
                return $this->createToken();
            case 'list':
                return $this->listTokens();
            case 'revoke':
                return $this->revokeToken();
            case 'cleanup':
                return $this->cleanupTokens();
            default:
                $this->error("Acción no válida: $action");
                $this->info('Acciones disponibles: create, list, revoke, cleanup');
                return 1;
        }
    }

    private function createToken(): int
    {
        $agentId = $this->option('agent-id');
        $name = $this->option('name');

        if (!$agentId) {
            $agentId = $this->ask('ID del Agente (1-5)');
        }

        if (!$name) {
            $name = $this->ask('Nombre del token', "Token Agente $agentId");
        }

        if (!is_numeric($agentId) || $agentId < 1 || $agentId > 5) {
            $this->error('El ID del agente debe ser un número entre 1 y 5');
            return 1;
        }

        try {
            $token = AgentToken::createForAgent((int) $agentId, $name);
            
            $this->info('Token creado exitosamente:');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $token->id],
                    ['Agente ID', $token->agent_id],
                    ['Nombre', $token->name],
                    ['Token', $token->token],
                    ['Expira', $token->expires_at->format('Y-m-d H:i:s')],
                ]
            );

            $this->warn('⚠️  Guarda este token de forma segura. No se mostrará nuevamente.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error al crear token: ' . $e->getMessage());
            return 1;
        }
    }

    private function listTokens(): int
    {
        $agentId = $this->option('agent-id');
        
        $query = AgentToken::query();
        
        if ($agentId) {
            if (!is_numeric($agentId) || $agentId < 1 || $agentId > 5) {
                $this->error('El ID del agente debe ser un número entre 1 y 5');
                return 1;
            }
            $query->forAgent((int) $agentId);
        }

        $tokens = $query->orderBy('created_at', 'desc')->get();

        if ($tokens->isEmpty()) {
            $this->info('No se encontraron tokens.');
            return 0;
        }

        $data = $tokens->map(function ($token) {
            return [
                'ID' => $token->id,
                'Agente' => $token->agent_id,
                'Nombre' => $token->name,
                'Token' => substr($token->token, 0, 20) . '...',
                'Estado' => $token->is_active ? 
                    ($token->isExpired() ? 'Expirado' : 'Activo') : 
                    'Revocado',
                'Último uso' => $token->last_used_at?->format('Y-m-d H:i') ?? 'Nunca',
                'Expira' => $token->expires_at?->format('Y-m-d H:i') ?? 'Nunca',
                'Creado' => $token->created_at->format('Y-m-d H:i'),
            ];
        });

        $this->table(
            ['ID', 'Agente', 'Nombre', 'Token', 'Estado', 'Último uso', 'Expira', 'Creado'],
            $data->toArray()
        );

        return 0;
    }

    private function revokeToken(): int
    {
        $tokenString = $this->option('token');
        
        if (!$tokenString) {
            $this->listTokens();
            $tokenId = $this->ask('ID del token a revocar');
            
            if (!is_numeric($tokenId)) {
                $this->error('ID de token no válido');
                return 1;
            }
            
            $token = AgentToken::find($tokenId);
        } else {
            $token = AgentToken::where('token', $tokenString)->first();
        }

        if (!$token) {
            $this->error('Token no encontrado');
            return 1;
        }

        if (!$token->is_active) {
            $this->warn('El token ya está revocado');
            return 0;
        }

        if ($this->confirm("¿Está seguro de revocar el token '{$token->name}' del Agente {$token->agent_id}?")) {
            $token->revoke();
            $this->info('Token revocado exitosamente');
            return 0;
        }

        $this->info('Operación cancelada');
        return 0;
    }

    private function cleanupTokens(): int
    {
        $expiredCount = AgentToken::where('is_active', true)
            ->where('expires_at', '<', now())
            ->count();

        $inactiveCount = AgentToken::where('is_active', false)
            ->where('created_at', '<', now()->subMonths(3))
            ->count();

        if ($expiredCount === 0 && $inactiveCount === 0) {
            $this->info('No hay tokens para limpiar');
            return 0;
        }

        $this->info("Tokens expirados: $expiredCount");
        $this->info("Tokens inactivos antiguos: $inactiveCount");

        if ($this->confirm('¿Desea continuar con la limpieza?')) {
            // Revocar tokens expirados
            AgentToken::where('is_active', true)
                ->where('expires_at', '<', now())
                ->update(['is_active' => false]);

            // Eliminar tokens inactivos antiguos
            $deleted = AgentToken::where('is_active', false)
                ->where('created_at', '<', now()->subMonths(3))
                ->delete();

            $this->info("Tokens expirados revocados: $expiredCount");
            $this->info("Tokens antiguos eliminados: $deleted");
            
            return 0;
        }

        $this->info('Operación cancelada');
        return 0;
    }
}
