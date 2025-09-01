# Manual de Entrenamiento: Seguimiento Físico de Permisos
## Para Personal de Recursos Humanos

---

### **Tabla de Contenidos**
1. [Introducción al Sistema](#introducción-al-sistema)
2. [Acceso al Dashboard de RRHH](#acceso-al-dashboard-de-rrhh)
3. [Operación del Escáner de DNI](#operación-del-escáner-de-dni)
4. [Registro de Salidas y Entradas](#registro-de-salidas-y-entradas)
5. [Monitoreo en Tiempo Real](#monitoreo-en-tiempo-real)
6. [Gestión de Retrasos](#gestión-de-retrasos)
7. [Resolución de Problemas](#resolución-de-problemas)
8. [Procedimientos de Emergencia](#procedimientos-de-emergencia)

---

## **Introducción al Sistema**

### **¿Qué es el Sistema de Seguimiento Físico?**
El Sistema de Seguimiento Físico es una nueva funcionalidad que permite el control real y automático de las salidas y entradas de los empleados que tienen permisos aprobados. Ya no se basa en horarios planificados, sino en el registro físico mediante escaneo de DNI.

### **Beneficios del Sistema**
- ✅ **Control Real**: Seguimiento físico vs. planificado
- ✅ **Automatización**: Cálculo automático de horas utilizadas
- ✅ **Transparencia**: Historial completo de movimientos
- ✅ **Cumplimiento**: Control efectivo de políticas institucionales

### **Flujo del Proceso**
```
1. Empleado solicita permiso → 2. Aprobación normal → 
3. Sistema crea seguimiento automático → 4. RRHH registra salida física → 
5. RRHH registra entrada física → 6. Sistema calcula tiempo real utilizado
```

---

## **Acceso al Dashboard de RRHH**

### **Cómo Acceder**
1. **Iniciar sesión** en el sistema con credenciales de Jefe de RRHH
2. En el menú lateral izquierdo, hacer clic en **"Dashboard RRHH"**
3. Se abrirá la pantalla principal de seguimiento

### **Pantalla Principal**
El Dashboard de RRHH contiene:

#### **Sección Superior - Escáner de DNI**
- 📱 **Campo de entrada**: Para escanear o ingresar DNI manualmente
- 🔍 **Botón "Buscar Manual"**: Para búsquedas sin escáner
- 📋 **Área de resultados**: Muestra información del empleado encontrado

#### **Tarjetas de Estado (Tiempo Real)**
- 🕐 **Pendientes de Salida**: Empleados con permisos aprobados que aún no han salido
- 🚪 **Fuera de Oficina**: Empleados que han salido pero no han regresado
- ⚠️ **Con Retraso**: Empleados que excedieron el tiempo esperado de regreso

#### **Tabla de Seguimientos Activos**
- 📊 Lista detallada de todos los seguimientos en curso
- 🔄 Actualización automática cada 30 segundos
- 🔍 Información completa de cada empleado y su estado

---

## **Operación del Escáner de DNI**

### **Configuración Inicial**
Antes de usar el escáner, verificar que:
- ✅ El lector USB esté conectado
- ✅ Windows lo reconozca como "HID Keyboard Device"
- ✅ El cursor esté en el campo de entrada del DNI

### **Proceso de Escaneo**

#### **Método 1: Escaneo Automático (Recomendado)**
1. **Enfocar** el campo de entrada (debe estar activo por defecto)
2. **Acercar** el DNI al escáner (distancia: 5-25 cm)
3. **Escuchar** el pitido confirmatorio del escáner
4. **Esperar** que aparezca la información del empleado

#### **Método 2: Entrada Manual**
1. **Hacer clic** en el campo de entrada
2. **Escribir** el DNI de 8 dígitos
3. **Presionar Enter** o hacer clic en "Buscar Manual"

### **Interpretación de Resultados**

#### **Caso 1: Empleado Encontrado**
```
✅ RESULTADO EXITOSO
Empleado: Juan Pérez García
DNI: 12345678
Estado: Pendiente de Salida
Tipo Permiso: Permiso Personal
Acción Requerida: [REGISTRAR SALIDA]
```

#### **Caso 2: No Encontrado**
```
❌ NO ENCONTRADO
"No se encontró un permiso activo para este DNI"

Posibles causas:
- El empleado no tiene permisos aprobados pendientes
- Ya completó su permiso
- Error en el DNI escaneado
```

---

## **Registro de Salidas y Entradas**

### **Registro de Salida**

#### **Cuándo Registrar**
- ✅ El empleado está **físicamente presente** para salir
- ✅ Ha mostrado su **DNI original**
- ✅ Coincide con la **información en pantalla**

#### **Pasos para Registrar Salida**
1. **Escanear/buscar** el DNI del empleado
2. **Verificar** que los datos mostrados son correctos
3. **Hacer clic** en el botón **"Registrar Salida"**
4. **Agregar observaciones** si es necesario (opcional)
5. **Confirmar** la acción

#### **Ejemplo de Observaciones para Salidas**
```
- "Empleado salió 10 minutos tarde por reunión"
- "Presentó certificado médico"
- "Salida autorizada por jefe inmediato"
- "Permiso por emergencia familiar"
```

### **Registro de Entrada**

#### **Cuándo Registrar**
- ✅ El empleado **regresó físicamente** a la oficina
- ✅ Ha mostrado su **DNI original**
- ✅ El sistema muestra estado **"Fuera de Oficina"**

#### **Pasos para Registrar Entrada**
1. **Escanear/buscar** el DNI del empleado
2. **Verificar** que el empleado estuvo fuera
3. **Hacer clic** en el botón **"Registrar Regreso"**
4. **Agregar observaciones** si es necesario
5. **Confirmar** la acción

#### **Ejemplo de Observaciones para Entradas**
```
- "Regresó 30 minutos después de lo esperado"
- "Trajo documentos solicitados"
- "Regreso normal, sin novedades"
- "Explicó retraso por tráfico"
```

### **Cálculo Automático de Tiempo**
El sistema calcula automáticamente:
- ⏱️ **Tiempo exacto** entre salida y entrada
- 📊 **Horas utilizadas** con precisión de minutos
- 📈 **Comparación** con tiempo planificado (si aplica)

---

## **Monitoreo en Tiempo Real**

### **Interpretación de Estados**

#### **🔵 PENDIENTE DE SALIDA**
- **Significado**: Permiso aprobado, empleado aún no ha salido
- **Acción**: Esperar a que el empleado se presente para registrar salida
- **Color**: Azul

#### **🟡 FUERA DE OFICINA**
- **Significado**: Empleado salió, aún no ha regresado
- **Acción**: Monitorear tiempo transcurrido, esperar regreso
- **Color**: Amarillo

#### **🟢 HA REGRESADO**
- **Significado**: Empleado completó su permiso exitosamente
- **Acción**: Ninguna, proceso completado
- **Color**: Verde

#### **🔴 RETRASO EN REGRESO**
- **Significado**: Empleado excedió tiempo esperado (+ 1 hora de gracia)
- **Acción**: **ATENCIÓN INMEDIATA REQUERIDA**
- **Color**: Rojo

### **Monitoreo de Tiempo**
- 🕐 **Tiempo de gracia**: 1 hora después del tiempo esperado
- ⚠️ **Alerta automática**: El sistema marca automáticamente retrasos
- 📱 **Notificaciones**: Se generan alertas para el personal de RRHH

---

## **Gestión de Retrasos**

### **Identificación de Retrasos**
Los retrasos se identifican automáticamente cuando:
- ⏰ Han pasado **más de 8 horas + 1 hora de gracia** desde la salida
- 🚨 El empleado no ha registrado su regreso
- 🔴 El estado cambia automáticamente a **"RETRASO EN REGRESO"**

### **Protocolo para Retrasos**

#### **Paso 1: Verificación Inmediata**
1. **Contactar** al empleado por teléfono/WhatsApp
2. **Verificar** si ya regresó y olvidó registrarse
3. **Consultar** con seguridad/recepción sobre su presencia

#### **Paso 2: Registro Manual (si corresponde)**
Si el empleado ya regresó pero no se registró:
1. **Escanear su DNI**
2. **Registrar regreso** con observaciones
3. **Anotar**: "Regreso manual - empleado olvidó registrarse"

#### **Paso 3: Escalamiento**
Si no se puede contactar al empleado:
1. **Informar** al jefe inmediato del empleado
2. **Documentar** el incidente en las observaciones
3. **Seguir** protocolo institucional para ausencias

#### **Ejemplo de Observaciones para Retrasos**
```
- "Empleado contactado - regresó hace 30 min, olvidó registrarse"
- "No responde llamadas - informado a jefe inmediato"
- "Emergencia médica confirmada - documentación pendiente"
- "Justificó retraso por transporte público"
```

---

## **Resolución de Problemas**

### **Problemas Comunes y Soluciones**

#### **❌ Problema: "El escáner no funciona"**
**Posibles Causas y Soluciones:**
1. **Cable desconectado** → Verificar conexión USB
2. **No reconocido por Windows** → Reiniciar computador
3. **Campo no enfocado** → Hacer clic en el campo de entrada
4. **Escáner configurado incorrectamente** → Consultar manual técnico

**Solución Temporal:**
- Usar entrada manual de DNI
- Escribir el número y presionar Enter

#### **❌ Problema: "DNI no encontrado"**
**Verificaciones:**
1. **DNI correcto** → Verificar los 8 dígitos
2. **Permiso aprobado** → Consultar en "Mis Solicitudes"
3. **Estado del permiso** → Debe estar "aprobado" y tener seguimiento activo

**Pasos para resolver:**
1. Verificar en la lista de "Aprobaciones" si el permiso existe
2. Comprobar el estado del seguimiento en la tabla
3. Si persiste, consultar con administrador del sistema

#### **❌ Problema: "El sistema está lento"**
**Soluciones:**
1. **Actualizar página** → Presionar F5
2. **Limpiar caché** → Ctrl + F5
3. **Cerrar otras pestañas** del navegador
4. **Verificar conexión** a internet

#### **❌ Problema: "No aparece el botón de acción"**
**Causas posibles:**
1. **Permisos insuficientes** → Verificar rol de "jefe_rrhh"
2. **Sesión expirada** → Cerrar sesión y volver a iniciar
3. **Error en el navegador** → Recargar página

---

## **Procedimientos de Emergencia**

### **🚨 Emergencia 1: Sistema Completamente Fuera de Servicio**

#### **Protocolo de Contingencia:**
1. **Documentar manualmente** en formato físico:
   ```
   Fecha: ___________
   Empleado: _________________
   DNI: ___________
   Hora Salida: ___________
   Hora Regreso: ___________
   Observaciones: _________________
   Registrado por: _________________
   ```

2. **Usar formato digital** (Excel/Word) como respaldo
3. **Contactar** inmediatamente al administrador del sistema
4. **Ingresar datos** al sistema una vez que se restablezca

### **🚨 Emergencia 2: Falla del Escáner**

#### **Protocolo:**
1. **Cambiar a entrada manual** inmediatamente
2. **Solicitar** DNI físico al empleado
3. **Escribir manualmente** el número
4. **Continuar** con el proceso normal
5. **Reportar** la falla técnica

### **🚨 Emergencia 3: Empleado en Situación Crítica**

#### **Si un empleado no regresa y hay sospecha de emergencia:**
1. **NO registrar regreso** hasta confirmar la situación
2. **Contactar** inmediatamente:
   - Jefe inmediato del empleado
   - Administración de RRHH
   - Si es necesario, servicios de emergencia
3. **Documentar todo** en observaciones detalladas
4. **Seguir** protocolo institucional para emergencias

---

## **Mejores Prácticas**

### **✅ Prácticas Recomendadas**

#### **Operación Diaria**
- 🌅 **Inicio del día**: Verificar que el escáner funcione
- 🔍 **Revisión constante**: Monitorear cada 30 minutos
- 📝 **Documentación**: Siempre agregar observaciones relevantes
- 🔄 **Actualización**: Refrescar la página cada hora

#### **Control de Calidad**
- ✅ **Verificar identidad**: Siempre pedir DNI físico
- ✅ **Confirmar información**: Comparar datos en pantalla
- ✅ **Doble verificación**: Para casos complejos o dudosos
- ✅ **Registro inmediato**: No posponer las operaciones

#### **Comunicación**
- 📞 **Contacto directo**: Llamar en caso de dudas
- 💬 **Observaciones claras**: Usar lenguaje específico
- 📊 **Reportes regulares**: Informar anomalías diariamente
- 🤝 **Coordinación**: Mantener comunicación con jefes inmediatos

### **❌ Prácticas a Evitar**

- ❌ **NO** registrar sin ver al empleado físicamente
- ❌ **NO** aceptar DNI de terceros o copias
- ❌ **NO** registrar a solicitud sin verificación
- ❌ **NO** dejar el escáner desatendido
- ❌ **NO** omitir observaciones en casos especiales

---

## **Contactos de Soporte**

### **📞 Soporte Técnico**
- **Administrador del Sistema**: [Insertar contacto]
- **Soporte TI**: [Insertar contacto]
- **Horario**: Lunes a Viernes, 8:00 AM - 5:00 PM

### **📋 Soporte Operativo**
- **Jefe de RRHH**: [Insertar contacto]
- **Administración**: [Insertar contacto]
- **Emergencias**: [Insertar contacto de emergencia]

### **💻 Recursos Adicionales**
- **Manual técnico**: `CONFIGURACION_LECTOR_CODIGOS.md`
- **Documentación del sistema**: `NUEVA_FUNCIONALIDAD_SEGUIMIENTO.txt`
- **Portal de ayuda**: [Insertar URL si existe]

---

## **Registro de Cambios y Actualizaciones**

| Fecha | Versión | Cambios |
|-------|---------|---------|
| 21/08/2025 | 1.0 | Versión inicial del manual |
| ___ | ___ | [Futuras actualizaciones] |

---

## **Anexos**

### **Anexo A: Códigos de Estado del Sistema**
- `pending` = Pendiente de Salida
- `out` = Fuera de Oficina  
- `returned` = Ha Regresado
- `overdue` = Retraso en Regreso

### **Anexo B: Mensajes de Error Comunes**
- "DNI inválido" = Formato incorrecto (debe ser 8 dígitos)
- "No se encontró permiso activo" = Sin seguimientos pendientes
- "No se pudo registrar" = Error del sistema, intentar nuevamente

### **Anexo C: Atajos de Teclado**
- `Enter` = Buscar DNI ingresado manualmente
- `Escape` = Limpiar campo de entrada
- `F5` = Actualizar página
- `Ctrl + F5` = Actualizar sin caché

---

**Este manual debe ser revisado y actualizado periódicamente para mantener su relevancia y precisión.**

**Fecha de elaboración: 21 de Agosto, 2025**
**Elaborado por: Sistema de Papeletas Digitales - Módulo de Seguimiento Físico**