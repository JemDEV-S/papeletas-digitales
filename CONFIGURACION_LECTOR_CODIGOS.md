# Configuración del Lector de Códigos de Barras

## Requisitos de Hardware

### Lector de Códigos de Barras USB
- **Recomendado**: Lectores que emulan teclado (HID - Human Interface Device)
- **Compatibilidad**: Windows, que funcionen como "keyboard wedge"
- **Tipos soportados**: Code 39, Code 128, QR Code
- **Marcas recomendadas**: 
  - Honeywell Voyager 1200g
  - Symbol/Zebra LS2208
  - Datalogic QuickScan Lite QW2100
  - Cualquier lector USB que emule teclado

### Especificaciones Técnicas
- **Conexión**: USB 2.0 o superior
- **Modo de operación**: Keyboard Wedge (emulación de teclado)
- **Formatos soportados**: Code 39, Code 128 (para DNI peruano)
- **Velocidad de lectura**: Mínimo 100 scans/segundo
- **Distancia de lectura**: 5-25 cm recomendado

## Configuración del Lector

### 1. Conexión Física
1. Conectar el lector USB al puerto USB del computador
2. Windows debería reconocerlo automáticamente como "HID Keyboard Device"
3. No requiere drivers adicionales si es un lector HID estándar

### 2. Configuración del Lector (si es programable)

#### Códigos de Configuración Básica
Escanear estos códigos en el manual del lector para configurarlo:

```
1. RESTABLECER A CONFIGURACIÓN DE FÁBRICA
2. HABILITAR PREFIJO: Ninguno
3. HABILITAR SUFIJO: Enter (CR)
4. FORMATO DE DATOS: Solo datos numéricos
5. LONGITUD: 8 dígitos (para DNI)
6. VERIFICACIÓN: Habilitar verificación de longitud
```

#### Configuración Específica para DNI Peruano
- **Tipo de código**: Code 39 o Code 128
- **Longitud esperada**: 8 dígitos
- **Prefijo**: Ninguno
- **Sufijo**: Enter (ASCII 13)
- **Verificación**: Activar checksum si está disponible

### 3. Verificación de Funcionamiento

#### Prueba Básica
1. Abrir un editor de texto (Notepad)
2. Escanear un código de barras de DNI
3. Debería aparecer el número de 8 dígitos seguido de Enter
4. Ejemplo: `12345678` + Enter

#### Prueba en Navegador Web
1. Abrir la página del sistema
2. Ir al Dashboard de RRHH
3. El cursor debería estar en el campo "DNI Scanner"
4. Escanear un DNI
5. El sistema debería procesar automáticamente

## Configuración del Sistema Web

### 1. Inclusión del Script
El archivo `barcode-scanner.js` debe estar incluido en las páginas que necesiten funcionalidad de escaneo:

```html
<script src="{{ asset('js/barcode-scanner.js') }}"></script>
```

### 2. Inicialización en el Dashboard de RRHH

```javascript
// Configuración básica
const scanner = createBarcodeScanner({
    inputElement: document.getElementById('dni-scanner'),
    onScan: handleDniScan,
    scanTimeout: 100,
    minLength: 8,
    maxLength: 8
});

// Función de manejo de escaneo
function handleDniScan(dni) {
    return fetch('/tracking/api/scan-dni', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ dni: dni })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayScanResult(data);
        } else {
            showAlert(data.message, 'error');
        }
    });
}
```

## Resolución de Problemas

### Problema: El lector no es reconocido
**Solución:**
1. Verificar conexión USB
2. Probar en otro puerto USB
3. Verificar en Administrador de Dispositivos
4. Debe aparecer como "HID Keyboard Device"

### Problema: El lector escanea pero no ingresa datos
**Solución:**
1. Verificar que está en modo "Keyboard Wedge"
2. Probar en editor de texto primero
3. Verificar configuración de sufijo (Enter)
4. Reconfigurar el lector según manual

### Problema: El sistema no procesa el escaneo
**Solución:**
1. Verificar que JavaScript está habilitado
2. Comprobar la consola del navegador por errores
3. Verificar que el campo input tiene focus
4. Comprobar formato del DNI (8 dígitos)

### Problema: Múltiples caracteres o caracteres incorrectos
**Solución:**
1. Limpiar el lente del lector
2. Ajustar distancia de escaneo
3. Verificar calidad del código de barras
4. Reconfigurar formato de datos en el lector

## Mantenimiento

### Limpieza Regular
- Limpiar lente con paño suave
- Evitar líquidos directos
- Verificar conexión USB periódicamente

### Verificación Periódica
- Probar escaneo diario
- Verificar precisión de lectura
- Comprobar velocidad de respuesta

### Respaldo de Configuración
- Documentar configuración específica del lector
- Guardar códigos de configuración utilizados
- Mantener manual del fabricante disponible

## Códigos de Barras de Prueba

Para pruebas sin DNI físico, crear códigos de barras de prueba:

### DNIs de Prueba
```
12345678
87654321
11111111
22222222
```

### Generación Online
- Utilizar generadores online de Code 39 o Code 128
- Imprimir en papel para pruebas
- Verificar que el formato sea compatible

## Configuración Avanzada

### Para Múltiples Lectores
Si se requieren múltiples estaciones:
1. Cada computador necesita su propio lector
2. Configuración idéntica en todos los lectores
3. Sincronización de datos via base de datos central

### Configuración de Red
- Sistema web accesible desde estación de RRHH
- Conexión estable a internet/intranet
- Backup de conectividad (móvil/ethernet)

### Integración con Active Directory
- Si aplica, configurar autenticación SSO
- Permisos específicos para jefe de RRHH
- Auditoría de accesos

---

**Nota Importante**: Este sistema está diseñado para funcionar con lectores USB estándar que emulan teclado. No requiere software adicional del fabricante, pero algunos lectores avanzados pueden ofrecer características adicionales mediante su software específico.

**Contacto de Soporte**: Para asistencia técnica con la configuración, contactar al administrador del sistema o al departamento de TI de la institución.