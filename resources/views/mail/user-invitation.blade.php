<x-mail::message>
# Hola, {{ $name }}

Te dieron de alta en **{{ config('app.name') }}**, el sistema de requisiciones y comprobación de viáticos{{ $role ? ', con el rol de **'.$role.'**' : '' }}.

Para entrar, primero configura tu contraseña:

<x-mail::button :url="$url">
Configurar mi contraseña
</x-mail::button>

El enlace vence en {{ $expiresInHours }} horas. Si vence, pide al administrador que te reenvíe la invitación.

Si no esperabas este correo, puedes ignorarlo.

Saludos,<br>
{{ config('app.name') }}

<x-slot:subcopy>
Si el botón no funciona, copia y pega este enlace en tu navegador: [{{ $url }}]({{ $url }})
</x-slot:subcopy>
</x-mail::message>
