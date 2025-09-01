<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva Solicitud de Permiso</title>
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
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e5e7eb;
        }
        .details {
            background-color: white;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #4F46E5;
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
        .button:hover {
            background-color: #3730A3;
        }
        .urgent {
            background-color: #DC2626;
        }
        .urgent:hover {
            background-color: #B91C1C;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Nueva Solicitud de Permiso</h1>
    </div>
    
    <div class="content">
        <p>Estimado/a <strong>{{ $approver_name }}</strong>,</p>
        
        <p>Se ha enviado una nueva solicitud de permiso que requiere su aprobación:</p>
        
        <div class="details">
            <h3>Detalles de la Solicitud</h3>
            <ul>
                <li><strong>Número de Solicitud:</strong> {{ $request_number }}</li>
                <li><strong>Empleado:</strong> {{ $employee_name }}</li>
                <li><strong>Tipo de Permiso:</strong> {{ $permission_type }}</li>
                <li><strong>Fecha y Hora de Inicio:</strong> {{ $start_date }}</li>
                <li><strong>Fecha y Hora de Fin:</strong> {{ $end_date }}</li>
                <li><strong>Horas Solicitadas:</strong> {{ $requested_hours }}</li>
                <li><strong>Motivo:</strong> {{ $reason }}</li>
            </ul>
        </div>
        
        <p>Por favor, revise la solicitud y proceda con la aprobación o rechazo correspondiente.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $approval_url }}" class="button urgent">Revisar Solicitud</a>
            <a href="{{ $dashboard_url }}" class="button">Ver Dashboard</a>
        </div>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;">
        
        <p><small>
            Este correo ha sido generado automáticamente por el Sistema de Papeletas Digitales. 
            Por favor, no responda a este correo.
        </small></p>
    </div>
</body>
</html>