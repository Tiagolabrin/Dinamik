# Consultorio Lupiañez

## 1. Nombre y descripción de la aplicación

**Consultorio Lupiañez** es un turnero web para un consultorio odontológico.
Permite que los pacientes soliciten turnos según la disponibilidad de los
odontólogos y que cada perfil gestione la información correspondiente a sus
tareas.

## 2. Integrantes del grupo

- Tiago Labrin
- Josefina Lupiañez
- Paloma Stucke

## 3. Funcionalidades implementadas

- Registro de pacientes e inicio y cierre de sesión.
- Edición del perfil propio; el paciente puede actualizar sus datos personales
  y el odontólogo también puede gestionar su matrícula, disponibilidades y
  períodos de vacaciones. Todos los perfiles pueden cambiar su contraseña
  verificando la actual e ingresando y confirmando la nueva.
- Consulta de odontólogos y selección de fecha y horarios disponibles.
- Reserva de turnos con observaciones opcionales y pantalla de confirmación con
  los datos del consultorio.
- Consulta de turnos propios por pacientes y consulta de agenda por
  odontólogos. Ambos listados muestran turnos desde las 00:00 de tres días
  antes de la fecha actual en adelante.
- Cancelación de turnos futuros por el paciente titular, el odontólogo
  responsable o el administrador. Al reservar o cancelar un turno, se guarda
  una notificación para el paciente y el odontólogo; Notific8 la muestra cuando
  cada uno vuelve a usar la aplicación.
- Administración de odontólogos y pacientes: alta, consulta, edición y baja,
  con restricciones para impedir eliminaciones cuando existen turnos futuros.
- Alta y actualización de los datos de contacto del consultorio.
- Validación de los datos recibidos y control de disponibilidad, vacaciones y
  superposición de turnos al reservar.

## 4. Modelo de datos y relaciones entre entidades

- **User** almacena los datos y credenciales de las cuentas. Un usuario puede
  tener un perfil de **Paciente** o de **Odontologo**; estos perfiles se
  relacionan con `User` mediante una relación uno a uno.
- **Odontologo** tiene muchas **Disponibilidad** y muchos períodos de
  **Vacaciones**. Cada disponibilidad y período de vacaciones pertenece a un
  odontólogo (relaciones uno a muchos).
- **Appointment** almacena el turno, su fecha y horario, duración, estado,
  descripción y metadatos. La librería registra sus participantes en
  `appointment_participants`, una asociación polimórfica que relaciona un turno
  con el odontólogo organizador y el paciente asistente. El rol de cada
  participante queda guardado en esa asociación.
- **Consultorio** almacena la dirección, el teléfono y el correo de contacto.

Las relaciones se definen en los modelos Eloquent y se utilizan para consultar
perfiles, disponibilidades, vacaciones y participantes de los turnos.

## 5. Perfiles de usuario y permisos

- **Administrador:** puede crear, consultar, editar y eliminar odontólogos;
  consultar, editar y eliminar pacientes; y cargar o actualizar los datos del
  consultorio. También puede consultar y cancelar turnos. La aplicación no
  ofrece actualmente funciones para editar o eliminar turnos: para un turno,
  la acción disponible es cancelarlo.
- **Odontólogo:** puede consultar su agenda, cancelar sus propios turnos
  futuros y editar su perfil, matrícula, disponibilidades y vacaciones.
- **Paciente:** puede registrarse, editar su perfil, consultar horarios,
  reservar turnos y cancelar sus propios turnos futuros.

Las rutas de administración requieren una sesión iniciada y el middleware de
administrador. Las operaciones de turnos comprueban además quién participa en
cada turno.

## 6. Funcionalidad AJAX

La aplicación utiliza la API `fetch()` de JavaScript para hacer peticiones
asíncronas y actualizar información sin recargar la página:

- **Horarios de reserva:** al elegir un odontólogo y una fecha, el formulario
  consulta `GET /reserva/horarios` y envía `odontologo_id` y `fecha`. Laravel
  valida los datos y responde con JSON (`horarios`), calculados según la
  disponibilidad semanal, las vacaciones y los turnos existentes. JavaScript
  actualiza las opciones de horario y comunica si no hay disponibilidad o si
  ocurre un error.
- **Notificaciones de turnos:** al cargar una página autenticada,
  `resources/js/app.js` consulta `GET /notificaciones/no-leidas`. Laravel
  devuelve las notificaciones pendientes de reservas y cancelaciones, y
  Notific8 las presenta como avisos visuales. Después, JavaScript envía sus
  identificadores a `POST /notificaciones/leidas`, incluyendo el token CSRF,
  para marcarlas como leídas. La consulta devuelve hasta 50 avisos pendientes
  por vez.

Notific8 se encarga únicamente de mostrar los avisos; las peticiones AJAX las
realiza `fetch()` y Laravel guarda y recupera las notificaciones. Estas se
consultan al cargar las páginas, no en tiempo real mientras están abiertas.

## 7. Librería externa y justificación

El proyecto utiliza
[`roundly-consulting/appointments-for-laravel`](https://github.com/roundly-consulting/appointments-for-laravel),
declarada en `composer.json`. Se eligió para modelar y persistir turnos,
asociarles participantes con roles y administrar sus estados. En el flujo de
reserva, la librería crea el turno con su fecha, duración y participantes, le
asigna el estado confirmado y ayuda a evitar superposiciones. La aplicación
complementa con sus reglas de disponibilidad y
vacaciones del odontólogo antes de guardar la reserva.

Para avisos visuales se utiliza
[`notific8`](https://github.com/pynklynn/notific8), instalada como dependencia
JavaScript en `package.json`. Los avisos de reserva y cancelación se guardan
mediante las notificaciones de base de datos de Laravel y se muestran con
Notific8 cuando el destinatario entra a la aplicación. Además, si la sesión
está autenticada permite consultar una bandeja con las últimas 15
notificaciones. Esta implementación no envía correos.

Después de instalar las dependencias del proyecto, ejecutá `npm run build` para
generar los recursos JavaScript y CSS usados por Vite.

## Datos de prueba

En entornos distintos de producción, `php artisan migrate --seed` crea el
administrador inicial y datos de demostración para probar los perfiles de
paciente y odontólogo, sus disponibilidades y vacaciones, un turno futuro y los
datos del consultorio. Antes de ejecutarlo, configurá en `.env` las credenciales
del administrador inicial y contraseñas propias de al menos 8 caracteres para
las cuentas de demostración:

```dotenv
ADMIN_INITIAL_NAME=Nombre del administrador
ADMIN_INITIAL_EMAIL=admin@example.test
ADMIN_INITIAL_PASSWORD=una-clave-segura
DEMO_PACIENTE_PASSWORD=una-clave-segura
DEMO_ODONTOLOGO_PASSWORD=otra-clave-segura
```

Las cuentas de demostración son `paciente.demo@example.test` y
`odontologa.demo@example.test`. La carga inicial del administrador y de las
cuentas de demonstración conserva las contraseñas y perfiles de cuentas que ya existen.
Al repetir la carga, las disponibilidades del odontólogo demo se establecen
de lunes a viernes de 09:00 a 12:00 y se actualiza su período de vacaciones de
demostración. El consultorio existente no se reemplaza. Los datos demo no se
cargan en producción.

`php artisan migrate --seed` aplica las migraciones pendientes y carga los
seeders; no elimina las tablas ni los datos existentes. Si necesitamos reconstruir
una base local desde cero, usamos `php artisan migrate:fresh --seed`, pero
ese comando elimina primero todas las tablas y sus datos.
