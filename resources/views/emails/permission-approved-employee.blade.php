<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitud de Permiso Aprobada</title>
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
            background-color: #059669;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background-color: #f0fdf4;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #bbf7d0;
        }
        .details {
            background-color: white;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #059669;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .approved {
            background-color: #d1fae5;
            color: #059669;
        }
        .pending {
            background-color: #fef3c7;
            color: #d97706;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #059669;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Solicitud de Permiso Aprobada</h1>
    </div>
    
    <div class="content">
        <p>Estimado/a <strong>{{ $employee_name }}</strong>,</p>
        
        <p>
            @if($is_final_approval)
                ¡Excelente noticia! Su solicitud de permiso ha sido <strong>aprobada completamente</strong>.
            @else
                Su solicitud de permiso ha sido aprobada por su {{ $approval_level }} y ahora será revisada por RRHH.
            @endif
        </p>
        
        <div class="details">
            <h3>Detalles de la Solicitud</h3>
            <ul>
                <li><strong>Número de Solicitud:</strong> {{ $request_number }}</li>
                <li><strong>Tipo de Permiso:</strong> {{ $permission_type }}</li>
                <li><strong>Fecha y Hora de Inicio:</strong> {{ $start_date }}</li>
                <li><strong>Fecha y Hora de Fin:</strong> {{ $end_date }}</li>
                <li><strong>Horas Aprobadas:</strong> {{ $requested_hours }}</li>
                <li><strong>Aprobado por:</strong> {{ $approver_name }} ({{ $approval_level }})</li>
                <li><strong>Estado:</strong> 
                    @if($is_final_approval)
                        <span class="status-badge approved">Aprobado</span>
                    @else
                        <span class="status-badge pending">Pendiente RRHH</span>
                    @endif
                </li>
            </ul>
        </div>
        
        @if($is_final_approval)
            <div style="background-color: #d1fae5; padding: 15px; border-radius: 6px; margin: 20px 0;">
                <p><strong>✨ Su permiso está completamente aprobado</strong></p>
                <p>Puede proceder con los planes para las fechas solicitadas. Recuerde coordinar con su equipo cualquier entrega de trabajo pendiente.</p>
            </div>
        @else
            <div style="background-color: #fef3c7; padding: 15px; border-radius: 6px; margin: 20px 0;">
                <p><strong>⏳ Siguiente paso: {{ $next_step }}</strong></p>
                <p>Le notificaremos tan pronto como recibamos la decisión final.</p>
            </div>
        @endif
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $dashboard_url }}" class="button">Ver Mis Solicitudes</a>
        </div>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #bbf7d0;">
        
        <p><small>
            Este correo ha sido generado automáticamente por el Sistema de Papeletas Digitales. 
            Por favor, no responda a este correo.
        </small></p>
    </div>
</body>
</html>