<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitud de Permiso Rechazada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #DC2626;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background-color: #fef2f2;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #fecaca;
        }
        .details {
            background-color: white;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #DC2626;
        }
        .rejection-reason {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4F46E5;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            background-color: #fee2e2;
            color: #DC2626;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>❌ Solicitud de Permiso Rechazada</h1>
    </div>
    
    <div class="content">
        <p>Estimado/a <strong>{{ $employee_name }}</strong>,</p>
        
        <p>Lamentamos informarle que su solicitud de permiso ha sido <strong>rechazada</strong> por {{ $approval_level }}.</p>
        
        <div class="details">
            <h3>Detalles de la Solicitud</h3>
            <ul>
                <li><strong>Número de Solicitud:</strong> {{ $request_number }}</li>
                <li><strong>Tipo de Permiso:</strong> {{ $permission_type }}</li>
                <li><strong>Fecha y Hora de Inicio:</strong> {{ $start_date }}</li>
                <li><strong>Fecha y Hora de Fin:</strong> {{ $end_date }}</li>
                <li><strong>Horas Solicitadas:</strong> {{ $requested_hours }}</li>
                <li><strong>Rechazado por:</strong> {{ $approver_name }} ({{ $approval_level }})</li>
                <li><strong>Estado:</strong> <span class="status-badge">Rechazado</span></li>
            </ul>
        </div>
        
        <div class="rejection-reason">
            <h3>🔍 Motivo del Rechazo</h3>
            <p><em>"{{ $rejection_reason }}"</em></p>
        </div>
        
        <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <p><strong>💡 Próximos pasos</strong></p>
            <ul>
                <li>Revise los comentarios del aprobador</li>
                <li>Considere realizar los ajustes necesarios</li>
                <li>Puede enviar una nueva solicitud con las correcciones correspondientes</li>
                <li>Si tiene dudas, consulte directamente con su supervisor</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $new_request_url }}" class="button">Crear Nueva Solicitud</a>
            <a href="{{ $dashboard_url }}" class="button" style="background-color: #6B7280;">Ver Mis Solicitudes</a>
        </div>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #fecaca;">
        
        <p><small>
            Este correo ha sido generado automáticamente por el Sistema de Papeletas Digitales. 
            Por favor, no responda a este correo.
        </small></p>
    </div>
</body>
</html>