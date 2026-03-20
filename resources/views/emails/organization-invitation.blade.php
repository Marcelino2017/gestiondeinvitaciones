<h2>Invitacion a organizacion</h2>

<p>Has sido invitado a la organizacion <strong>{{ $organization->name }}</strong>.</p>
<p>Rol asignado: <strong>{{ $invitation->role }}</strong></p>
<p>Token de invitacion: <code>{{ $invitation->token }}</code></p>
<p>Usa este token en el endpoint de aceptacion para completar el acceso.</p>
