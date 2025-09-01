<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgentToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AgentManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:jefe_rrhh']);
    }

    public function index()
    {
        $agents = $this->getAgentsWithStatus();
        $tokens = AgentToken::with([])->orderBy('created_at', 'desc')->get();

        return view('agents.index', compact('agents', 'tokens'));
    }

    public function show($agentId)
    {
        if (!is_numeric($agentId) || $agentId < 1 || $agentId > 5) {
            abort(404, 'Agente no encontrado');
        }

        $agent = $this->getAgentStatus($agentId);
        $tokens = AgentToken::forAgent($agentId)->orderBy('created_at', 'desc')->get();

        if (!$agent) {
            abort(404, 'Agente no encontrado o nunca se ha conectado');
        }

        return view('agents.show', compact('agent', 'tokens'));
    }

    public function createToken(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|integer|min:1|max:5',
            'name' => 'required|string|max:255',
        ]);

        try {
            $token = AgentToken::createForAgent(
                $request->agent_id,
                $request->name
            );

            return response()->json([
                'success' => true,
                'message' => 'Token creado exitosamente',
                'token' => $token->token,
                'token_id' => $token->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear token: ' . $e->getMessage()
            ], 500);
        }
    }

    public function revokeToken($tokenId)
    {
        $token = AgentToken::findOrFail($tokenId);
        $token->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Token revocado exitosamente'
        ]);
    }

    public function getAgentsData()
    {
        $agents = $this->getAgentsWithStatus();
        
        return response()->json([
            'success' => true,
            'agents' => $agents
        ]);
    }

    public function getAgentLogs($agentId)
    {
        if (!is_numeric($agentId) || $agentId < 1 || $agentId > 5) {
            abort(404);
        }

        // Obtener logs del cache o base de datos
        // Por ahora, retornamos un ejemplo
        $logs = [
            [
                'timestamp' => now()->subMinutes(5)->toISOString(),
                'level' => 'info',
                'message' => 'Conexión establecida con dispositivo ZKTeco',
                'context' => ['ip' => '192.168.1.100']
            ],
            [
                'timestamp' => now()->subMinutes(10)->toISOString(),
                'level' => 'info',
                'message' => 'Sincronización de empleados completada',
                'context' => ['count' => 25]
            ],
            [
                'timestamp' => now()->subMinutes(15)->toISOString(),
                'level' => 'warning',
                'message' => 'Reintento de conexión con servidor',
                'context' => []
            ],
        ];

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    public function refreshAgentStatus($agentId)
    {
        if (!is_numeric($agentId) || $agentId < 1 || $agentId > 5) {
            abort(404);
        }

        $agent = $this->getAgentStatus($agentId);
        
        return response()->json([
            'success' => true,
            'agent' => $agent
        ]);
    }

    public function sendCommand(Request $request, $agentId)
    {
        $request->validate([
            'command' => 'required|in:sync,restart,status',
        ]);

        if (!is_numeric($agentId) || $agentId < 1 || $agentId > 5) {
            abort(404);
        }

        // Por ahora, simular el envío de comando
        // En una implementación real, esto podría usar una cola o WebSocket
        $command = $request->command;
        
        // Guardar comando en cache para que el agente lo recoja
        Cache::put("agent_{$agentId}_command", [
            'command' => $command,
            'issued_at' => now()->toISOString(),
            'status' => 'pending'
        ], now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'message' => "Comando '$command' enviado al Agente $agentId"
        ]);
    }

    private function getAgentsWithStatus(): array
    {
        $agents = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $agent = $this->getAgentStatus($i);
            if ($agent) {
                $agents[] = $agent;
            }
        }

        return $agents;
    }

    private function getAgentStatus(int $agentId): ?array
    {
        $configKey = "agent_$agentId";
        $agentData = Cache::get($configKey);
        
        if (!$agentData) {
            return null;
        }

        $lastHeartbeat = isset($agentData['last_heartbeat']) 
            ? Carbon::parse($agentData['last_heartbeat'])
            : null;
        
        $isOnline = $lastHeartbeat && $lastHeartbeat->diffInMinutes(now()) < 5;

        // Obtener tokens activos
        $activeTokens = AgentToken::forAgent($agentId)->active()->count();

        return [
            'id' => $agentId,
            'name' => $agentData['name'] ?? "Agente $agentId",
            'status' => $isOnline ? 'online' : 'offline',
            'zkteco_ip' => $agentData['zkteco_ip'] ?? null,
            'last_heartbeat' => $lastHeartbeat?->toISOString(),
            'last_heartbeat_human' => $lastHeartbeat?->diffForHumans(),
            'registered_at' => isset($agentData['registered_at']) ? 
                Carbon::parse($agentData['registered_at'])->toISOString() : null,
            'stats' => $agentData['stats'] ?? null,
            'active_tokens' => $activeTokens,
            'uptime_minutes' => $lastHeartbeat ? 
                Carbon::parse($agentData['registered_at'] ?? $agentData['last_heartbeat'])
                    ->diffInMinutes(now()) : 0,
        ];
    }
}
