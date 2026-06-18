# Analisis profundo y plan para Version 2

Sistema: Papeletas Digitales  
Fecha de analisis: 2026-05-08  
Stack actual: Laravel 12, PHP 8.2+, Blade, Tailwind CSS, Alpine/JavaScript, Sanctum, DomPDF/TCPDF/FPDI, Maatwebsite Excel, FIRMA PERU, agente ZKTeco.

## 1. Resumen ejecutivo

El sistema actual ya resuelve un flujo municipal importante: creacion de papeletas, aprobacion por jefe inmediato, aprobacion por RRHH, firma digital, adjuntos, notificaciones, reportes y seguimiento fisico con ZKTeco.

La base funcional existe, pero la aplicacion crecio por acumulacion de parches. Hay logica duplicada entre modelos, servicios y controladores; rutas web y APIs mezcladas; reglas de negocio parcialmente desconectadas del modelo de datos; codigo de depuracion en produccion; credenciales externas manejadas fuera del sistema de configuracion; y varios puntos donde la V1 puede fallar por columnas inexistentes o estados inconsistentes.

La Version 2 debe conservar lo que ya funciona, pero ordenar el dominio alrededor de una maquina de estados clara, servicios por modulo, politicas de autorizacion, reglas parametrizables, una API limpia, auditoria formal y una base de datos coherente.

## 2. Modulos actuales detectados

### 2.1 Autenticacion y usuarios

Archivos principales:

- `routes/auth.php`
- `app/Models/User.php`
- `app/Http/Controllers/Auth/*`
- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Admin/DepartmentController.php`
- `app/Http/Middleware/CheckRole.php`
- `app/Http/Middleware/AdminMiddleware.php`

Capacidades:

- Login con DNI y password.
- Registro base heredado de Laravel Breeze.
- Roles propios mediante tabla `roles`.
- Usuarios asociados a departamento.
- Jerarquia por `immediate_supervisor_id`.
- Administracion de usuarios, departamentos y jerarquias.

Observaciones:

- Se usa un sistema de roles propio, aunque `composer.json` incluye `spatie/laravel-permission`. Conviene elegir uno.
- `User::hasRole()` depende de una relacion simple `role`, no de Spatie.
- `User::pendingPermissionRequests()` mezcla `where` y `orWhere` sin agrupar; en usuarios RRHH puede traer resultados mas amplios de lo esperado.
- Hay campos de segundo factor (`two_factor_secret`, `two_factor_expires_at`), pero no se ve un flujo completo de 2FA.

### 2.2 Solicitudes de papeletas

Archivos principales:

- `app/Models/PermissionRequest.php`
- `app/Models/PermissionType.php`
- `app/Services/PermissionService.php`
- `app/Services/PermissionValidationService.php`
- `app/Http/Controllers/PermissionRequestController.php`
- `app/Http/Requests/StorePermissionRequestRequest.php`
- `app/Http/Requests/UpdatePermissionRequestRequest.php`
- `resources/views/permissions/*`

Capacidades:

- Creacion, edicion, cancelacion y visualizacion de solicitudes.
- Tipos de permiso con limites diarios, mensuales y documentos.
- Adjuntos por solicitud.
- Generacion de PDF.
- Envio con o sin firma digital.
- Caso especial: saltar jefe inmediato y derivar a RRHH.

Observaciones criticas:

- La migracion inicial creo `start_datetime`, `end_datetime` y `requested_hours`, pero luego una migracion los elimina. Sin embargo, varios servicios, controladores, reportes y APIs aun usan esos campos.
- `PermissionValidationService` valida reglas usando `start_datetime` y `requested_hours`, pero el formulario actual de `StorePermissionRequestRequest` solo valida `permission_type_id`, `reason`, documentos y `skip_immediate_supervisor`.
- `PermissionType::getRequiredDocuments()` siempre retorna `[]`, aunque los seeders mantienen `requires_document` y `validation_rules.required_documents`.
- Existen dos fuentes de verdad para aprobaciones: metodos en `PermissionRequest` y metodos en `PermissionService`.
- Existe `app/Http/Controllers/PermissionRequestController copy.php`, lo cual indica codigo viejo o paralelo que debe eliminarse o archivarse fuera del runtime.

### 2.3 Aprobaciones

Archivos principales:

- `app/Models/Approval.php`
- `app/Http/Controllers/ApprovalController.php`
- `app/Services/PermissionService.php`
- `app/Http/Requests/ApprovePermissionRequest.php`
- `app/Http/Requests/RejectPermissionRequest.php`
- `resources/views/approvals/*`

Capacidades:

- Aprobacion nivel 1 por jefe inmediato.
- Aprobacion nivel 2 por RRHH.
- Rechazo con comentario.
- Acciones masivas.
- Caso especial de RRHH aprobando nivel 1 y nivel 2 cuando se omite jefe inmediato.

Observaciones criticas:

- `PermissionService::approve()` y `reject()` escriben `metadata` en `Approval`, pero la tabla `approvals` no tiene columna `metadata` y el modelo `Approval` tampoco la tiene en `$fillable`. Esto puede romper aprobaciones.
- El estado final de una solicitud aprobada vuelve a `approved` despues del retorno fisico. Eso mezcla "aprobacion administrativa" con "ciclo fisico de salida/retorno".
- La autorizacion esta distribuida entre middleware, `User::canApprove()`, metodos privados del controlador y validaciones dentro del servicio.

### 2.4 Firma digital / FIRMA PERU

Archivos principales:

- `app/Services/FirmaPeruService.php`
- `app/Http/Controllers/Api/FirmaPeruController.php`
- `app/Http/Controllers/SignatureController.php`
- `app/Models/DigitalSignature.php`
- `resources/js/firma-peru.js`
- `fwAuthorization.json`

Capacidades:

- Generacion de token JWT contra FIRMA PERU.
- Firma de empleado, jefe inmediato y RRHH.
- Descarga de documento original o firmado.
- Recepcion de PDF firmado.
- Registro de hash SHA256, metadata y ruta del documento.
- Verificacion de integridad por hash.

Observaciones criticas:

- `FirmaPeruService` lee credenciales desde `fwAuthorization.json` en la raiz. Ese archivo existe en el repo de trabajo y no deberia versionarse ni gestionarse fuera de `.env`/`config/services.php`.
- Hay logs con `client_id`, longitud de secreto, URL de token, previews de JWT y datos de cache. En produccion esto aumenta el riesgo operativo.
- La firma de empleado aparece como requerida en `canBeSubmitted()`, pero hay flujo `submitWithoutSignature()` que la omite. La regla de negocio debe quedar explicita.
- Hay codigo comentado largo y fallbacks complejos en `FirmaPeruService`; conviene aislar el protocolo externo en un cliente pequeno y testeable.

### 2.5 Seguimiento fisico y ZKTeco

Archivos principales:

- `app/Models/PermissionTracking.php`
- `app/Http/Controllers/PermissionTrackingController.php`
- `app/Http/Controllers/Api/AgentController.php`
- `app/Http/Controllers/AgentManagementController.php`
- `app/Http/Middleware/AuthenticateAgent.php`
- `app/Models/AgentToken.php`
- `zkteco-agent/*`

Capacidades:

- Registro de salida y retorno.
- Lectura por DNI.
- Actualizacion manual de salida y retorno.
- Deteccion de atraso.
- API de agentes: registro, heartbeat, empleados, eventos de acceso.
- Tokens de agente.

Observaciones criticas:

- `PermissionTrackingController::hrDashboard()` contiene `dd()` dentro del catch. Eso detiene produccion ante un error.
- `PermissionTracking::isOverdue()` usa 8 horas fijas mas 1 hora de tolerancia. No usa duracion real solicitada ni reglas por tipo.
- `registerDeparture()` cambia la solicitud a `in_progress`, pero la tabla `permission_requests` originalmente no tenia ese valor; fue agregado por migracion posterior. Esto esta bien solo si todas las bases tienen esa migracion aplicada.
- `AgentToken` guarda el token completo en base de datos. En V2 deberia guardarse hash del token.
- El agente y la API comparten conceptos, pero no hay tabla formal de agentes/dispositivos; el estado parece depender de configuracion/cache/logica.

### 2.6 Notificaciones

Archivos principales:

- `app/Models/Notification.php`
- `app/Helpers/NotificationHelper.php`
- `app/Http/Controllers/Api/NotificationController.php`
- `app/Jobs/SendEmailNotification.php`
- `app/Jobs/SendBulkNotifications.php`
- `resources/js/notifications.js`
- `resources/views/emails/*`

Capacidades:

- Notificaciones internas.
- Conteo de no leidas.
- Polling desde frontend.
- Correos asincronos por jobs.

Observaciones:

- El frontend tiene muchos `console.log()` de diagnostico.
- Las rutas de notificaciones estan bajo `routes/web.php` con prefijo `/api`, ademas de `routes/api.php`. Conviene separar API web autenticada de API publica/integracion.
- El polling puede mantenerse en V2, pero seria mejor normalizar eventos, canales y expiracion.

### 2.7 Reporteria

Archivos principales:

- `app/Http/Controllers/HRReportsController.php`
- `app/Exports/ReportExport.php`
- `resources/views/hr/reports/*`
- `resources/views/approvals/reports.blade.php`

Capacidades:

- Dashboard de RRHH.
- Reportes por estado, tipo, departamento.
- Tiempos de aprobacion.
- Ausentismo.
- Empleados activos.
- Desempeno de supervisores.
- Seguimiento en tiempo real.
- Tendencias temporales.
- Cumplimiento.
- Exportacion a Excel.

Observaciones:

- Los reportes dependen de campos removidos (`start_datetime`, `requested_hours`) en varios puntos.
- No se ve una capa de consultas/repositories para reportes; mucha consulta vive en controladores.
- La V2 deberia definir vistas/materializaciones o query objects para evitar reportes fragiles.

## 3. Hallazgos de riesgo prioritarios

### Riesgo 1: modelo de datos inconsistente con el codigo

Campos removidos de `permission_requests` siguen siendo usados:

- `start_datetime`
- `end_datetime`
- `requested_hours`

Impacto:

- Reportes pueden fallar.
- APIs de notificaciones pueden devolver errores.
- Validaciones de limites mensuales/semanales/anuales no funcionan correctamente.
- Seguimiento fisico no puede saber cuanto tiempo estaba autorizado el empleado.

Decision V2:

- Restaurar estos campos si la papeleta representa un permiso por rango horario.
- O crear una tabla `permission_periods` si una papeleta puede tener varios tramos.

Recomendacion: restaurar en V2 como campos obligatorios en la solicitud, salvo que el negocio confirme que solo se controlara salida/retorno real.

### Riesgo 2: aprobaciones escriben columna inexistente

`PermissionService` intenta guardar `metadata` en `approvals`, pero la migracion no crea esa columna.

Impacto:

- Aprobacion/rechazo puede fallar en runtime.
- No queda auditoria formal del metodo de aprobacion.

Decision V2:

- Agregar `metadata` JSON a `approvals`.
- Mejor aun: crear tabla `approval_actions` o `audit_events` para registrar eventos inmutables.

### Riesgo 3: estados mezclados

Actualmente `PermissionRequest.status` representa al mismo tiempo:

- Estado de solicitud administrativa.
- Estado de aprobacion.
- Estado de ejecucion fisica.

Ejemplo:

- `approved` significa aprobado administrativamente.
- Luego `in_progress` significa empleado salio.
- Luego vuelve a `approved` cuando retorna.

Impacto:

- Reportes ambiguos.
- Flujo dificil de mantener.
- Riesgo de permisos aprobados que parecen "no ejecutados" o retornados que parecen solo aprobados.

Decision V2:

- Separar estado administrativo y estado de uso.

Propuesta:

- `permission_requests.status`: `draft`, `submitted`, `pending_level_1`, `pending_level_2`, `approved`, `rejected`, `cancelled`, `expired`.
- `permission_trackings.status`: `not_started`, `out`, `returned`, `overdue`, `auto_closed`, `manually_closed`.

### Riesgo 4: autorizacion dispersa

Hay autorizacion en middleware, modelos, controladores y servicios.

Impacto:

- Es dificil garantizar que una accion sea segura desde todos los caminos.
- El mismo permiso puede ser permitido o negado segun ruta/controlador.

Decision V2:

- Usar Policies de Laravel:
  - `PermissionRequestPolicy`
  - `ApprovalPolicy`
  - `PermissionTrackingPolicy`
  - `DocumentPolicy`
  - `DigitalSignaturePolicy`
- Dejar los servicios para reglas de negocio, no para decidir acceso HTTP.

### Riesgo 5: secretos y tokens

Problemas:

- `fwAuthorization.json` en la raiz.
- Token de agente guardado en texto plano.
- Logs con informacion sensible.

Decision V2:

- Mover credenciales FIRMA PERU a `.env` y `config/services.php`.
- Guardar hash de tokens de agente.
- Sanitizar logs.
- Crear rotacion y revocacion formal.

### Riesgo 6: codigo de depuracion en runtime

Detectado:

- `dd()` en `PermissionTrackingController`.
- Muchos `console.log()` en vistas y JS.
- Logs excesivos en flujos normales.

Decision V2:

- Eliminar `dd()`.
- Pasar logs verbosos a `debug` y condicionar por ambiente.
- Crear logging estructurado solo para auditoria y errores.

## 4. Arquitectura objetivo para V2

### 4.1 Principios

- Dominio primero: la papeleta debe tener una definicion clara antes de la UI.
- Una sola fuente de verdad para estados y transiciones.
- Reglas parametrizables por tipo de permiso.
- Controladores delgados.
- Servicios pequenos y testeables.
- Autorizacion con Policies.
- Auditoria inmutable.
- Integraciones externas encapsuladas.
- Reportes sobre datos consistentes, no sobre suposiciones.

### 4.2 Capas propuestas

```text
app/
  Domain/
    Permissions/
      Models/
      Actions/
      Data/
      Enums/
      Policies/
      Rules/
      Services/
    Approvals/
    Signatures/
    Tracking/
    Notifications/
    Reports/
  Http/
    Controllers/
    Requests/
    Resources/
  Infrastructure/
    FirmaPeru/
    Zkteco/
    Pdf/
    Excel/
```

Si no se quiere mover todo a `Domain`, una version incremental puede mantener `app/Models`, `app/Services`, etc., pero ordenar por modulos:

```text
app/Services/Permissions
app/Services/Approvals
app/Services/Signatures
app/Services/Tracking
app/Services/Reports
```

### 4.3 Componentes V2 recomendados

#### PermissionWorkflow

Responsable de transiciones de solicitud:

- Crear borrador.
- Actualizar borrador.
- Enviar.
- Aprobar nivel 1.
- Aprobar nivel 2.
- Rechazar.
- Cancelar.
- Expirar.

Debe validar origen y destino de estado.

#### ApprovalWorkflow

Responsable de:

- Crear aprobaciones pendientes.
- Resolver aprobador actual.
- Permitir aprobacion especial de RRHH.
- Registrar accion de aprobacion/rechazo.
- Disparar eventos.

#### SignatureClient / FirmaPeruClient

Responsable unicamente de hablar con FIRMA PERU:

- Token.
- Parametros.
- Validacion de callback.
- Descarga y carga de documento.

No debe decidir estados de solicitud.

#### PermissionRulesEngine

Responsable de evaluar reglas:

- Limites por dia, mes, semana, ano.
- Documentos requeridos/recomendados.
- Reglas por tipo de permiso.
- Excepciones.

Debe leer reglas desde base de datos, no desde switches gigantes.

#### TrackingWorkflow

Responsable de:

- Registrar salida.
- Registrar retorno.
- Marcar atraso.
- Cerrar automaticamente.
- Calcular horas reales.
- Comparar contra horas autorizadas.

#### AuditLogger

Responsable de eventos inmutables:

- Solicitud creada.
- Solicitud enviada.
- Firma iniciada.
- Firma recibida.
- Aprobacion realizada.
- Rechazo realizado.
- Documento subido/eliminado.
- Salida registrada.
- Retorno registrado.
- Accion administrativa.

## 5. Modelo de datos recomendado para V2

### 5.1 Tablas base

#### users

Campos clave:

- `id`
- `dni`
- `first_name`
- `last_name`
- `email`
- `password`
- `department_id`
- `role_id` o roles Spatie
- `immediate_supervisor_id`
- `is_active`
- `last_login_at`
- `created_at`
- `updated_at`

Mejoras:

- Indice unico para `dni`.
- Indice para `department_id`, `role_id`, `immediate_supervisor_id`.
- Si se mantiene email, definir si es obligatorio.

#### departments

Campos clave:

- `id`
- `name`
- `code`
- `parent_id`
- `manager_id`
- `is_active`

Mejoras:

- `code` unico.
- Jerarquia formal con `parent_id`.

#### permission_types

Campos clave:

- `id`
- `code`
- `name`
- `description`
- `with_pay`
- `requires_document`
- `rules` JSON
- `is_active`

Ejemplo de `rules`:

```json
{
  "max_hours_per_day": 2,
  "max_hours_per_month": 6,
  "max_times_per_month": null,
  "allowed_weekdays": [1, 2, 3, 4, 5],
  "required_documents": [],
  "recommended_documents": ["certificado_medico"],
  "requires_compensation": false,
  "approval_flow": "standard_two_level"
}
```

### 5.2 Solicitudes

#### permission_requests

Campos recomendados:

- `id`
- `request_number`
- `user_id`
- `permission_type_id`
- `start_datetime`
- `end_datetime`
- `requested_minutes`
- `reason`
- `status`
- `submitted_at`
- `approved_at`
- `rejected_at`
- `cancelled_at`
- `current_approval_level`
- `metadata`
- `created_by`
- `updated_by`
- `created_at`
- `updated_at`

Estados administrativos:

- `draft`
- `submitted`
- `pending_immediate_boss`
- `pending_hr`
- `approved`
- `rejected`
- `cancelled`
- `expired`

Reglas:

- `request_number` unico.
- `requested_minutes` calculado desde fechas.
- `start_datetime` menor que `end_datetime`.
- No usar `approved` para indicar retorno fisico.

#### permission_request_documents

Puede mantenerse como `documents`, pero seria mas claro:

- `id`
- `permission_request_id`
- `document_type`
- `original_name`
- `stored_name`
- `disk`
- `path`
- `mime_type`
- `size_bytes`
- `sha256`
- `uploaded_by`
- `deleted_at`
- `created_at`

### 5.3 Aprobaciones

#### approvals

Campos recomendados:

- `id`
- `permission_request_id`
- `level`
- `approver_id`
- `assigned_role`
- `status`
- `comments`
- `decided_at`
- `decision_method`
- `metadata`
- `created_at`
- `updated_at`

Estados:

- `pending`
- `approved`
- `rejected`
- `skipped`
- `cancelled`

`decision_method`:

- `manual`
- `firma_peru`
- `automatic`
- `special_hr_override`

#### approval_actions

Tabla inmutable recomendada:

- `id`
- `approval_id`
- `permission_request_id`
- `actor_id`
- `action`
- `from_status`
- `to_status`
- `ip_address`
- `user_agent`
- `metadata`
- `created_at`

### 5.4 Firmas

#### digital_signatures

Mantener y mejorar:

- `id`
- `permission_request_id`
- `approval_id` nullable
- `user_id`
- `signature_stage`
- `provider`
- `certificate_serial`
- `signature_hash`
- `document_hash`
- `document_path`
- `signed_at`
- `is_valid`
- `validation_status`
- `certificate_data`
- `metadata`

Etapas:

- `employee`
- `immediate_boss`
- `hr`

### 5.5 Seguimiento

#### permission_trackings

Campos recomendados:

- `id`
- `permission_request_id`
- `employee_dni`
- `authorized_start_datetime`
- `authorized_end_datetime`
- `authorized_minutes`
- `departure_datetime`
- `return_datetime`
- `actual_minutes_used`
- `status`
- `departure_source`
- `return_source`
- `registered_by_user_id`
- `agent_id`
- `notes`
- `created_at`
- `updated_at`

Estados:

- `not_started`
- `out`
- `returned`
- `overdue`
- `auto_closed`
- `manually_closed`

### 5.6 Agentes ZKTeco

#### agents

Nueva tabla:

- `id`
- `agent_code`
- `name`
- `location`
- `zkteco_ip`
- `status`
- `last_heartbeat_at`
- `last_sync_at`
- `metadata`
- `is_active`

#### agent_tokens

Mejorar:

- `id`
- `agent_id`
- `name`
- `token_hash`
- `abilities`
- `last_used_at`
- `expires_at`
- `revoked_at`
- `created_at`

No guardar token plano.

#### access_events

Nueva tabla recomendada:

- `id`
- `agent_id`
- `employee_dni`
- `event_type`
- `event_datetime`
- `zkteco_event_id`
- `raw_payload`
- `processed_at`
- `processing_status`
- `permission_tracking_id`
- `created_at`

Esto permite auditar eventos aunque no encuentren tracking activo.

### 5.7 Auditoria

#### audit_events

Nueva tabla:

- `id`
- `actor_type`
- `actor_id`
- `event`
- `entity_type`
- `entity_id`
- `old_values`
- `new_values`
- `ip_address`
- `user_agent`
- `metadata`
- `created_at`

## 6. Flujo V2 recomendado

### 6.1 Creacion de papeleta

1. Usuario autenticado abre formulario.
2. Selecciona tipo de permiso.
3. Ingresa fecha/hora de inicio y fin.
4. Sistema calcula minutos solicitados.
5. Motor de reglas valida limites.
6. Usuario adjunta documentos requeridos o justifica pendiente si el negocio lo permite.
7. Solicitud queda en `draft`.

### 6.2 Envio

1. Usuario envia solicitud.
2. Si la firma de empleado es obligatoria, se exige firma previa.
3. Sistema crea aprobacion nivel 1.
4. Estado pasa a `pending_immediate_boss`.
5. Se registra auditoria.
6. Se notifica al aprobador.

### 6.3 Aprobacion nivel 1

1. Policy valida que el usuario pueda aprobar.
2. Si requiere FIRMA PERU, se inicia firma.
3. Al recibir firma, se registra `digital_signature`.
4. Se actualiza `approval` nivel 1.
5. Se crea aprobacion nivel 2.
6. Estado pasa a `pending_hr`.

### 6.4 Aprobacion RRHH

1. Policy valida rol RRHH.
2. RRHH firma o aprueba manualmente, segun regla.
3. Estado administrativo pasa a `approved`.
4. Se crea `permission_tracking` con ventana autorizada.
5. Se notifica al empleado.

### 6.5 Salida y retorno

1. ZKTeco envia evento.
2. Sistema guarda `access_event`.
3. `TrackingWorkflow` busca tracking activo por DNI y ventana.
4. Si esta `not_started`, registra salida.
5. Si esta `out` u `overdue`, registra retorno.
6. Calcula minutos reales.
7. Genera o actualiza PDF de control.
8. Registra auditoria.

## 7. Backlog priorizado para construir V2

### Fase 0: estabilizacion de V1 antes de migrar

Objetivo: que el sistema actual deje de tener errores evidentes mientras se planifica V2.

Tareas:

- Eliminar `dd()` de `PermissionTrackingController`.
- Agregar columna `metadata` a `approvals` o quitar escritura de metadata hasta crearla.
- Decidir si se restauran `start_datetime`, `end_datetime`, `requested_hours`.
- Eliminar o mover `PermissionRequestController copy.php`.
- Quitar logs sensibles de FIRMA PERU.
- Mover `fwAuthorization.json` a variables de entorno y sacarlo del control de versiones si aplica.
- Limpiar `console.log()` en assets y vistas.
- Revisar rutas `/api` definidas en `web.php`.

### Fase 1: dominio y datos

Objetivo: definir la estructura que no se va a romper.

Tareas:

- Crear enums para estados:
  - `PermissionRequestStatus`
  - `ApprovalStatus`
  - `TrackingStatus`
  - `SignatureStage`
- Crear migraciones V2 para restaurar o normalizar fecha/hora y minutos solicitados.
- Crear `audit_events`.
- Crear `access_events`.
- Crear tabla `agents`.
- Cambiar `agent_tokens.token` a `token_hash`.
- Agregar `metadata` a `approvals`.
- Documentar reglas por tipo de permiso en JSON.

### Fase 2: servicios y workflows

Objetivo: sacar logica critica de controladores y modelos.

Tareas:

- Crear `PermissionWorkflow`.
- Crear `ApprovalWorkflow`.
- Crear `TrackingWorkflow`.
- Crear `PermissionRulesEngine`.
- Crear `FirmaPeruClient`.
- Crear `PdfService` con interfaz clara.
- Crear `AuditService`.
- Reducir `PermissionRequest` a relaciones, casts y helpers simples.

### Fase 3: autorizacion y seguridad

Objetivo: permisos consistentes.

Tareas:

- Crear policies.
- Reemplazar checks manuales por `$this->authorize()`.
- Normalizar roles.
- Decidir si se adopta Spatie o se elimina la dependencia.
- Hashear tokens de agente.
- Sanitizar logs.
- Validar y limitar endpoints publicos de FIRMA PERU.
- Agregar rate limiting a endpoints de agente y firma.

### Fase 4: UI ordenada

Objetivo: experiencia clara para cada rol.

Pantallas:

- Empleado:
  - Mis papeletas.
  - Nueva papeleta.
  - Detalle con timeline.
  - Documentos y firma.
- Jefe inmediato:
  - Bandeja de aprobacion.
  - Detalle de solicitud.
  - Historial.
- RRHH:
  - Bandeja final.
  - Control de salidas/retornos.
  - Reportes.
  - Papeleta directa.
- Admin:
  - Usuarios.
  - Departamentos.
  - Roles.
  - Agentes.
  - Configuracion de reglas.

Mejoras UI:

- Timeline unico por solicitud.
- Estados visibles y consistentes.
- Acciones disponibles segun policy.
- Menos pantallas duplicadas.
- Componentes Blade reutilizables para badges, tablas, filtros y modales.

### Fase 5: reporteria

Objetivo: reportes confiables.

Tareas:

- Crear query objects por reporte.
- Basar reportes en `requested_minutes`, `actual_minutes_used`, estados y auditoria.
- Exportacion Excel por jobs si el reporte es pesado.
- Dashboard RRHH con indicadores clave:
  - pendientes por nivel
  - aprobadas del mes
  - rechazadas del mes
  - personas fuera ahora
  - retornos tardios
  - tiempo promedio de aprobacion
  - permisos por tipo/departamento

### Fase 6: pruebas

Objetivo: que la V2 sea mantenible.

Pruebas minimas:

- Crear solicitud valida.
- Rechazar solicitud.
- Aprobar flujo normal jefe + RRHH.
- Aprobar flujo especial RRHH sin jefe inmediato.
- Enviar con firma obligatoria.
- Enviar sin firma si la regla lo permite.
- Registrar salida.
- Registrar retorno.
- Marcar atraso.
- Procesar evento ZKTeco sin tracking.
- Validar limites por tipo de permiso.
- Ver permisos de cada rol.
- Descargar PDF solo con autorizacion.
- Callback FIRMA PERU con token valido/invalido.

## 8. Cambios concretos recomendados en codigo

### 8.1 Rutas

Separar:

- `routes/web.php`: pantallas Blade.
- `routes/api.php`: integraciones externas y API JSON real.
- `routes/internal-api.php` opcional: AJAX autenticado desde Blade.

Evitar prefijo `/api` dentro de `web.php`.

### 8.2 Controladores

Controladores V2:

- `PermissionRequestController`: CRUD de solicitudes.
- `PermissionSubmissionController`: envio/cancelacion.
- `ApprovalController`: bandeja y decision.
- `SignatureController`: vistas de firma.
- `FirmaPeruWebhookController`: callbacks.
- `TrackingController`: vistas.
- `TrackingApiController`: acciones JSON.
- `AgentApiController`: ZKTeco.
- `ReportController`: vistas.
- `ReportExportController`: descargas.

### 8.3 Servicios

Servicios V2:

- `PermissionWorkflow`
- `PermissionRulesEngine`
- `ApprovalWorkflow`
- `SignatureWorkflow`
- `FirmaPeruClient`
- `TrackingWorkflow`
- `NotificationService`
- `AuditService`
- `PdfGenerator`
- `ReportQueryService`

### 8.4 Eventos

Eventos recomendados:

- `PermissionCreated`
- `PermissionSubmitted`
- `PermissionApproved`
- `PermissionRejected`
- `PermissionCancelled`
- `SignatureStarted`
- `SignatureCompleted`
- `TrackingDepartureRegistered`
- `TrackingReturnRegistered`
- `TrackingMarkedOverdue`
- `AgentHeartbeatReceived`
- `AccessEventReceived`

Listeners:

- Crear notificaciones internas.
- Enviar correos.
- Registrar auditoria.
- Generar documentos.

## 9. Configuracion recomendada

### .env

```env
FIRMA_PERU_CLIENT_ID=
FIRMA_PERU_CLIENT_SECRET=
FIRMA_PERU_TOKEN_URL=
FIRMA_PERU_JS_URL=https://apps.firmaperu.gob.pe/web/clienteweb/firmaperu.min.js
FIRMA_PERU_VERIFY_SSL=true

ZKTECO_AGENT_TOKEN_PREFIX=zka_
ZKTECO_HEARTBEAT_TIMEOUT_SECONDS=90

PERMISSION_DEFAULT_GRACE_MINUTES=60
PERMISSION_REQUIRE_EMPLOYEE_SIGNATURE=false
```

### config/services.php

```php
'firma_peru' => [
    'client_id' => env('FIRMA_PERU_CLIENT_ID'),
    'client_secret' => env('FIRMA_PERU_CLIENT_SECRET'),
    'token_url' => env('FIRMA_PERU_TOKEN_URL'),
    'js_url' => env('FIRMA_PERU_JS_URL'),
    'verify_ssl' => env('FIRMA_PERU_VERIFY_SSL', true),
],
```

## 10. Plan de migracion de V1 a V2

### Paso 1: congelar reglas

Antes de tocar codigo, confirmar con RRHH:

- Firma del empleado: obligatoria u opcional.
- Documentos: obligatorios, recomendados o subsanables.
- Todos los permisos requieren RRHH o algunos terminan en jefe inmediato.
- Duracion autorizada: se captura en solicitud o solo se mide salida/retorno.
- Tolerancia de retorno.
- Que hacer si empleado no registra retorno.

### Paso 2: migracion de base de datos

- Agregar campos faltantes.
- Poblar `requested_minutes` desde datos historicos si existen.
- Crear auditoria inicial desde `created_at`, `submitted_at`, aprobaciones y tracking.
- Crear `agents` desde configuracion actual.
- Migrar tokens a hash y regenerar tokens activos.

### Paso 3: compatibilidad

- Mantener rutas antiguas con redirects o wrappers.
- Mantener vistas actuales mientras se cambian servicios.
- Cambiar primero backend y pruebas, luego UI.

### Paso 4: despliegue

- Entorno staging con copia de base real anonimizada.
- Ejecutar pruebas.
- Simular firma.
- Simular eventos ZKTeco.
- Validar reportes con RRHH.
- Desplegar en ventana controlada.

## 11. Criterios de aceptacion V2

La V2 se considera lista cuando:

- Una solicitud tiene fechas, horas y estado claro.
- No hay referencias a columnas inexistentes.
- Cada transicion de estado esta centralizada y testeada.
- Cada accion sensible pasa por policy.
- FIRMA PERU no expone secretos en logs.
- ZKTeco guarda eventos aunque no haya permiso activo.
- Reportes usan datos consistentes.
- El dashboard de cada rol muestra solo lo que corresponde.
- Hay pruebas feature del flujo completo.
- El README explica instalacion real del sistema, no el texto base de Laravel.

## 12. Primer sprint recomendado

Duracion sugerida: 1 a 2 semanas.

Entregables:

1. Arreglo de fallos criticos V1:
   - `approvals.metadata`
   - `dd()` eliminado
   - logs sensibles reducidos
   - columnas fecha/hora restauradas o referencias retiradas
2. Enums de estados.
3. `PermissionWorkflow` inicial.
4. `ApprovalWorkflow` inicial.
5. Policies principales.
6. Pruebas del flujo normal y flujo especial RRHH.
7. README actualizado con instalacion real.

## 13. Segundo sprint recomendado

Duracion sugerida: 2 semanas.

Entregables:

1. `TrackingWorkflow`.
2. Tabla `access_events`.
3. Tabla `agents`.
4. Tokens de agente con hash.
5. Refactor de `AgentController`.
6. Reporte basico de eventos y tracking.
7. Pruebas de salida/retorno por API.

## 14. Tercer sprint recomendado

Duracion sugerida: 2 semanas.

Entregables:

1. `FirmaPeruClient`.
2. Configuracion por `.env`.
3. Limpieza de `FirmaPeruService`.
4. Auditoria de firmas.
5. Pruebas de callbacks.
6. UI de timeline con firmas, aprobaciones, documentos y tracking.

## 15. Deuda tecnica puntual detectada

Lista directa para atacar:

- `PermissionRequestController copy.php` debe salir del codigo activo.
- `README.md` sigue siendo el README base de Laravel.
- `DOCUMENTACION_TECNICA.md` esta util, pero no refleja inconsistencias actuales.
- `PermissionType::getRequiredDocuments()` contradice seeders y requests.
- `PermissionValidationService` usa campos eliminados.
- `NotificationController` usa campos eliminados.
- `PermissionTrackingController` usa campos eliminados.
- `Approval` no tiene `metadata`, pero el servicio intenta escribirla.
- `PermissionTrackingController::hrDashboard()` contiene `dd()`.
- `FirmaPeruService` lee `fwAuthorization.json` directamente.
- `AgentToken` guarda token en texto plano.
- Rutas API internas estan mezcladas en `web.php`.
- Logs de debug y `console.log()` permanecen en vistas/assets.
- Estado `approved` se usa para conceptos distintos.
- Hay logica de aprobacion duplicada en modelo y servicio.

## 16. Recomendacion final

No conviene reescribir todo desde cero sin controlar datos y reglas. La mejor Version 2 es una reconstruccion incremental: primero estabilizar V1, luego introducir workflows y policies, despues normalizar datos, y finalmente redisenar UI/reportes sobre una base limpia.

La prioridad tecnica mas alta es alinear base de datos, reglas y estados. Una vez eso este claro, FIRMA PERU, ZKTeco, reportes y notificaciones se vuelven modulos manejables en vez de parches conectados por excepciones.

