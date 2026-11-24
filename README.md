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
  períodos de vacaciones.
- Consulta de odontólogos y selección de fecha y horarios disponibles.
- Reserva de turnos con observaciones opcionales y pantalla de confirmación con
  los datos del consultorio.
- Consulta de turnos propios por pacientes y consulta de agenda por
  odontólogos.
- Cancelación de turnos futuros por el paciente titular, el odontólogo
  responsable o el administrador.
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

En el formulario de reserva, al elegir un odontólogo y una fecha, JavaScript
envía una petición asíncrona con `fetch()` al endpoint
`GET /reserva/horarios`, incluyendo el identificador del odontólogo y la fecha.
Laravel valida esos datos y responde con un JSON que contiene los horarios
disponibles, considerando la disponibilidad semanal, las vacaciones y los
turnos ya reservados. La página actualiza la lista de horarios sin recargarse y
muestra un mensaje si no hay horarios o si ocurre un error.

## 7. Librería externa y justificación

El proyecto utiliza
[`roundly-consulting/appointments-for-laravel`](https://github.com/roundly-consulting/appointments-for-laravel),
declarada en `composer.json`. Se eligió para modelar y persistir turnos,
asociarles participantes con roles y administrar sus estados. En el flujo de
reserva, la librería crea el turno con su fecha, duración y participantes, le
asigna el estado confirmado y ayuda a evitar superposiciones. La aplicación
complementa con sus reglas de disponibilidad y
vacaciones del odontólogo antes de guardar la reserva.

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
`odontologa.demo@example.test`. El seeder no reemplaza los datos existentes del
consultorio ni las contraseñas de cuentas que ya creó. Si necesitás reconstruir
una base local desde cero, podés usar `php artisan migrate:fresh --seed`; ese
comando elimina primero todas las tablas y sus datos.
