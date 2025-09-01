<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AgentToken;
use App\Models\User;

class AuthenticateAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractTokenFromRequest($request);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación requerido'
            ], 401);
        }

        $agentToken = AgentToken::findValidToken($token);

        if (!$agentToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación inválido o expirado'
            ], 401);
        }

        // Actualizar último uso del token
        $agentToken->updateLastUsed();

        // Crear un usuario fake para el contexto de auth (necesario para algunas funcionalidades)
        $fakeUser = $this->createFakeUserForAgent($agentToken);
        auth()->setUser($fakeUser);

        // Agregar información del agente al request
        $request->merge([
            'agent_id' => $agentToken->agent_id,
            'agent_token_id' => $agentToken->id,
        ]);

        return $next($request);
    }

    private function extractTokenFromRequest(Request $request): ?string
    {
        // Buscar en header Authorization
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // Buscar en header X-Agent-Token
        $agentToken = $request->header('X-Agent-Token');
        if ($agentToken) {
            return $agentToken;
        }

        // Buscar en query parameter
        return $request->query('token');
    }

    private function createFakeUserForAgent(AgentToken $agentToken): User
    {
        // Crear un usuario fake para representar al agente en el contexto de autenticación
        $fakeUser = new User([
            'id' => 99990 + $agentToken->agent_id, // IDs especiales para agentes
            'dni' => 'AGENT' . str_pad($agentToken->agent_id, 2, '0', STR_PAD_LEFT),
            'first_name' => 'Agente',
            'last_name' => "ZKTeco #{$agentToken->agent_id}",
            'name' => "Agente ZKTeco #{$agentToken->agent_id}",
            'email' => "agent{$agentToken->agent_id}@system.local",
            'role_id' => null,
            'is_active' => true,
        ]);

        // Marcar como existente para evitar problemas con save()
        $fakeUser->exists = true;

        return $fakeUser;
    }
}
