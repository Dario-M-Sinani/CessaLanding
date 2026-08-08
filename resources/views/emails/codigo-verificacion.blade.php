<x-mail::message>
# Verificación de correo

@if($clientName)
Hola {{ $clientName }},
@else
Hola,
@endif

Estás actualizando tus datos de contacto en el portal de CESSA. Usa el siguiente código para confirmar que este correo te pertenece:

<x-mail::panel>
<div style="font-size: 28px; font-weight: bold; letter-spacing: 6px; text-align: center;">{{ $codigo }}</div>
</x-mail::panel>

Este código vence en 10 minutos. Si no solicitaste esta actualización, puedes ignorar este correo.

Saludos,<br>
CESSA
</x-mail::message>
