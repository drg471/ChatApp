# ChatApp
## Sistema de mensajería entre usuarios para aplicaciones web

ChatApp es un sistema de mensajería integrado en aplicaciones web que permite a los usuarios comunicarse entre sí mediante chats privados. Desarrollado con PHP y Laravel, ofrece una experiencia similar a un chat en tiempo real mediante técnicas de pulling.

## ⚙️ Tecnologías utilizadas

- **Lenguaje:** PHP  
- **Framework:** Laravel  
- **Base de datos:** MySQL  
- **Frontend:** Vue 3 con Inertia.js  
- **Técnica de actualización:** Pulling cada 1.5 segundos  

<table>
  <tr>
    <td><img src="https://github.com/drg471/ChatApp/blob/screenshots/Imagen10.png" width="500" /></td>
    <td><img src="https://github.com/drg471/ChatApp/blob/screenshots/Imagen111.png" width="500" /></td>
  </tr>
</table>

## ✨ Características principales

- 💌 **Inicio de chats por invitación** mediante correo electrónico  
- 🔄 **Actualización automática** mediante pulling (consulta cada 1.5 segundos)  
- 🔔 **Notificación de mensajes no leídos** con contador visible  
- 🗑️ **Eliminación de chats** completos  

## 🖼️ Flujo de trabajo

### 1. Iniciar un nuevo chat

Los usuarios pueden iniciar conversaciones introduciendo el correo electrónico del destinatario.

<img src="https://github.com/drg471/ChatApp/blob/screenshots/Imagen12.png" width="600" />
<img src="https://github.com/drg471/ChatApp/blob/screenshots/Imagen13.png" width="600" />

---

### 2. Lista de chats con mensajes no leídos

La interfaz muestra todos los chats activos con un indicador de mensajes no leídos.

<img src="https://github.com/drg471/ChatApp/blob/screenshots/Imagen11.png" width="600" />

---

### 3. Eliminar chats

Los usuarios pueden eliminar conversaciones completas cuando lo deseen.

<img src="https://github.com/drg471/ChatApp/blob/screenshots/Imagen14.png" width="600" />

---

## 🛠️ Estructura técnica

El sistema funciona mediante:

1. **Pulling periódico**: Consulta al servidor cada 1.5 segundos para detectar nuevos mensajes
2. **Gestión de estados**:
   - Mensajes leídos/no leídos
   - Listado de conversaciones activas
3. **Invitaciones por email**: Validación de usuarios existentes

```php
// Ejemplo de controlador básico
class ChatController extends Controller {
    public function checkNewMessages(Request $request) {
        // Lógica para verificar mensajes nuevos
        return response()->json(['newMessages' => $messages]);
    }
}
