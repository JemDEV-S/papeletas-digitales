# Arquitectura del nuevo Sistema de Papeletas Digitales V2

Fecha: 2026-05-08  
Rol del documento: guia tecnica y funcional para desarrollar un nuevo sistema desde una base ordenada.  
Stack recomendado: Laravel 12, PHP 8.2+, Blade, Tailwind CSS, Alpine.js, MySQL/PostgreSQL, Redis opcional, colas, scheduler, FIRMA PERU, integracion ZKTeco.

## 1. Vision del sistema

El nuevo Sistema de Papeletas Digitales V2 gestionara solicitudes de permisos laborales, aprobaciones, firmas digitales, control fisico de salida/retorno, reportes y auditoria.

La V2 debe mantener una experiencia familiar respecto al sistema original, pero con una arquitectura mas limpia:

- Modulos separados.
- Reglas de negocio centralizadas.
- Controladores delgados.
- Base de datos coherente.
- Autorizacion clara.
- Auditoria completa.
- Reportes basados en datos reales.
- Integraciones externas aisladas.

La premisa funcional mas importante es esta: el empleado no declara hora de salida ni hora de retorno al crear la papeleta. La solicitud aprueba el permiso; el tiempo real se controla despues en seguimiento.

## 2. Principios de arquitectura

1. Separar solicitud y seguimiento.
   La solicitud representa aprobacion administrativa. El seguimiento representa salida y retorno reales.

2. Una persona puede tener varios departamentos bajo su cargo.
   No se usara una tabla de encargatura temporal separada. Todo se modelara con asignaciones entre usuarios y departamentos.

3. RRHH puede tener varios aprobadores.
   La aprobacion RRHH no pertenece a un unico usuario fijo. Cualquier usuario activo con rol RRHH puede tomar y resolver la solicitud.

4. Los estados deben ser explicitos.
   Evitar que `approved` signifique varias cosas. Una solicitud aprobada no debe cambiar a "en progreso" por salida fisica; eso corresponde al tracking.

5. La autorizacion debe estar centralizada.
   Usar Policies y servicios de resolucion, no checks dispersos en vistas/controladores.

6. Las integraciones no gobiernan el dominio.
   FIRMA PERU y ZKTeco son adaptadores externos. No deben decidir reglas de aprobacion ni estados principales.

7. Todo evento critico debe auditarse.
   Aprobaciones, rechazos, documentos, firmas, salidas, retornos, cambios administrativos y eventos ZKTeco deben quedar registrados.

8. Optimizar para operacion municipal real.
   Debe ser facil administrar departamentos, encargados, usuarios RRHH, reportes, pendientes y salidas abiertas.

## 3. Roles del sistema

## 3.1 Empleado

Puede:

- Crear papeletas.
- Guardar borradores.
- Adjuntar documentos.
- Enviar solicitudes.
- Ver su historial.
- Descargar PDF.
- Ver estado de aprobacion.
- Ver seguimiento de salida/retorno.

No puede:

- Aprobar solicitudes.
- Ver papeletas ajenas.
- Registrar salida/retorno de otros.

## 3.2 Encargado de departamento

Puede:

- Ver solicitudes pendientes de los departamentos bajo su cargo.
- Aprobar o rechazar en primera instancia.
- Ver historial de solicitudes de esos departamentos.

Regla clave:

- Un usuario puede estar a cargo de uno o varios departamentos.
- Un departamento puede tener uno o varios encargados, si se desea permitir suplencia.

## 3.3 RRHH

Puede:

- Ver solicitudes pendientes de RRHH.
- Aprobar o rechazar como segunda instancia.
- Registrar salidas y retornos.
- Ver reportes.
- Crear papeletas directas autorizadas, si el negocio lo mantiene.
- Administrar seguimiento diario.

Regla clave:

- Puede haber varios usuarios RRHH.
- La solicitud pendiente de RRHH puede ser tomada por cualquiera de ellos.
- La decision debe registrar quien aprobo realmente.

## 3.4 Administrador

Puede:

- Gestionar usuarios.
- Gestionar departamentos.
- Asignar encargados a departamentos.
- Gestionar tipos de permiso.
- Gestionar agentes ZKTeco.
- Ver auditoria.
- Configurar reglas generales.

## 4. Modulos del sistema

1. Autenticacion y usuarios.
2. Departamentos y encargados.
3. Tipos de permiso.
4. Solicitudes de papeletas.
5. Documentos adjuntos.
6. Aprobaciones.
7. Firma digital.
8. Seguimiento de salida/retorno.
9. Agentes ZKTeco.
10. Notificaciones.
11. Reportes.
12. Auditoria.
13. Configuracion del sistema.

## 5. Modelo de datos propuesto

## 5.1 users

Representa a cada usuario del sistema.

Campos:

- `id`
- `dni`
- `first_name`
- `last_name`
- `name`
- `email`
- `password`
- `department_id`
- `role_id`
- `is_active`
- `last_login_at`
- `email_verified_at`
- `remember_token`
- `created_at`
- `updated_at`

Indices:

- Unico `dni`.
- Unico opcional `email`.
- Indice `department_id`.
- Indice `role_id`.
- Indice `is_active`.

Relaciones:

- Pertenece a `Department`.
- Pertenece a `Role`.
- Tiene muchas `PermissionRequest`.
- Tiene muchas decisiones de aprobacion.
- Pertenece a muchos departamentos como encargado mediante `department_managers`.

## 5.2 roles

Campos:

- `id`
- `name`
- `display_name`
- `description`
- `is_active`
- `created_at`
- `updated_at`

Roles iniciales:

- `empleado`
- `encargado`
- `rrhh`
- `admin`

Recomendacion:

- Si no se usara Spatie Permission, eliminar la dependencia.
- Si se usara Spatie, migrar completamente roles/permisos a Spatie y no mantener sistema duplicado.

Para este sistema, por simplicidad y control, se recomienda mantener roles propios si los permisos son pocos y estables.

## 5.3 departments

Representa la estructura organizacional.

Campos:

- `id`
- `code`
- `name`
- `parent_id`
- `is_active`
- `created_at`
- `updated_at`

Indices:

- Unico `code`.
- Indice `parent_id`.
- Indice `is_active`.

Relaciones:

- Tiene muchos usuarios.
- Puede tener departamento padre.
- Puede tener subdepartamentos.
- Tiene muchos encargados mediante `department_managers`.

Notas:

- `Department` reemplaza a `Office`.
- La jerarquia es opcional, pero recomendable para reportes.

## 5.4 department_managers

Tabla pivote que define que departamentos tiene a cargo cada usuario.

Esta tabla reemplaza cualquier `TemporaryOfficeAssignment`.

Campos:

- `id`
- `department_id`
- `user_id`
- `manager_type`
- `starts_at`
- `ends_at`
- `is_active`
- `created_by`
- `created_at`
- `updated_at`

Valores de `manager_type`:

- `principal`
- `suplente`
- `encargado`

Reglas:

- Si `starts_at` y `ends_at` son nulos, la asignacion es indefinida.
- Si tienen fechas, la asignacion solo aplica dentro del rango.
- Un usuario puede tener varios registros activos en distintos departamentos.
- Un departamento puede tener varios encargados activos.
- Para aprobar, la asignacion debe estar activa y vigente.

Indices:

- `department_id`
- `user_id`
- `is_active`
- compuesto `department_id`, `user_id`, `is_active`

Ventaja:

- Maneja encargaturas temporales y permanentes con la misma estructura.
- Evita una tabla adicional.
- Hace mas simple la bandeja del encargado.

## 5.5 permission_types

Catalogo de tipos de permiso.

Campos:

- `id`
- `code`
- `name`
- `description`
- `with_pay`
- `document_policy`
- `approval_flow`
- `rules`
- `is_active`
- `created_at`
- `updated_at`

Valores de `document_policy`:

- `none`
- `recommended`
- `required_before_submit`
- `required_before_hr_approval`

Valores de `approval_flow`:

- `department_then_hr`
- `hr_only`
- `department_only`
- `direct_hr_approved`

Ejemplo de `rules`:

```json
{
  "required_documents": ["certificado_medico"],
  "recommended_documents": [],
  "requires_employee_signature": false,
  "requires_department_manager_signature": true,
  "requires_hr_signature": true,
  "max_open_tracking_hours": 8,
  "grace_minutes": 60
}
```

Recomendacion:

- Las reglas que dependen de horas reales deben evaluarse en seguimiento, no en solicitud.

## 5.6 permission_requests

Solicitud administrativa de papeleta.

Campos:

- `id`
- `request_number`
- `user_id`
- `department_id`
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

No incluir:

- `start_datetime`
- `end_datetime`
- `requested_hours`

Motivo:

- El empleado no indica hora de salida ni retorno.
- El tiempo real se registra en `permission_trackings`.

Estados:

- `draft`
- `pending_department`
- `pending_hr`
- `approved`
- `rejected`
- `cancelled`
- `expired`

Reglas:

- `department_id` debe copiar el departamento del empleado al momento de crear la solicitud.
- El historial no debe cambiar si luego el empleado cambia de departamento.
- `request_number` debe ser unico, por ejemplo `PAP-2026-000001`.

## 5.7 permission_documents

Documentos adjuntos.

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

Reglas:

- Validar MIME y extension.
- Calcular hash SHA256.
- Guardar fuera de `public` si el documento es sensible.
- Acceder siempre mediante controlador autorizado.
- Usar soft delete si se requiere auditoria.

## 5.8 approvals

Paso de aprobacion.

Campos:

- `id`
- `permission_request_id`
- `step`
- `approval_type`
- `target_role`
- `target_department_id`
- `status`
- `decided_by`
- `comments`
- `decided_at`
- `decision_method`
- `metadata`
- `created_at`
- `updated_at`

Valores de `approval_type`:

- `department_manager`
- `hr`
- `admin_override`

Valores de `target_role`:

- `encargado`
- `rrhh`
- `admin`

Estados:

- `pending`
- `approved`
- `rejected`
- `cancelled`
- `skipped`

Decision method:

- `manual`
- `firma_peru`
- `automatic`
- `override`

Regla para departamentos:

- Para `department_manager`, `target_department_id` define el departamento que debe aprobar.
- Cualquier usuario con asignacion activa en `department_managers` para ese departamento puede decidir.

Regla para RRHH:

- Para `hr`, cualquier usuario activo con rol `rrhh` puede decidir.

## 5.9 approval_decisions

Historial inmutable de acciones sobre aprobaciones.

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

Uso:

- Auditoria especifica de aprobaciones.
- Reportes de desempeno.
- Trazabilidad de quien aprobo realmente.

## 5.10 digital_signatures

Firmas digitales asociadas a solicitud y aprobacion.

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

Provider:

- `firma_peru`

Stages:

- `employee`
- `department_manager`
- `hr`

Regla:

- Si la firma pertenece a una aprobacion, debe tener `approval_id`.
- El usuario firmante debe coincidir con quien decide la aprobacion.

## 5.11 permission_trackings

Control de salida y retorno real.

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

- `pending_departure`
- `out`
- `returned`
- `overdue`
- `auto_closed`
- `cancelled`

Sources:

- `manual`
- `zkteco`
- `system`

Reglas:

- Se crea cuando la solicitud pasa a `approved`.
- No modifica el estado administrativo de la solicitud.
- Calcula `actual_minutes_used` cuando existe salida y retorno.
- El atraso se calcula con reglas del tipo de permiso o una regla general.

## 5.12 agents

Representa agente o punto ZKTeco.

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

## 5.13 agent_tokens

Tokens de autenticacion de agentes.

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

Mejor practica:

- Nunca guardar token plano.
- Mostrar token completo solo al crearlo.
- Hashear con SHA256 o HMAC.
- Permitir revocacion.

## 5.14 access_events

Eventos recibidos desde ZKTeco.

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

Eventos:

- `entry`
- `exit`

Processing status:

- `pending`
- `processed`
- `ignored`
- `error`

Regla:

- Todo evento se guarda, incluso si no hay permiso activo.

## 5.15 notifications

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

## 5.16 audit_events

Auditoria general.

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

Eventos minimos:

- `permission.created`
- `permission.submitted`
- `permission.approved`
- `permission.rejected`
- `permission.cancelled`
- `document.uploaded`
- `document.deleted`
- `approval.approved`
- `approval.rejected`
- `signature.completed`
- `tracking.departure_registered`
- `tracking.return_registered`
- `tracking.overdue`
- `department.manager_assigned`
- `department.manager_removed`
- `agent.event_received`

## 6. Servicios de dominio

## 6.1 PermissionWorkflow

Responsable de:

- Crear solicitud.
- Actualizar borrador.
- Enviar solicitud.
- Cancelar solicitud.
- Cambiar estado administrativo.
- Crear primera aprobacion.

No debe:

- Procesar firma digital directamente.
- Registrar salidas/retornos.
- Hacer consultas grandes de reportes.

## 6.2 PermissionNumberGenerator

Responsable de generar numeros correlativos.

Formato recomendado:

```text
PAP-YYYY-000001
```

Debe evitar colisiones con transacciones o bloqueo.

## 6.3 ApprovalResolver

Responsable de determinar quien puede aprobar.

Funciones:

- `getManagedDepartmentIds(User $user)`
- `canApproveDepartment(User $user, Department $department)`
- `canApprovePermission(User $user, PermissionRequest $request)`
- `getEligibleDepartmentManagers(PermissionRequest $request)`
- `getEligibleHrApprovers()`

Logica principal:

- Si aprobacion es de departamento, verificar `department_managers`.
- Si aprobacion es RRHH, verificar rol `rrhh`.
- Si usuario es admin, puede tener override segun politica.

## 6.4 ApprovalWorkflow

Responsable de:

- Crear pasos de aprobacion.
- Aprobar.
- Rechazar.
- Crear siguiente paso.
- Finalizar solicitud.
- Crear tracking tras aprobacion final.

## 6.5 SignatureWorkflow

Responsable de:

- Validar si una accion requiere firma.
- Iniciar firma.
- Procesar callback.
- Crear `DigitalSignature`.
- Avisar a `ApprovalWorkflow`.

## 6.6 FirmaPeruClient

Adaptador externo.

Responsable de:

- Generar token.
- Preparar parametros.
- Validar callbacks.
- Descargar documento a firmar.
- Recibir documento firmado.

Buenas practicas:

- Credenciales desde `.env`.
- Logs sin tokens ni secretos.
- Timeouts definidos.
- Manejo claro de errores.

## 6.7 TrackingWorkflow

Responsable de:

- Registrar salida.
- Registrar retorno.
- Calcular minutos reales.
- Marcar atraso.
- Cerrar automaticamente.
- Actualizar tracking manualmente.

## 6.8 AccessEventProcessor

Responsable de:

- Procesar eventos ZKTeco.
- Guardar evento crudo.
- Buscar usuario por DNI.
- Buscar tracking activo.
- Registrar salida o retorno.
- Marcar evento como procesado, ignorado o error.

## 6.9 NotificationService

Responsable de:

- Crear notificaciones internas.
- Enviar correos si aplica.
- Marcar leidas.
- Limpiar expiradas.

## 6.10 AuditService

Responsable de:

- Registrar acciones criticas.
- Normalizar actor, IP, user agent y entidad.
- Evitar duplicacion de auditoria en controladores.

## 6.11 ReportQueryService

Responsable de consultas para reportes.

Debe evitar:

- Consultas complejas en controladores.
- Duplicacion de filtros.
- Depender de campos no existentes.

## 7. Procesos funcionales

## 7.1 Crear solicitud

1. Empleado abre formulario.
2. Selecciona tipo de permiso.
3. Ingresa motivo.
4. Adjunta documentos, si desea o si el tipo lo exige.
5. Guarda.

Resultado:

- Solicitud en `draft`.
- Se fija `department_id` del empleado.
- Se registra auditoria.

## 7.2 Enviar solicitud

1. Empleado presiona enviar.
2. Sistema valida documentos segun `document_policy`.
3. Sistema determina flujo segun `approval_flow`.
4. Si requiere departamento, crea aprobacion `department_manager`.
5. Solicitud pasa a `pending_department`.
6. Si no requiere departamento, crea aprobacion RRHH.
7. Notifica a aprobadores elegibles.

## 7.3 Aprobar por encargado

1. Encargado entra a su bandeja.
2. Sistema obtiene departamentos a cargo desde `department_managers`.
3. Muestra solicitudes pendientes de esos departamentos.
4. Encargado aprueba o rechaza.
5. Si aprueba, la solicitud pasa a `pending_hr`.
6. Se crea aprobacion RRHH.

## 7.4 Aprobar por RRHH

1. RRHH entra a bandeja.
2. Ve solicitudes `pending_hr`.
3. Aprueba o rechaza.
4. Si aprueba, solicitud pasa a `approved`.
5. Se crea `permission_tracking` en `pending_departure`.
6. Se notifica al empleado.

## 7.5 Registrar salida

1. RRHH o ZKTeco envia DNI.
2. Sistema busca tracking aprobado en `pending_departure`.
3. Registra `departure_datetime`.
4. Cambia tracking a `out`.

## 7.6 Registrar retorno

1. RRHH o ZKTeco envia DNI.
2. Sistema busca tracking en `out` u `overdue`.
3. Registra `return_datetime`.
4. Calcula minutos reales.
5. Cambia tracking a `returned`.

## 7.7 Procesar eventos ZKTeco

1. Agente envia evento.
2. Se valida token.
3. Se guarda `access_event`.
4. Se procesa contra tracking activo.
5. Se responde JSON con resultado.

## 8. Autorizacion

Crear Policies:

- `PermissionRequestPolicy`
- `ApprovalPolicy`
- `PermissionDocumentPolicy`
- `PermissionTrackingPolicy`
- `DepartmentPolicy`
- `DepartmentManagerPolicy`
- `AgentPolicy`
- `ReportPolicy`

Reglas principales:

- Empleado ve solo lo suyo.
- Encargado ve y aprueba solicitudes de departamentos bajo su cargo.
- RRHH ve solicitudes en etapa RRHH y seguimiento.
- Admin administra todo.
- Un usuario sin asignacion activa en `department_managers` no aprueba por departamento.

## 9. Rutas recomendadas

## 9.1 Web

```text
GET    /dashboard

GET    /permissions
GET    /permissions/create
POST   /permissions
GET    /permissions/{permission}
GET    /permissions/{permission}/edit
PUT    /permissions/{permission}
POST   /permissions/{permission}/submit
POST   /permissions/{permission}/cancel
POST   /permissions/{permission}/documents
DELETE /permissions/{permission}/documents/{document}

GET    /approvals
GET    /approvals/{permission}
POST   /approvals/{approval}/approve
POST   /approvals/{approval}/reject

GET    /tracking
GET    /tracking/dashboard
POST   /tracking/scan-dni
POST   /tracking/register-departure
POST   /tracking/register-return

GET    /hr/reports
GET    /hr/reports/export

GET    /admin/users
GET    /admin/departments
GET    /admin/department-managers
GET    /admin/permission-types
GET    /admin/agents
GET    /admin/audit
```

## 9.2 API externa

```text
POST /api/firma-peru/param
GET  /api/firma-peru/document/{permission}
POST /api/firma-peru/upload/{permission}

GET  /api/agent/ping
POST /api/agent/register
POST /api/agent/heartbeat
GET  /api/agent/employees
POST /api/agent/access-events
GET  /api/agent/permission-trackings/{dni}
```

## 10. Interfaz de usuario

Se mantiene el patron visual del sistema original:

- Layout administrativo.
- Navegacion lateral o superior.
- Tablas con filtros.
- Badges de estado.
- Formularios simples.
- Modales para confirmacion.
- Dashboards por rol.

Mejoras recomendadas:

- Componentes Blade reutilizables:
  - `status-badge`
  - `data-table`
  - `filter-bar`
  - `empty-state`
  - `timeline`
  - `document-list`
  - `approval-actions`
- Timeline unico en detalle de solicitud.
- Acciones visibles solo si la policy las permite.
- Bandeja de encargados filtrable por departamento.
- Bandeja RRHH con filtros por estado, departamento, tipo y fecha.

## 11. Reportes

Reportes minimos:

- Solicitudes por estado.
- Solicitudes por departamento.
- Solicitudes por tipo de permiso.
- Aprobaciones por encargado.
- Aprobaciones por RRHH.
- Solicitudes rechazadas.
- Empleados actualmente fuera.
- Salidas abiertas.
- Retornos tardios.
- Tiempo real usado por empleado.
- Tiempo real usado por departamento.
- Eventos ZKTeco no procesados.

Fuente de datos:

- Solicitudes administrativas: `permission_requests`.
- Decisiones: `approvals` y `approval_decisions`.
- Tiempo real: `permission_trackings`.
- Eventos biometricos: `access_events`.

## 12. Seguridad

Buenas practicas obligatorias:

- Hash de passwords con algoritmo Laravel por defecto.
- Hash de tokens de agentes.
- CSRF en rutas web.
- Rate limiting en API externa.
- Validacion estricta de archivos.
- Documentos servidos por controlador autorizado.
- Logs sin secretos.
- Credenciales en `.env`.
- Backups de base de datos y storage.
- Auditoria de acciones criticas.

Variables `.env` recomendadas:

```env
FIRMA_PERU_CLIENT_ID=
FIRMA_PERU_CLIENT_SECRET=
FIRMA_PERU_TOKEN_URL=
FIRMA_PERU_JS_URL=https://apps.firmaperu.gob.pe/web/clienteweb/firmaperu.min.js
FIRMA_PERU_VERIFY_SSL=true

ZKTECO_HEARTBEAT_TIMEOUT_SECONDS=90

PERMISSION_DEFAULT_GRACE_MINUTES=60
PERMISSION_REQUIRE_EMPLOYEE_SIGNATURE=false
```

## 13. Plan de implementacion

## Fase 1: fundacion del proyecto

Objetivo:

Crear base limpia del sistema.

Tareas:

- Crear proyecto Laravel.
- Configurar Breeze o autenticacion propia.
- Configurar Tailwind.
- Configurar base de datos.
- Crear layout base.
- Crear roles iniciales.
- Crear usuario admin inicial.

Entregable:

- Login funcional.
- Dashboard base.
- Estructura inicial lista.

## Fase 2: departamentos y encargados

Objetivo:

Modelar la organizacion real.

Tareas:

- Crear migraciones:
  - `departments`
  - `department_managers`
- Crear modelos y relaciones.
- Crear CRUD de departamentos.
- Crear pantalla para asignar encargados.
- Permitir que un usuario tenga varios departamentos bajo su cargo.
- Crear policies.

Entregable:

- Admin gestiona departamentos y encargados.
- Encargado puede consultar sus departamentos.

## Fase 3: tipos de permiso

Objetivo:

Configurar reglas de negocio.

Tareas:

- Crear `permission_types`.
- Seeder con tipos iniciales.
- CRUD admin.
- Reglas JSON.
- Politicas de documentos.
- Flujo de aprobacion configurable.

Entregable:

- Tipos de permiso activos y configurables.

## Fase 4: solicitudes

Objetivo:

Empleado crea y envia papeletas.

Tareas:

- Crear `permission_requests`.
- Crear `permission_documents`.
- Crear `PermissionWorkflow`.
- Crear generador de numero.
- Crear vistas de listar, crear, editar y detalle.
- Crear subida de documentos.
- Crear envio a aprobacion.
- Crear notificaciones iniciales.

Entregable:

- Empleado puede crear, guardar, editar y enviar papeleta.

## Fase 5: aprobaciones

Objetivo:

Implementar flujo departamento -> RRHH.

Tareas:

- Crear `approvals`.
- Crear `approval_decisions`.
- Crear `ApprovalResolver`.
- Crear `ApprovalWorkflow`.
- Crear bandeja de encargado por departamentos asignados.
- Crear bandeja RRHH para multiples aprobadores.
- Implementar aprobar/rechazar.
- Crear auditoria.

Entregable:

- Encargado aprueba por departamento.
- RRHH multiple aprueba etapa final.
- Se registra quien decidio.

## Fase 6: seguimiento

Objetivo:

Controlar salida y retorno reales.

Tareas:

- Crear `permission_trackings`.
- Crear tracking al aprobar RRHH.
- Crear dashboard de seguimiento.
- Crear escaneo/ingreso de DNI.
- Registrar salida.
- Registrar retorno.
- Calcular minutos reales.
- Marcar salidas abiertas.

Entregable:

- RRHH controla salida y retorno sin horas declaradas por empleado.

## Fase 7: ZKTeco

Objetivo:

Integrar eventos biometricos de forma robusta.

Tareas:

- Crear `agents`.
- Crear `agent_tokens`.
- Crear `access_events`.
- Crear API de agente.
- Crear `AccessEventProcessor`.
- Panel de agentes.
- Heartbeat.
- Sincronizacion de empleados.

Entregable:

- Agente envia eventos.
- Sistema registra y procesa eventos.
- Eventos no procesados quedan auditados.

## Fase 8: firma digital

Objetivo:

Integrar FIRMA PERU con bajo acoplamiento.

Tareas:

- Crear `digital_signatures`.
- Crear `FirmaPeruClient`.
- Crear `SignatureWorkflow`.
- Crear endpoints FIRMA PERU.
- Asociar firmas a aprobaciones.
- Generar PDF firmable.
- Procesar PDF firmado.

Entregable:

- Aprobaciones pueden cerrarse con firma digital segun reglas.

## Fase 9: reportes y exportaciones

Objetivo:

Dar valor operativo a RRHH.

Tareas:

- Crear `ReportQueryService`.
- Crear dashboard RRHH.
- Crear reportes base.
- Exportacion Excel.
- Filtros por fecha, departamento, tipo y estado.

Entregable:

- RRHH puede tomar decisiones con datos reales.

## Fase 10: auditoria, hardening y despliegue

Objetivo:

Preparar produccion.

Tareas:

- Completar `audit_events`.
- Revisar logs.
- Revisar permisos.
- Crear pruebas feature.
- Crear README real.
- Crear guia de despliegue.
- Configurar scheduler.
- Configurar backups.

Entregable:

- Sistema listo para piloto y produccion.

## 14. Pruebas minimas obligatorias

Autenticacion:

- Login con DNI.
- Usuario inactivo no ingresa.

Departamentos:

- Crear departamento.
- Asignar encargado.
- Usuario con varios departamentos ve ambos.
- Usuario sin departamento asignado no aprueba.

Solicitudes:

- Crear borrador.
- Editar borrador.
- Enviar solicitud.
- No editar enviada.
- Cancelar si corresponde.

Aprobaciones:

- Encargado aprueba solicitud de departamento asignado.
- Encargado no aprueba departamento no asignado.
- RRHH aprueba solicitud pendiente de RRHH.
- Otro RRHH tambien puede verla antes de decision.
- Se registra `decided_by`.

Seguimiento:

- Aprobacion final crea tracking.
- Registrar salida manual.
- Registrar retorno manual.
- Calcular minutos reales.
- Marcar overdue.

ZKTeco:

- Token valido accede.
- Token revocado no accede.
- Evento crea access_event.
- Evento procesa salida/retorno.
- Evento sin tracking queda ignored.

Firma:

- Iniciar firma.
- Procesar callback valido.
- Rechazar callback invalido.
- Asociar firma a aprobacion.

Seguridad:

- Empleado no ve solicitud ajena.
- Encargado no ve departamento ajeno.
- Documentos no se descargan sin autorizacion.

## 15. Criterios de exito

El sistema V2 sera exitoso si:

- El empleado crea papeletas sin indicar horas.
- El seguimiento registra tiempo real de salida y retorno.
- Una persona puede encargarse de varios departamentos.
- El encargado ve y aprueba todas las solicitudes de sus departamentos asignados.
- Hay varios aprobadores RRHH.
- La solicitud y el tracking tienen estados separados.
- Las aprobaciones registran quien decidio.
- FIRMA PERU y ZKTeco estan desacoplados del dominio.
- Los reportes salen de datos reales.
- Las acciones criticas quedan auditadas.
- El codigo queda organizado para crecer sin parches.

