# ChatApp
## Sistema de mensajería entre usuarios para aplicaciones web

ChatApp es un sistema de mensajería integrado en aplicaciones web que permite a los usuarios comunicarse entre sí mediante chats privados. Desarrollado con PHP y Laravel, ofrece una experiencia similar a un chat en tiempo real mediante técnicas de pulling.

## ⚙️ Tecnologías utilizadas

- **Lenguaje:** PHP  
- **Framework:** Laravel  
- **Base de datos:** MySQL  
- **Frontend:** Vue 3 con Inertia.js  
- **Técnica de actualización:** Pulling cada 1.5 segundos  

<img src="https://github.com/drg471/ChatApp/blob/screenshots/Imagen10.png" width="600" />

## ✨ Características principales

- 💌 **Inicio de chats por invitación** mediante correo electrónico  
- 🔄 **Actualización automática** mediante pulling (consulta cada 1.5 segundos)  
- 🔔 **Notificación de mensajes no leídos** con contador visible  
- 🗑️ **Eliminación de chats** completos  
- 📱 **Diseño responsive** adaptable a diferentes dispositivos  

## 🖼️ Flujo de trabajo

### 1. Iniciar un nuevo chat

Los usuarios pueden iniciar conversaciones introduciendo el correo electrónico del destinatario.

<img src="https://github.com/tuusuario/ChatApp/blob/main/screenshots/Imagen11.png" width="400" />

---

### 2. Lista de chats con mensajes no leídos

La interfaz muestra todos los chats activos con un indicador de mensajes no leídos.

<img src="https://github.com/tuusuario/ChatApp/blob/main/screenshots/chat-list.png" width="400" />

---

### 3. Interfaz de mensajería

Área de conversación con historial de mensajes y campo para enviar nuevos.

<img src="https://github.com/tuusuario/ChatApp/blob/main/screenshots/message-interface.png" width="600" />

---

### 4. Eliminar chats

Los usuarios pueden eliminar conversaciones completas cuando lo deseen.

<img src="https://github.com/tuusuario/ChatApp/blob/main/screenshots/delete-chat.png" width="400" />

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
