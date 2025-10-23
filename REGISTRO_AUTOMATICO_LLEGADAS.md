# Sistema de Registro Automático de Llegadas

## Descripción

Este sistema automatiza el registro de llegadas para empleados que salieron con permiso y aún no han regresado hasta las 5:00 PM (17:00 hrs).

## ¿Cómo funciona?

1. **Detección automática**: El sistema detecta todos los empleados con estado `out` (fuera de oficina).

2. **Registro automático**: A las 5:00 PM de cada día laborable (lunes a viernes), el sistema:
   - Busca todos los registros de tracking con estado `out`
   - Registra automáticamente el regreso a las 17:00 hrs
   - Calcula las horas reales utilizadas
   - Actualiza el estado del permiso a `approved`
   - Agrega una nota indicando que fue un registro automático

3. **Usuario del sistema**: El registro se realiza usando un usuario administrador del sistema.

## Comando Artisan

### Comando principal
```bash
php artisan tracking:auto-register-returns
```

### Modo de prueba (dry-run)
Para ver qué empleados serían registrados sin hacer cambios reales:
```bash
php artisan tracking:auto-register-returns --dry-run
```

## Programación Automática

El comando está programado para ejecutarse automáticamente:

- **Horario**: 17:00 hrs (5:00 PM)
- **Días**: Lunes a Viernes (días laborables)
- **Ubicación del código**: `routes/console.php`

```php
Schedule::command('tracking:auto-register-returns')
    ->dailyAt('17:00')
    ->weekdays()
    ->withoutOverlapping()
    ->runInBackground();
```

## Configuración del Servidor

Para que las tareas programadas funcionen en producción, asegúrate de tener configurado el cron job en tu servidor:

```bash
* * * * * cd /ruta/del/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

### En cPanel:
1. Ve a **Cron Jobs**
2. Agrega un nuevo cron job con la siguiente configuración:
   - **Frecuencia**: Cada minuto (`* * * * *`)
   - **Comando**: `/usr/local/bin/php /home/usuario/public_html/artisan schedule:run >> /dev/null 2>&1`

### En Windows (Task Scheduler):
1. Abre el **Programador de tareas**
2. Crea una nueva tarea básica
3. **Frecuencia**: Diario
4. **Acción**: Iniciar un programa
5. **Programa**: `C:\xampp\php\php.exe` (ajusta la ruta según tu instalación)
6. **Argumentos**: `C:\Users\...\papeletas-digitales\artisan schedule:run`
7. Configura para que se ejecute cada minuto

## Logs

El sistema registra cada ejecución en los logs de Laravel:

- **Ubicación**: `storage/logs/laravel.log`
- **Eventos registrados**:
  - Ejecución exitosa del comando
  - Cada registro automático de llegada
  - Errores si los hay

### Ejemplo de log:
```
[2025-10-23 17:00:01] local.INFO: Auto-registered return for employee {"tracking_id":1,"employee_id":5,"employee_name":"Juan Pérez","departure_datetime":"2025-10-23 08:00:00","return_datetime":"2025-10-23 17:00:00","actual_hours_used":9.0}
```

## Verificar tareas programadas

Para ver todas las tareas programadas:
```bash
php artisan schedule:list
```

Para ejecutar todas las tareas programadas manualmente:
```bash
php artisan schedule:run
```

## Notas importantes

1. **Usuario administrador requerido**: El sistema necesita al menos un usuario con rol `admin` para poder registrar las llegadas.

2. **Horario de cierre**: El sistema registra todas las llegadas a las 17:00 hrs exactas, independientemente de la hora de salida.

3. **Días laborables**: Solo se ejecuta de lunes a viernes, no en fines de semana.

4. **Sin duplicación**: El sistema tiene protección `withoutOverlapping()` para evitar ejecuciones duplicadas.

5. **Notas automáticas**: Cada registro automático incluye una nota que indica:
   ```
   Regreso registrado automáticamente por el sistema a las 17:00 hrs (horario de cierre).
   ```

## Solución de problemas

### El comando no se ejecuta automáticamente
- Verifica que el cron job esté configurado correctamente en el servidor
- Revisa los logs en `storage/logs/laravel.log`
- Ejecuta manualmente: `php artisan schedule:run`

### Error: "No admin user found"
- Asegúrate de tener al menos un usuario con rol `admin` en la base de datos
- Verifica la tabla `users` y `roles`

### El comando se ejecuta pero no registra llegadas
- Verifica que haya empleados con estado `out`
- Revisa los logs para ver detalles del error
- Ejecuta en modo dry-run para depurar: `php artisan tracking:auto-register-returns --dry-run`

## Archivos relacionados

- **Comando**: `app/Console/Commands/AutoRegisterReturns.php`
- **Programación**: `routes/console.php`
- **Modelo**: `app/Models/PermissionTracking.php`
- **Controlador**: `app/Http/Controllers/PermissionTrackingController.php`

## Futuras mejoras

Posibles mejoras al sistema:

1. Configurar horario de cierre por departamento
2. Enviar notificaciones a empleados cuando se registra automáticamente
3. Reportes de empleados con registros automáticos frecuentes
4. Excepciones para ciertos tipos de permisos
5. Configuración de horarios especiales para días festivos
