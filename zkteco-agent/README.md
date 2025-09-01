# Agente ZKTeco para Sistema de Papeletas Digitales

Este agente PHP permite la integración de dispositivos ZKTeco G3 con el sistema de papeletas digitales, proporcionando control de acceso en tiempo real y sincronización automática de eventos.

## Características

- ✅ **Conexión con dispositivos ZKTeco G3**
- ✅ **Sincronización bidireccional de empleados**
- ✅ **Captura de eventos de entrada/salida en tiempo real**
- ✅ **Base de datos local SQLite para confiabilidad**
- ✅ **API REST para comunicación con el servidor Laravel**
- ✅ **Sistema de autenticación por tokens**
- ✅ **Soporte para hasta 5 agentes simultáneos**
- ✅ **Logs detallados y monitoreo**
- ✅ **Reintentos automáticos en caso de errores**

## Requisitos del Sistema

### Hardware
- PC/Laptop conectada a la misma red que el dispositivo ZKTeco
- Dispositivo ZKTeco G3 (o compatible)
- Conexión a internet para comunicación con el servidor Laravel

### Software
- PHP 8.1 o superior
- Extensiones PHP: `sqlite3`, `curl`, `json`, `mbstring`
- Compositor (se descarga automáticamente si no está disponible)

## Instalación

### 1. Descarga e Instalación Automática

```bash
# Ejecutar el instalador
php install.php
```

El instalador realizará:
- Verificación de requisitos
- Configuración interactiva
- Instalación de dependencias
- Creación de base de datos local
- Pruebas de conectividad

### 2. Configuración Manual

Si prefiere configurar manualmente:

```bash
# Copiar archivo de configuración
cp .env.example .env

# Editar configuración
nano .env

# Instalar dependencias
composer install --no-dev --optimize-autoloader
```

## Configuración

### Variables de Entorno

```env
# ID único del agente (1-5)
AGENT_ID=1
AGENT_NAME="Agente RRHH Principal"

# Dispositivo ZKTeco
ZKTECO_IP=192.168.1.100
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0

# Servidor Laravel
SERVER_URL=http://localhost:8000
API_TOKEN=tu_token_aqui

# Configuración local
DB_PATH=./database/agent.db
LOG_FILE=./logs/agent.log
SYNC_INTERVAL=30
```

### Configuración del Servidor Laravel

1. **Crear token de autenticación:**
```bash
php artisan agent:token create --agent-id=1 --name="Token Principal"
```

2. **Ejecutar migraciones:**
```bash
php artisan migrate
```

## Uso

### Comandos Básicos

```bash
# Iniciar el agente
php agent.php start

# Probar conexiones
php agent.php test

# Ver estado del agente
php agent.php status

# Ejecutar sincronización manual
php agent.php sync

# Ejecutar un solo ciclo (útil para debugging)
php agent.php cycle

# Mostrar ayuda
php agent.php help
```

### Ejecutar como Servicio

#### Windows
```batch
# Usar el script generado
start_agent_1.bat
```

#### Linux (systemd)
```bash
# Copiar archivo de servicio
sudo cp zkteco-agent-1.service /etc/systemd/system/

# Habilitar servicio
sudo systemctl enable zkteco-agent-1

# Iniciar servicio
sudo systemctl start zkteco-agent-1

# Ver estado
sudo systemctl status zkteco-agent-1

# Ver logs
sudo journalctl -u zkteco-agent-1 -f
```

## Arquitectura

### Componentes Principales

1. **AgentRunner**: Bucle principal de ejecución
2. **ZktecoService**: Comunicación con dispositivo
3. **ServerSyncService**: Sincronización con Laravel
4. **AgentDatabase**: Gestión de base de datos local
5. **AgentConfig**: Gestión de configuración

### Flujo de Datos

```
[Dispositivo ZKTeco] ←→ [Agente PHP] ←→ [Servidor Laravel]
                           ↓
                    [Base de Datos Local]
```

### Ciclo de Operación

1. **Inicialización**
   - Conexión con dispositivo ZKTeco
   - Registro con servidor Laravel
   - Sincronización inicial de empleados

2. **Bucle Principal (cada 30 segundos)**
   - Captura de nuevos eventos de acceso
   - Envío de eventos al servidor
   - Heartbeat al servidor
   - Sincronización periódica de empleados

## API Endpoints (Laravel)

### Autenticación
Todas las rutas requieren token en header:
```
Authorization: Bearer zka_tu_token_aqui
```

### Endpoints Disponibles

```http
# Ping (sin autenticación)
GET /api/agent/ping

# Registro del agente
POST /api/agent/register

# Heartbeat
POST /api/agent/heartbeat

# Obtener empleados
GET /api/agent/employees

# Enviar eventos de acceso
POST /api/agent/access-events

# Obtener trackings por DNI
GET /api/agent/permission-trackings/{dni}

# Estado del servidor
GET /api/agent/status
```

## Panel de Administración Web

Accesible desde el sistema Laravel en `/agents` (solo jefes RRHH):

### Funcionalidades
- **Dashboard de agentes**: Estados en tiempo real
- **Gestión de tokens**: Crear/revocar tokens de autenticación
- **Monitoreo**: Logs y estadísticas de cada agente
- **Comandos remotos**: Sincronización forzada, reinicio, etc.

## Solución de Problemas

### Problemas Comunes

#### 1. No se puede conectar al dispositivo ZKTeco
```bash
# Verificar conectividad
ping 192.168.1.100

# Probar telnet al puerto
telnet 192.168.1.100 4370

# Verificar configuración
php agent.php test
```

#### 2. Error de autenticación con el servidor
```bash
# Verificar token
php artisan agent:token list

# Crear nuevo token si es necesario
php artisan agent:token create --agent-id=1
```

#### 3. Problemas de sincronización
```bash
# Forzar sincronización
php agent.php sync

# Ver logs detallados
tail -f logs/agent.log

# Limpiar base de datos local (recomendado solo en desarrollo)
rm database/agent.db
```

### Logs

Los logs se encuentran en:
- **Archivo**: `logs/agent_X.log` (donde X es el ID del agente)
- **Consola**: Al ejecutar manualmente
- **Sistema**: journalctl en Linux, Event Viewer en Windows

### Comandos de Diagnóstico

```bash
# Ver último heartbeat
php -r "echo cache()->get('agent_1')['last_heartbeat'] ?? 'nunca';"

# Verificar tokens activos
php artisan agent:token list --agent-id=1

# Limpiar tokens expirados
php artisan agent:token cleanup
```

## Desarrollo y Contribución

### Estructura del Proyecto

```
zkteco-agent/
├── src/
│   ├── Config/AgentConfig.php
│   ├── Database/AgentDatabase.php
│   ├── Services/ZktecoService.php
│   ├── Services/ServerSyncService.php
│   └── AgentRunner.php
├── database/
├── logs/
├── agent.php
├── install.php
├── composer.json
└── .env.example
```

### Agregar Nuevas Funcionalidades

1. **Crear nueva funcionalidad en ZktecoService**
2. **Agregar endpoint correspondiente en AgentController**
3. **Actualizar documentación**
4. **Agregar pruebas**

## Seguridad

### Buenas Prácticas
- Cambiar tokens regularmente
- Usar conexiones HTTPS en producción
- Limitar acceso de red al dispositivo ZKTeco
- Monitorear logs regularmente
- Mantener actualizadas las dependencias

### Tokens de Autenticación
- Los tokens expiran después de 1 año
- Se puede revocar tokens individualmente
- Los tokens incluyen el timestamp del último uso
- Soporte para múltiples tokens por agente

## Licencia

Este proyecto es propiedad del sistema de papeletas digitales y está sujeto a los términos de la licencia del proyecto principal.

## Soporte

Para soporte técnico:
1. Revisar logs del agente
2. Verificar conexiones de red
3. Consultar este README
4. Contactar al administrador del sistema