# Diseno del nuevo Sistema de Papeletas Digitales V2

Fecha: 2026-05-08  
Proyecto: Sistema de Papeletas Digitales  
Enfoque: mantener el patron visual y operativo del sistema original, pero con una organizacion interna mas clara, mantenible y preparada para casos reales de encargaturas, multiples aprobadores RRHH y seguimiento fisico sin horario declarado por el usuario.

## 1. Objetivo de la V2

La Version 2 busca reconstruir el sistema actual de papeletas digitales conservando su forma de uso principal:

- Laravel con Blade.
- Vistas administrativas similares al sistema actual.
- Flujo de empleado, jefe/encargado, RRHH y administracion.
- Firma digital con FIRMA PERU.
- Seguimiento fisico de salida y retorno.
- Reporteria para RRHH.

La mejora principal sera interna: separar responsabilidades, formalizar modelos, ordenar reglas de negocio, soportar encargaturas temporales y permitir varios aprobadores RRHH sin depender de un unico usuario.

## 2. Reglas de negocio confirmadas para V2

### 2.1 El usuario no declara hora de salida ni retorno

En la solicitud de permiso, el empleado no indica una hora exacta de salida ni de llegada. El sistema solo registra:

- Tipo de permiso.
- Motivo.
- Documentos adjuntos si aplica.
- Oficina/unidad del empleado.
- Estado de aprobacion.

La salida y el retorno reales se controlan despues, en el modulo de seguimiento, manualmente por RRHH o mediante dispositivos ZKTeco.

Implicacion tecnica:

- La tabla principal de solicitudes no debe depender de `start_datetime`, `end_datetime` ni `requested_hours`.
- Las horas reales pertenecen al seguimiento, no a la solicitud.
- Los reportes de ausentismo se calculan desde `permission_trackings`.

### 2.2 Encargados temporales de oficina

Un encargado de una oficina puede encargarse temporalmente de otra oficina o unidad. Durante ese periodo puede aprobar papeletas de empleados de ambas unidades.

Ejemplo:

- Usuario A es encargado de Oficina 1.
- Temporalmente tambien queda encargado de Oficina 2.
- Usuario A puede aprobar solicitudes de empleados de Oficina 1 y Oficina 2 mientras la encargatura este vigente.

Implicacion tecnica:

- No basta con `users.immediate_supervisor_id`.
- Debe existir una tabla de encargaturas por unidad/oficina.
- La resolucion de aprobador debe considerar encargaturas activas por fecha.

### 2.3 Multiples aprobadores de RRHH

Puede haber varios usuarios con capacidad de aprobar como RRHH. Cualquier aprobador RRHH activo puede tomar una solicitud pendiente de RRHH.

Implicacion tecnica:

- La aprobacion RRHH no debe asignarse obligatoriamente a un unico usuario.
- Puede crearse una aprobacion pendiente con rol objetivo `rrhh`.
- Cuando un aprobador RRHH decide, se registra su usuario como `decided_by`.

### 2.4 Se conserva el patron de diseno original

La V2 no sera un cambio radical visual. Se mantiene:

- Layout administrativo.
- Blade + Tailwind.
- Tablas, filtros, badges y formularios similares.
- Dashboard por rol.
- Flujo de aprobaciones parecido.

La mejora sera:

- Componentes Blade reutilizables.
- Menos duplicacion.
- Vistas mas consistentes.
- Estados claros.
- Controladores mas pequenos.

## 3. Roles del sistema

### 3.1 Empleado

Puede:

- Crear solicitudes.
- Ver sus solicitudes.
- Editar solicitudes en borrador.
- Cancelar solicitudes antes de aprobacion final.
- Adjuntar documentos.
- Firmar si el flujo lo requiere.
- Descargar PDF.
- Ver seguimiento de sus permisos.

### 3.2 Encargado o jefe de oficina

Puede:

- Ver solicitudes pendientes de sus oficinas asignadas.
- Aprobar o rechazar solicitudes.
- Ver historial de solicitudes de empleados bajo su responsabilidad.
- Aprobar tambien oficinas encargadas temporalmente.

La condicion importante es que su poder de aprobacion viene de la oficina/unidad, no solo de ser supervisor directo del empleado.

### 3.3 Aprobador RRHH

Puede:

- Ver todas las solicitudes pendientes de RRHH.
- Aprobar o rechazar como segunda instancia.
- Crear solicitudes directas autorizadas si se mantiene esa funcionalidad.
- Controlar salidas y retornos.
- Ver reportes.

Puede haber varios.

### 3.4 Administrador

Puede:

- Gestionar usuarios.
- Gestionar oficinas/departamentos/unidades.
- Gestionar roles.
- Gestionar encargaturas temporales.
- Gestionar tipos de permiso.
- Gestionar aprobadores RRHH.
- Gestionar agentes ZKTeco.
- Ver auditoria.

## 4. Modulos del sistema V2

## 4.1 Modulo de usuarios y oficinas

Responsable de:

- Usuarios.
- Roles.
- Oficinas/unidades.
- Encargados oficiales.
- Encargaturas temporales.
- Estado activo/inactivo de usuarios.

### Modelos

#### User

Representa a un usuario del sistema.

Campos:

- `id`
- `dni`
- `first_name`
- `last_name`
- `name`
- `email`
- `password`
- `office_id`
- `role_id`
- `is_active`
- `email_verified_at`
- `last_login_at`
- `created_at`
- `updated_at`

Relaciones:

- Pertenece a `Office`.
- Pertenece a `Role`.
- Tiene muchas `PermissionRequest`.
- Tiene muchas `ApprovalDecision`.
- Tiene muchas `OfficeAssignment` como encargado.
- Tiene muchas `TemporaryOfficeAssignment`.

Metodos utiles:

- `fullName()`
- `hasRole(string|array $roles)`
- `isActive()`
- `activeAssignedOffices()`
- `canManageOffice(Office $office)`

#### Role

Representa roles internos.

Roles sugeridos:

- `empleado`
- `encargado_oficina`
- `rrhh`
- `admin`

Campos:

- `id`
- `name`
- `display_name`
- `description`
- `is_active`
- `created_at`
- `updated_at`

#### Office

Representa oficina, unidad, area o departamento.

Campos:

- `id`
- `code`
- `name`
- `parent_id`
- `is_active`
- `created_at`
- `updated_at`

Relaciones:

- Tiene muchos usuarios.
- Puede tener oficina padre.
- Puede tener suboficinas.
- Tiene encargados oficiales.
- Tiene encargaturas temporales.

#### OfficeAssignment

Representa el encargado oficial o permanente de una oficina.

Campos:

- `id`
- `office_id`
- `user_id`
- `assignment_type`
- `is_active`
- `started_at`
- `ended_at`
- `created_by`
- `created_at`
- `updated_at`

Valores de `assignment_type`:

- `primary_manager`
- `secondary_manager`

Uso:

- Define quien aprueba normalmente las papeletas de una oficina.
- Permite tener mas de un encargado si la municipalidad lo requiere.

#### TemporaryOfficeAssignment

Representa encargaturas temporales.

Campos:

- `id`
- `office_id`
- `user_id`
- `reason`
- `starts_at`
- `ends_at`
- `is_active`
- `created_by`
- `cancelled_by`
- `cancelled_at`
- `created_at`
- `updated_at`

Reglas:

- Solo aplica si `is_active = true`.
- Solo aplica si la fecha actual esta entre `starts_at` y `ends_at`.
- Permite que un encargado apruebe solicitudes de otra oficina temporalmente.

Proceso:

1. Admin registra encargatura temporal.
2. El usuario encargado aparece como aprobador valido de esa oficina.
3. Al vencer el periodo, deja de aprobar automaticamente.
4. Se registra auditoria.

## 4.2 Modulo de tipos de permiso

Responsable de:

- Catalogo de tipos de papeleta.
- Reglas por tipo.
- Documentos requeridos o recomendados.
- Flujo de aprobacion aplicable.

### Modelo PermissionType

Campos:

- `id`
- `code`
- `name`
- `description`
- `with_pay`
- `requires_document`
- `document_policy`
- `approval_flow`
- `rules`
- `is_active`
- `created_at`
- `updated_at`

Valores de `document_policy`:

- `none`: no pide documento.
- `recommended`: sugiere documento, pero permite enviar.
- `required_before_submit`: exige documento antes de enviar.
- `required_before_final_approval`: permite enviar, pero RRHH no aprueba sin documento.

Valores de `approval_flow`:

- `office_then_hr`: encargado de oficina y luego RRHH.
- `hr_only`: solo RRHH.
- `office_only`: solo encargado de oficina.
- `direct_approved_by_hr`: creado y aprobado por RRHH.

Ejemplo de `rules`:

```json
{
  "recommended_documents": ["certificado_medico"],
  "required_documents": [],
  "max_uses_per_month": null,
  "max_real_minutes_per_day": null,
  "requires_employee_signature": false,
  "requires_manager_signature": true,
  "requires_hr_signature": true
}
```

Nota:

Como el usuario no indica hora de salida ni retorno, limites por horas deben evaluarse despues del seguimiento, no al crear la solicitud.

## 4.3 Modulo de solicitudes de permiso

Responsable de:

- Crear papeletas.
- Enviar a aprobacion.
- Mostrar estado.
- Asociar documentos.
- Asociar aprobaciones.
- Generar PDF.

### Modelo PermissionRequest

Campos:

- `id`
- `request_number`
- `user_id`
- `office_id`
- `permission_type_id`
- `reason`
- `status`
- `current_approval_step`
- `submitted_at`
- `approved_at`
- `rejected_at`
- `cancelled_at`
- `metadata`
- `created_at`
- `updated_at`

Campos importantes:

- `office_id` guarda la oficina del empleado al momento de solicitar. Esto conserva historial aunque luego el empleado cambie de oficina.
- No se guardan horas planificadas.
- Las horas reales se calculan en `PermissionTracking`.

Estados:

- `draft`: borrador.
- `submitted`: enviada, preparando aprobaciones.
- `pending_office`: pendiente de encargado/jefe de oficina.
- `pending_hr`: pendiente de RRHH.
- `approved`: aprobada administrativamente.
- `rejected`: rechazada.
- `cancelled`: cancelada.
- `expired`: vencida sin uso, si se implementa caducidad.

Relaciones:

- Pertenece a `User`.
- Pertenece a `Office`.
- Pertenece a `PermissionType`.
- Tiene muchos `PermissionDocument`.
- Tiene muchos `Approval`.
- Tiene uno `PermissionTracking`.
- Tiene muchas `DigitalSignature`.
- Tiene muchos `AuditEvent`.

Metodos utiles:

- `isDraft()`
- `canBeEdited()`
- `canBeCancelled()`
- `isApproved()`
- `requiresHrApproval()`
- `getCurrentApproval()`
- `getStatusLabel()`
- `getStatusColor()`

### Modelo PermissionDocument

Campos:

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
- `updated_at`

Procesos:

- Subir documento.
- Validar extension y MIME.
- Calcular hash.
- Permitir eliminacion solo si la solicitud sigue editable o segun regla RRHH.
- Conservar auditoria.

## 4.4 Modulo de aprobaciones

Responsable de:

- Resolver aprobadores.
- Crear pasos de aprobacion.
- Registrar aprobacion o rechazo.
- Soportar varios RRHH.
- Soportar encargaturas temporales.

### Modelo Approval

Representa un paso de aprobacion.

Campos:

- `id`
- `permission_request_id`
- `step`
- `approval_type`
- `target_role`
- `target_office_id`
- `assigned_user_id`
- `status`
- `decided_by`
- `comments`
- `decided_at`
- `decision_method`
- `metadata`
- `created_at`
- `updated_at`

Valores de `approval_type`:

- `office_manager`
- `hr`
- `admin_override`

Valores de `target_role`:

- `encargado_oficina`
- `rrhh`
- `admin`

Valores de `status`:

- `pending`
- `approved`
- `rejected`
- `skipped`
- `cancelled`

Valores de `decision_method`:

- `manual`
- `firma_peru`
- `automatic`
- `override`

Reglas:

- Para aprobacion de oficina:
  - `target_office_id` identifica la oficina cuyo encargado debe aprobar.
  - `assigned_user_id` puede ser nulo si hay varios encargados validos.
  - `decided_by` guarda quien aprobo realmente.
- Para aprobacion RRHH:
  - `target_role = rrhh`.
  - `assigned_user_id` normalmente nulo.
  - Cualquier usuario RRHH activo puede decidir.

### Modelo ApprovalDecision

Opcional pero recomendado para historial inmutable de decisiones.

Campos:

- `id`
- `approval_id`
- `permission_request_id`
- `actor_id`
- `action`
- `from_status`
- `to_status`
- `comments`
- `ip_address`
- `user_agent`
- `metadata`
- `created_at`

Valores de `action`:

- `approved`
- `rejected`
- `reassigned`
- `skipped`
- `cancelled`

### Servicio ApprovalResolver

Responsable de responder:

- Que oficinas puede aprobar un usuario.
- Que usuarios pueden aprobar una solicitud.
- Si una encargatura temporal aplica.
- Si un usuario RRHH puede tomar una solicitud.

Reglas de resolucion:

1. Obtener `office_id` de la solicitud.
2. Buscar encargados oficiales activos en `office_assignments`.
3. Buscar encargados temporales activos en `temporary_office_assignments`.
4. Un usuario es aprobador valido si esta en cualquiera de esos grupos.
5. Para RRHH, cualquier usuario activo con rol `rrhh` puede aprobar.

### Servicio ApprovalWorkflow

Responsable de:

- Crear aprobacion de oficina.
- Aprobar paso actual.
- Rechazar paso actual.
- Crear siguiente paso.
- Finalizar solicitud.
- Disparar eventos.

Flujo normal:

1. Solicitud pasa a `pending_office`.
2. Se crea `Approval` tipo `office_manager`.
3. Encargado oficial o temporal aprueba.
4. Solicitud pasa a `pending_hr`.
5. Se crea `Approval` tipo `hr`.
6. Cualquier RRHH activo aprueba.
7. Solicitud pasa a `approved`.
8. Se crea `PermissionTracking`.

## 4.5 Modulo de firma digital

Responsable de:

- Preparar firma con FIRMA PERU.
- Recibir documento firmado.
- Guardar evidencia.
- Asociar firma a solicitud y aprobacion.

### Modelo DigitalSignature

Campos:

- `id`
- `permission_request_id`
- `approval_id`
- `user_id`
- `provider`
- `signature_stage`
- `certificate_serial`
- `signature_hash`
- `document_hash`
- `document_path`
- `signed_at`
- `is_valid`
- `validation_status`
- `certificate_data`
- `metadata`
- `created_at`
- `updated_at`

Valores de `provider`:

- `firma_peru`
- `manual_upload`

Valores de `signature_stage`:

- `employee`
- `office_manager`
- `hr`

Reglas:

- Si el tipo de permiso exige firma del encargado, la aprobacion puede pedir FIRMA PERU antes de finalizar el paso.
- Si hay varios RRHH, firma quien toma la decision.
- Las firmas deben asociarse a `approval_id` cuando correspondan.

### Servicio SignatureWorkflow

Responsable de:

- Validar si el usuario puede firmar.
- Iniciar proceso FIRMA PERU.
- Guardar token temporal.
- Procesar callback.
- Crear `DigitalSignature`.
- Avisar a `ApprovalWorkflow` cuando la firma completa una aprobacion.

### Servicio FirmaPeruClient

Responsable solo de integracion externa:

- Generar JWT.
- Armar parametros.
- Validar callback.
- Descargar/subir documento.

No debe:

- Aprobar solicitudes.
- Decidir roles.
- Cambiar estados de negocio.

## 4.6 Modulo de seguimiento fisico

Responsable de:

- Controlar salida real.
- Controlar retorno real.
- Calcular tiempo usado.
- Marcar atrasos.
- Integrarse con ZKTeco.

### Modelo PermissionTracking

Campos:

- `id`
- `permission_request_id`
- `employee_dni`
- `status`
- `departure_datetime`
- `return_datetime`
- `actual_minutes_used`
- `departure_source`
- `return_source`
- `registered_by_user_id`
- `departure_agent_id`
- `return_agent_id`
- `notes`
- `created_at`
- `updated_at`

Estados:

- `pending_departure`: aprobado, aun no salio.
- `out`: empleado fuera.
- `returned`: retorno registrado.
- `overdue`: retorno pendiente fuera de tolerancia.
- `auto_closed`: cerrado automaticamente.
- `cancelled`: seguimiento cancelado por anulacion administrativa.

Valores de source:

- `manual`
- `zkteco`
- `system`

Reglas:

- Se crea cuando la solicitud queda `approved`.
- No requiere hora planificada.
- `actual_minutes_used` se calcula entre salida y retorno.
- El atraso puede calcularse con una tolerancia general o por tipo de permiso si RRHH define limites.

### Modelo AccessEvent

Registra eventos enviados por ZKTeco aunque no se puedan procesar.

Campos:

- `id`
- `agent_id`
- `employee_dni`
- `event_type`
- `event_datetime`
- `zkteco_event_id`
- `raw_payload`
- `processing_status`
- `processing_message`
- `permission_tracking_id`
- `processed_at`
- `created_at`

Valores de `event_type`:

- `entry`
- `exit`

Valores de `processing_status`:

- `pending`
- `processed`
- `ignored`
- `error`

### Servicio TrackingWorkflow

Responsable de:

- Registrar salida.
- Registrar retorno.
- Actualizar salida/retorno manualmente.
- Marcar atraso.
- Cerrar automaticamente.
- Procesar evento ZKTeco.

Proceso manual:

1. RRHH escanea o ingresa DNI.
2. Sistema busca `PermissionTracking` activo.
3. Si esta `pending_departure`, registra salida.
4. Si esta `out` u `overdue`, registra retorno.
5. Calcula minutos usados.

Proceso ZKTeco:

1. Agente envia evento.
2. Sistema guarda `AccessEvent`.
3. Busca empleado por DNI.
4. Busca tracking activo.
5. Procesa salida o retorno.
6. Marca evento como procesado o ignorado.

## 4.7 Modulo de agentes ZKTeco

### Modelo Agent

Campos:

- `id`
- `code`
- `name`
- `location`
- `zkteco_ip`
- `status`
- `last_heartbeat_at`
- `last_sync_at`
- `metadata`
- `is_active`
- `created_at`
- `updated_at`

Estados:

- `online`
- `offline`
- `maintenance`

### Modelo AgentToken

Campos:

- `id`
- `agent_id`
- `name`
- `token_hash`
- `abilities`
- `last_used_at`
- `expires_at`
- `revoked_at`
- `created_at`
- `updated_at`

Reglas:

- El token completo solo se muestra al crearlo.
- La base de datos guarda hash.
- Se puede revocar.
- Se puede limitar por abilities.

## 4.8 Modulo de notificaciones

### Modelo Notification

Campos:

- `id`
- `user_id`
- `sender_id`
- `type`
- `title`
- `message`
- `data`
- `read_at`
- `expires_at`
- `created_at`
- `updated_at`

Eventos que generan notificaciones:

- Solicitud enviada.
- Solicitud pendiente de encargado.
- Solicitud pendiente de RRHH.
- Solicitud aprobada.
- Solicitud rechazada.
- Solicitud lista para salida.
- Retorno tardio.
- Encargatura temporal asignada.

## 4.9 Modulo de auditoria

### Modelo AuditEvent

Campos:

- `id`
- `actor_id`
- `actor_type`
- `event`
- `entity_type`
- `entity_id`
- `old_values`
- `new_values`
- `ip_address`
- `user_agent`
- `metadata`
- `created_at`

Eventos:

- `permission.created`
- `permission.submitted`
- `permission.approved`
- `permission.rejected`
- `permission.cancelled`
- `document.uploaded`
- `document.deleted`
- `signature.completed`
- `tracking.departure_registered`
- `tracking.return_registered`
- `office.temporary_assignment_created`
- `agent.access_event_received`

## 5. Procesos principales

## 5.1 Crear solicitud

Actor: empleado.

Pasos:

1. Empleado entra a "Nueva papeleta".
2. Selecciona tipo de permiso.
3. Escribe motivo.
4. Adjunta documentos si desea o si el tipo lo exige.
5. Guarda como borrador.

Resultado:

- `PermissionRequest` en estado `draft`.
- `office_id` queda fijado con la oficina actual del empleado.
- Auditoria `permission.created`.

## 5.2 Enviar solicitud

Actor: empleado.

Pasos:

1. Empleado presiona "Enviar".
2. Sistema valida documentos segun `document_policy`.
3. Sistema valida que el usuario este activo.
4. Sistema determina flujo de aprobacion segun `approval_flow`.
5. Sistema crea primer `Approval`.
6. Solicitud pasa a `pending_office` o `pending_hr`.
7. Se notifica a aprobadores.

Resultado:

- Solicitud enviada.
- Primer paso pendiente.

## 5.3 Aprobacion por encargado de oficina

Actor: encargado oficial o encargado temporal.

Pasos:

1. Encargado entra a bandeja.
2. Sistema muestra solicitudes de oficinas que puede aprobar.
3. Encargado revisa solicitud.
4. Aprueba o rechaza.
5. Si aprueba, se registra `decided_by`.
6. Si el flujo requiere firma, se inicia FIRMA PERU o se exige completar firma antes de aprobar.
7. Solicitud pasa a RRHH si corresponde.

Resultado si aprueba:

- `Approval` nivel oficina en `approved`.
- Solicitud en `pending_hr`.
- `Approval` RRHH creada.

Resultado si rechaza:

- Solicitud en `rejected`.
- No avanza a RRHH.

## 5.4 Aprobacion por RRHH

Actor: cualquier usuario RRHH activo.

Pasos:

1. RRHH entra a bandeja.
2. Ve todas las solicitudes `pending_hr`.
3. Toma una solicitud.
4. Aprueba o rechaza.
5. Si aprueba, puede firmar si el tipo lo exige.
6. Sistema finaliza solicitud.
7. Sistema crea `PermissionTracking`.

Resultado si aprueba:

- Solicitud `approved`.
- Seguimiento `pending_departure`.
- Empleado queda autorizado para salir.

Resultado si rechaza:

- Solicitud `rejected`.
- No se crea seguimiento.

## 5.5 Encargatura temporal

Actor: admin.

Pasos:

1. Admin abre modulo de encargaturas.
2. Selecciona oficina.
3. Selecciona usuario encargado.
4. Define fecha inicio y fecha fin.
5. Registra motivo.
6. Sistema activa encargatura.

Resultado:

- `TemporaryOfficeAssignment` activa.
- Usuario puede aprobar solicitudes de esa oficina durante el periodo.
- Auditoria registrada.

Consideraciones:

- Si ya hay solicitudes pendientes de esa oficina, el encargado temporal debe poder verlas inmediatamente.
- Si la encargatura vence, ya no puede aprobar nuevas decisiones.
- Las decisiones tomadas durante vigencia siguen siendo validas.

## 5.6 Registro de salida

Actor: RRHH o agente ZKTeco.

Pasos:

1. Se recibe DNI.
2. Sistema busca tracking activo en `pending_departure`.
3. Registra `departure_datetime`.
4. Cambia tracking a `out`.
5. Registra fuente manual o ZKTeco.

Resultado:

- Empleado queda marcado fuera.
- Auditoria de salida.

## 5.7 Registro de retorno

Actor: RRHH o agente ZKTeco.

Pasos:

1. Se recibe DNI.
2. Sistema busca tracking activo en `out` u `overdue`.
3. Registra `return_datetime`.
4. Calcula `actual_minutes_used`.
5. Cambia tracking a `returned`.
6. Actualiza PDF si corresponde.

Resultado:

- Seguimiento cerrado.
- Horas reales disponibles para reportes.

## 5.8 Marcar atrasos

Actor: sistema programado o RRHH.

Pasos:

1. Comando programado revisa trackings en `out`.
2. Aplica regla de tolerancia.
3. Marca `overdue` si corresponde.
4. Notifica a RRHH.

Nota:

Como no hay hora planificada en la solicitud, el atraso debe basarse en una regla general o reglas por tipo de permiso. Si RRHH no define limite, este proceso puede limitarse a alertar salidas abiertas por demasiado tiempo.

## 6. Estructura de carpetas recomendada

Manteniendo Laravel clasico y el patron actual:

```text
app/
  Models/
    User.php
    Role.php
    Office.php
    OfficeAssignment.php
    TemporaryOfficeAssignment.php
    PermissionType.php
    PermissionRequest.php
    PermissionDocument.php
    Approval.php
    ApprovalDecision.php
    DigitalSignature.php
    PermissionTracking.php
    Agent.php
    AgentToken.php
    AccessEvent.php
    Notification.php
    AuditEvent.php

  Services/
    Permissions/
      PermissionWorkflow.php
      PermissionRulesService.php
      PermissionNumberGenerator.php
    Approvals/
      ApprovalResolver.php
      ApprovalWorkflow.php
    Signatures/
      SignatureWorkflow.php
      FirmaPeruClient.php
    Tracking/
      TrackingWorkflow.php
      AccessEventProcessor.php
    Notifications/
      NotificationService.php
    Audit/
      AuditService.php
    Reports/
      ReportQueryService.php

  Http/
    Controllers/
      PermissionRequestController.php
      PermissionSubmissionController.php
      ApprovalController.php
      TrackingController.php
      TrackingApiController.php
      SignatureController.php
      Admin/
        UserController.php
        OfficeController.php
        TemporaryOfficeAssignmentController.php
        PermissionTypeController.php
        AgentController.php
      Api/
        AgentController.php
        FirmaPeruController.php
        NotificationController.php
    Requests/
    Middleware/
    Policies/
```

## 7. Rutas recomendadas

### Web autenticado

```text
/dashboard
/permissions
/permissions/create
/permissions/{permission}
/permissions/{permission}/edit
/permissions/{permission}/submit
/permissions/{permission}/cancel
/permissions/{permission}/documents

/approvals
/approvals/{permission}
/approvals/{permission}/approve
/approvals/{permission}/reject

/tracking
/tracking/dashboard
/tracking/{tracking}

/hr/reports
/hr/permissions/create

/admin/users
/admin/offices
/admin/temporary-assignments
/admin/permission-types
/admin/agents
/admin/audit
```

### API interna AJAX

```text
/internal/notifications/check
/internal/notifications/read
/internal/tracking/scan-dni
/internal/tracking/register-departure
/internal/tracking/register-return
/internal/approvals/stats
```

### API externa

```text
/api/firma-peru/param
/api/firma-peru/document/{permission}
/api/firma-peru/upload/{permission}

/api/agent/ping
/api/agent/register
/api/agent/heartbeat
/api/agent/employees
/api/agent/access-events
/api/agent/permission-trackings/{dni}
```

## 8. Pantallas V2

### 8.1 Empleado

Pantallas:

- Dashboard.
- Mis papeletas.
- Nueva papeleta.
- Detalle de papeleta.
- Adjuntos.
- PDF.
- Seguimiento.

Detalle de papeleta debe mostrar:

- Estado principal.
- Tipo.
- Motivo.
- Documentos.
- Timeline:
  - creada
  - enviada
  - aprobada/rechazada por oficina
  - aprobada/rechazada por RRHH
  - salida
  - retorno

### 8.2 Encargado de oficina

Pantallas:

- Bandeja de aprobacion.
- Historial.
- Filtros por oficina.
- Detalle de solicitud.

La bandeja debe mostrar solicitudes de:

- Oficinas oficiales.
- Oficinas temporales activas.

### 8.3 RRHH

Pantallas:

- Bandeja RRHH.
- Seguimiento de salidas.
- Registro manual por DNI.
- Reportes.
- Crear permiso directo.

### 8.4 Admin

Pantallas:

- Usuarios.
- Oficinas.
- Encargados oficiales.
- Encargaturas temporales.
- Tipos de permiso.
- Agentes ZKTeco.
- Auditoria.

## 9. Politicas de autorizacion

Crear Policies:

- `PermissionRequestPolicy`
- `ApprovalPolicy`
- `PermissionDocumentPolicy`
- `PermissionTrackingPolicy`
- `OfficePolicy`
- `TemporaryOfficeAssignmentPolicy`
- `AgentPolicy`
- `ReportPolicy`

Reglas clave:

- Empleado solo ve sus solicitudes.
- Encargado ve solicitudes de oficinas asignadas oficialmente o temporalmente.
- RRHH ve todas las solicitudes en etapa RRHH y todo seguimiento.
- Admin ve todo.
- Nadie aprueba fuera de su ambito.
- Encargatura vencida no autoriza decisiones nuevas.

## 10. Plan de implementacion

## Fase 1: base del dominio

Objetivo:

Crear la estructura central del nuevo sistema.

Tareas:

- Crear migraciones nuevas.
- Crear modelos:
  - `Office`
  - `OfficeAssignment`
  - `TemporaryOfficeAssignment`
  - `PermissionRequest`
  - `PermissionDocument`
  - `Approval`
  - `ApprovalDecision`
  - `PermissionTracking`
  - `AccessEvent`
  - `Agent`
  - `AuditEvent`
- Crear enums o constantes de estados.
- Crear seeders base de roles y tipos de permiso.
- Crear factories para pruebas.

Entregable:

- Base de datos V2 consistente.
- Modelos con relaciones.

## Fase 2: solicitudes

Objetivo:

Implementar creacion, edicion y envio.

Tareas:

- Crear `PermissionWorkflow`.
- Crear `PermissionRulesService`.
- Crear controlador de solicitudes.
- Crear requests de validacion.
- Crear vistas Blade siguiendo patron actual.
- Implementar documentos adjuntos.
- Crear auditoria de creacion/envio.

Entregable:

- Empleado puede crear, editar, adjuntar y enviar solicitud.

## Fase 3: aprobaciones con encargaturas

Objetivo:

Soportar aprobaciones por oficina, encargaturas temporales y multiples RRHH.

Tareas:

- Crear `ApprovalResolver`.
- Crear `ApprovalWorkflow`.
- Crear bandeja de encargado.
- Crear bandeja RRHH.
- Implementar aprobacion/rechazo.
- Crear modulo admin de encargaturas temporales.
- Crear policies.
- Crear notificaciones.

Entregable:

- Encargado oficial aprueba su oficina.
- Encargado temporal aprueba oficina asignada temporalmente.
- Cualquier RRHH activo aprueba etapa RRHH.

## Fase 4: seguimiento fisico

Objetivo:

Controlar salida y retorno sin horas declaradas en solicitud.

Tareas:

- Crear `TrackingWorkflow`.
- Crear seguimiento al aprobar RRHH.
- Crear pantalla RRHH de seguimiento.
- Crear registro manual por DNI.
- Calcular minutos reales.
- Implementar atrasos por regla general.
- Registrar auditoria.

Entregable:

- Solicitud aprobada genera seguimiento.
- RRHH registra salida y retorno.
- Reporte basico de tiempo real usado.

## Fase 5: agentes ZKTeco

Objetivo:

Integrar eventos biometricos de forma auditable.

Tareas:

- Crear `Agent`.
- Cambiar tokens a hash.
- Crear `AccessEvent`.
- Crear `AccessEventProcessor`.
- Refactor de API de agentes.
- Panel admin de agentes.
- Heartbeat y estado online/offline.

Entregable:

- Eventos ZKTeco quedan registrados.
- Eventos procesan salida/retorno si hay tracking activo.
- Eventos sin tracking quedan auditados.

## Fase 6: firma digital

Objetivo:

Ordenar FIRMA PERU sin mezclarla con aprobaciones.

Tareas:

- Crear `FirmaPeruClient`.
- Crear `SignatureWorkflow`.
- Mover credenciales a `.env`.
- Asociar firma con `approval_id`.
- Sanitizar logs.
- Crear pruebas de callback.

Entregable:

- Firma digital funciona para encargado y RRHH.
- Multiples RRHH pueden firmar segun quien decida.

## Fase 7: reportes

Objetivo:

Reportes confiables con datos reales.

Tareas:

- Crear `ReportQueryService`.
- Reportes por estado.
- Reportes por oficina.
- Reportes por tipo.
- Reporte de salidas/retornos.
- Reporte de encargados.
- Reporte de RRHH aprobador.
- Exportacion Excel.

Entregable:

- RRHH ve indicadores reales.
- Los reportes no dependen de campos inexistentes ni de horas declaradas por usuario.

## Fase 8: limpieza y estabilizacion

Objetivo:

Preparar produccion.

Tareas:

- Eliminar codigo duplicado.
- Actualizar README.
- Crear documentacion tecnica final.
- Revisar logs.
- Revisar permisos de archivos.
- Ejecutar pruebas.
- Prueba piloto con usuarios.

Entregable:

- V2 lista para despliegue.

## 11. Pruebas necesarias

### Usuarios y oficinas

- Crear oficina.
- Asignar encargado oficial.
- Crear encargatura temporal.
- Encargatura vencida no autoriza.
- Encargatura activa autoriza.

### Solicitudes

- Empleado crea borrador.
- Empleado edita borrador.
- Empleado envia solicitud.
- No puede editar solicitud enviada.
- Puede cancelar segun estado.

### Aprobaciones

- Encargado oficial aprueba.
- Encargado temporal aprueba.
- Usuario sin encargatura no aprueba.
- RRHH aprueba etapa RRHH.
- Varios RRHH pueden ver la misma bandeja.
- La decision registra quien aprobo realmente.

### Seguimiento

- Solicitud aprobada crea tracking.
- Salida manual cambia a `out`.
- Retorno manual cambia a `returned`.
- Calcula minutos reales.
- Evento ZKTeco registra salida.
- Evento ZKTeco registra retorno.
- Evento sin tracking queda como ignorado o error controlado.

### Firma digital

- Inicia firma para encargado.
- Inicia firma para RRHH.
- Callback valido crea firma.
- Callback invalido no cambia estado.

### Seguridad

- Empleado no ve solicitudes ajenas.
- Encargado no ve oficinas no asignadas.
- RRHH ve etapa RRHH.
- Admin gestiona todo.
- Token ZKTeco revocado no accede.

## 12. Prioridad de desarrollo

Orden recomendado:

1. Modelos y migraciones.
2. Usuarios, oficinas y encargaturas.
3. Solicitudes.
4. Aprobacion por oficina.
5. Aprobacion RRHH multiple.
6. Seguimiento manual.
7. ZKTeco.
8. Firma digital.
9. Reportes.
10. Auditoria completa y limpieza.

## 13. Criterios de exito

La V2 estara bien disenada si cumple:

- Un encargado puede aprobar varias oficinas por asignacion oficial o temporal.
- Varios RRHH pueden aprobar sin depender de un usuario unico.
- El empleado no necesita declarar hora de salida ni retorno.
- El seguimiento fisico controla tiempo real usado.
- Los estados de solicitud y seguimiento no se mezclan.
- La interfaz se siente familiar respecto al sistema original.
- Los controladores son delgados.
- Las reglas de aprobacion estan centralizadas.
- Las acciones criticas quedan auditadas.
- Los reportes salen de datos reales y consistentes.

