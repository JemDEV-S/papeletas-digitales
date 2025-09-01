<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitud Pendiente de Aprobación RRHH</title>
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
            background-color: #7C3AED;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background-color: #f8fafc;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e2e8f0;
        }
        .details {
            background-color: white;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #7C3AED;
        }
        .approval-chain {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #7C3AED;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 5px;
        }
        .button:hover {
            background-color: #5B21B6;
        }
        .urgent {
            background-color: #DC2626;
        }
        .urgent:hover {
            background-color: #B91C1C;
        }
        .check-icon {
            color: #059669;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Solicitud Pendiente - Aprobación RRHH</h1>
    </div>
    
    <div class="content">
        <p>Estimado/a <strong>{{ $approver_name }}</strong>,</p>
        
        <p>Una solicitud de permiso ha sido aprobada por el jefe inmediato y ahora requiere su aprobación como Jefe de RRHH:</p>
        
        <div class="approval-chain">
            <h4>📋 Cadena de Aprobación</h4>
            <p><span class="check-icon">✅</span> <strong>Jefe Inmediato:</strong> {{ $previous_approver }} - <em>Aprobado</em></p>
            <p><span style="color: #d97706; font-weight: bold;">⏳</span> <strong>RRHH:</strong> {{ $approver_name }} - <em>Pendiente</em></p>
        </div>
        
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
        
        <div style="background-color: #fef3c7; border: 1px solid #fde68a; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <p><strong>📌 Nota Importante</strong></p>
            <p>Esta solicitud ya cuenta con la aprobación del jefe inmediato. Como última instancia de aprobación, su decisión determinará si el permiso es otorgado al empleado.</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $approval_url }}" class="button urgent">Revisar y Decidir</a>
            <a href="{{ $dashboard_url }}" class="button">Ver Dashboard RRHH</a>
        </div>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e2e8f0;">
        
        <p><small>
            Este correo ha sido generado automáticamente por el Sistema de Papeletas Digitales. 
            Por favor, no responda a este correo.
        </small></p>
    </div>
</body>
</html>