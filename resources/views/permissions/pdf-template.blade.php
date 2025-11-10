<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Permiso {{ $request->request_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 8px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 14px;
            color: #1f2937;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 3px 0 0 0;
            font-size: 12px;
            color: #2563eb;
            font-weight: bold;
        }
        
        .header .request-number {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: bold;
            color: #dc2626;
        }
        
        .content-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .content-row {
            display: table-row;
        }
        
        .content-cell {
            display: table-cell;
            vertical-align: top;
            padding: 2px 5px;
        }
        
        .section {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            margin-bottom: 8px;
            background-color: #fafafa;
        }
        
        .section-header {
            background-color: #f3f4f6;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 9px;
            color: #1f2937;
            border-bottom: 1px solid #d1d5db;
            margin: 0;
        }
        
        .section-content {
            padding: 8px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 2px 5px;
            font-size: 9px;
            vertical-align: top;
        }
        
        .info-table .label {
            font-weight: bold;
            color: #4b5563;
            width: 25%;
        }
        
        .info-table .value {
            color: #1f2937;
        }
        
        .permission-info {
            background-color: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 8px;
        }
        
        .reason-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 6px;
            margin-top: 5px;
            font-size: 9px;
            min-height: 30px;
        }
        
        .signatures-section {
            margin-top: 10px;
        }
        
        .signature-boxes {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        
        .signature-row {
            display: table-row;
        }
        
        .signature-cell {
            display: table-cell;
            width: 33.33%;
            padding: 0 3px;
            vertical-align: top;
        }
        
        .signature-box {
            border: 1px solid #374151;
            border-radius: 4px;
            padding: 6px;
            text-align: center;
            background-color: #ffffff;
            min-height: 80px;
            position: relative;
        }
        
        .signature-title {
            font-weight: bold;
            font-size: 8px;
            color: #1f2937;
            margin-bottom: 3px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 2px;
        }
        
        .signature-placeholder {
            color: #6b7280;
            font-style: italic;
            font-size: 7px;
            margin: 8px 0;
            line-height: 1.2;
        }
        
        .signature-info {
            font-size: 7px;
            color: #374151;
            margin-top: 5px;
            line-height: 1.1;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            color: white;
            background-color: #f59e0b;
        }
        
        .legal-text {
            font-size: 7px;
            color: #6b7280;
            text-align: center;
            margin-top: 8px;
            line-height: 1.2;
        }
        
        .footer {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #e5e7eb;
            font-size: 7px;
            color: #6b7280;
            text-align: center;
        }
        
        .compact-grid {
            display: table;
            width: 100%;
        }
        
        .compact-row {
            display: table-row;
        }
        
        .compact-left {
            display: table-cell;
            width: 70%;
            vertical-align: top;
            padding-right: 8px;
        }
        
        .compact-right {
            display: table-cell;
            width: 30%;
            vertical-align: top;
        }
        
        .highlight-box {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 6px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Encabezado Compacto -->
    <div class="header">
        <h1>MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO</h1>
        <h2>PAPELETA DE SOLICITUD DE SALIDA</h2>
        <div class="request-number">N° {{ $request->request_number }}</div>
    </div>

    <!-- Layout Principal en 2 Columnas -->
    <div class="compact-grid">
        <div class="compact-row">
            <!-- Columna Izquierda: Información Principal -->
            <div class="compact-left">
                
                <!-- Información del Solicitante -->
                <div class="section">
                    <div class="section-header">DATOS DEL SOLICITANTE</div>
                    <div class="section-content">
                        <table class="info-table">
                            <tr>
                                <td class="label">Empleado:</td>
                                <td class="value"><strong>{{ $request->user->first_name }} {{ $request->user->last_name }}</strong></td>
                                <td class="label">DNI:</td>
                                <td class="value">{{ $request->user->dni }}</td>
                            </tr>
                            <tr>
                                <td class="label">Departamento:</td>
                                <td class="value">{{ $request->user->department->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Jefe Inmediato:</td>
                                <td class="value" colspan="3">
                                    @if($request->user->immediateSupervisor)
                                        {{ $request->user->immediateSupervisor->first_name }} {{ $request->user->immediateSupervisor->last_name }}
                                    @else
                                        No asignado
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Detalles del Permiso -->
                <div class="permission-info">
                    <table class="info-table">
                        <tr>
                            <td class="label">Tipo de Permiso:</td>
                            <td class="value"><strong>{{ $request->permissionType->name }}</strong></td>
                        </tr>
                        @if($request->tracking && ($request->tracking->departure_datetime || $request->tracking->return_datetime))
                        <tr>
                            <td class="label">Seguimiento:</td>
                            <td class="value">
                                @if($request->tracking->departure_datetime)
                                    <strong>Salida:</strong> {{ $request->tracking->departure_datetime->format('d/m/Y H:i') }}
                                    @if($request->tracking->return_datetime)
                                        <br><strong>Regreso:</strong> {{ $request->tracking->return_datetime->format('d/m/Y H:i') }}
                                        <br><strong>Tiempo utilizado:</strong> {{ $request->tracking->actual_hours_used }} horas
                                    @endif
                                @else
                                    Pendiente de registro de salida
                                @endif
                            </td>
                        </tr>
                        @else
                        <tr>
                            <td class="label">Seguimiento:</td>
                            <td class="value">Se registrará al momento de la salida/entrada</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Fecha de Solicitud:</td>
                            <td class="value">{{ $request->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Motivo -->
                <div class="section">
                    <div class="section-header">MOTIVO DE LA SOLICITUD</div>
                    <div class="section-content">
                        <div class="reason-box">{{ $request->reason }}</div>
                    </div>
                </div>

                <!-- Documentos (si existen) -->
                @if($request->documents->count() > 0)
                <div class="section">
                    <div class="section-header">DOCUMENTOS ADJUNTOS ({{ $request->documents->count() }})</div>
                    <div class="section-content">
                        @foreach($request->documents as $document)
                            <div style="font-size: 8px; margin-bottom: 2px;">
                                • {{ $document->getDocumentTypeLabel() }}: {{ $document->original_name }}
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <!-- Columna Derecha: Marco Legal y Información Adicional -->
            <div class="compact-right">
                <!-- Marco Legal -->
                <div class="section">
                    <div class="section-header">MARCO LEGAL</div>
                    <div class="section-content">
                        <div style="font-size: 8px; line-height: 1.2;">
                            <strong>Base Legal:</strong><br>
                            • Reglamento Interno de Trabajo<br>
                            • Arts. 53° al 68° - Permisos Laborales<br>
                            • Ley N° 27444 - Procedimiento Administrativo<br>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Sección de Firmas Digitales -->
    <div class="signatures-section">
        <div class="section-header" style="text-align: center; margin-bottom: 5px;">FIRMAS DIGITALES REQUERIDAS - FIRMA PERÚ</div>
        
        <div class="signature-boxes">
            <div class="signature-row">
                
                <!-- Firma del Empleado -->
                <div class="signature-cell">
                    <div class="signature-box">
                        <div class="signature-title">1. EMPLEADO SOLICITANTE</div>
                    </div>
                </div>

                <!-- Firma del Jefe Inmediato -->
                <div class="signature-cell">
                    <div class="signature-box">
                        <div class="signature-title">2. JEFE INMEDIATO</div>
                    </div>
                </div>

                <!-- Firma de RRHH -->
                <div class="signature-cell">
                    <div class="signature-box">
                        <div class="signature-title">3. RECURSOS HUMANOS</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Apartado de Observaciones -->
    <div class="section" style="margin-top: 8px;">
        <div class="section-header">OBSERVACIONES Y COMENTARIOS</div>
        <div class="section-content">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 33.33%; padding: 3px; vertical-align: top; border-right: 1px solid #e5e7eb;">
                        <div style="font-size: 8px; font-weight: bold; margin-bottom: 2px;">Empleado:</div>
                        <div style="border: 1px solid #e5e7eb; height: 25px; background-color: #f9fafb;"></div>
                    </td>
                    <td style="width: 33.33%; padding: 3px; vertical-align: top; border-right: 1px solid #e5e7eb;">
                        <div style="font-size: 8px; font-weight: bold; margin-bottom: 2px;">Jefe Inmediato:</div>
                        <div style="border: 1px solid #e5e7eb; height: 25px; background-color: #f9fafb;"></div>
                    </td>
                    <td style="width: 33.33%; padding: 3px; vertical-align: top;">
                        <div style="font-size: 8px; font-weight: bold; margin-bottom: 2px;">RRHH:</div>
                        <div style="border: 1px solid #e5e7eb; height: 25px; background-color: #f9fafb;"></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Texto Legal y Hash -->
    <div class="legal-text">
        <strong>IMPORTANTE:</strong> Este documento cuenta con firma digital certificada por FIRMA PERÚ, garantizando su autenticidad e integridad.
        Las firmas digitales tienen plena validez legal según la Ley N° 27269.
    </div>

    <!-- Pie de página -->
    <div class="footer">
        Generado automáticamente por el Sistema de Papeletas Digitales - Municipalidad Distrital de San Jerónimo
        <br>Este documento es válido sin firma física en virtud del uso de firma digital certificada.
    </div>
</body>
</html>