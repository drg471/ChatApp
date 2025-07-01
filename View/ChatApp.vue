<template>
    <v-container fluid>
        <v-row no-gutters>
            <v-col cols="4" class="pa-2" style="border-right: 1px solid #ccc">
                <!-- Botón Invitar -->
                <v-row justify="center">
                    <v-col cols="4">
                        <v-btn
                            color="primary"
                            block
                            class=""
                            @click="showInviteDialog = true"
                            prepend-icon="mdi-account-plus"
                        >
                            Invitar
                        </v-btn>
                    </v-col>
                </v-row>

                <!-- Lista de chats -->
                <v-list dense nav>
                    <v-list-subheader class="text-secondary mt-2 mb-2"
                        >Chats</v-list-subheader
                    >

                    <v-list-item
                        v-for="chat in chats"
                        :key="chat.id"
                        @click="selectChat(chat)"
                        :class="{
                            'bg-blue-lighten-5':
                                currentChat && currentChat.id === chat.id,
                            border: true,
                            'border-grey-lighten-1': true,
                        }"
                    >
                        <v-list-item-content>
                            <div
                                class="d-flex align-center justify-space-between"
                            >
                                <!-- Izquierda: avatar + nombre -->
                                <div class="d-flex align-center">
                                    <v-avatar
                                        color="primary"
                                        size="18"
                                        class="mr-2"
                                        style="
                                            font-size: 12px;
                                            line-height: 18px;
                                        "
                                    >
                                        {{ chat.name.charAt(0) }}
                                    </v-avatar>
                                    <v-list-item-title>{{
                                        chat.name
                                    }}</v-list-item-title>
                                </div>

                                <!-- Derecha: badge + botón borrar -->
                                <div class="d-flex align-center">
                                    <v-badge
                                        v-if="chat.unread > 0"
                                        :content="chat.unread"
                                        color="red"
                                        bordered
                                        :inline="true"
                                        style="line-height: 1"
                                        class="mr-2"
                                    />
                                    <v-btn
                                        icon
                                        size="small"
                                        variant="text"
                                        color="grey"
                                        class="custom-delete-btn"
                                        @click.stop="confirmDeleteChat(chat)"
                                    >
                                        <v-icon size="20">mdi-delete</v-icon>
                                    </v-btn>
                                </div>
                            </div>
                        </v-list-item-content>
                    </v-list-item>
                </v-list>
            </v-col>

            <!-- Área del chat seleccionado -->
            <v-col cols="8" class="pa-8">
                <div
                    v-if="loadingChat"
                    class="d-flex justify-center align-center"
                    style="height: 550px"
                >
                    <v-progress-circular
                        indeterminate
                        color="primary"
                        size="50"
                    />
                </div>

                <div
                    v-else-if="currentChat"
                    style="display: flex; flex-direction: column; height: 550px"
                >
                    <div
                        class="d-flex align-center bg-grey darken-1"
                        style="
                            border: 1px solid #ccc;
                            padding: 8px;
                            border-radius: 4px;
                        "
                    >
                        <v-avatar color="primary" size="30" class="mr-2">
                            {{ currentChat.name.charAt(0) }}
                        </v-avatar>
                        <h3
                            style="
                                display: inline-block;
                                vertical-align: middle;
                            "
                        >
                            {{ currentChat.name }}
                        </h3>
                    </div>

                    <div
                        ref="messagesContainer"
                        class="chat-messages"
                        style="
                            height: 450px;
                            overflow-y: auto;
                            margin-bottom: 8px;
                            border: 1px solid #ddd;
                            padding: 10px;
                            border-radius: 4px;
                            background: #fafafa;
                        "
                    >
                        <!-- Contenedor principal de mensajes (sin flex) -->
                        <div
                            v-for="(msg, index) in messages"
                            :key="msg.id"
                            class="mb-4"
                        >
                            <!-- Divider de mensajes no leídos (ahora fuera del contenedor flex) -->
                            <div
                                v-if="shouldShowUnreadDiv(msg, index)"
                                class="unread-divider"
                                style="width: 100%; margin-bottom: 16px"
                            >
                                <v-divider class="my-2"></v-divider>
                                <div class="text-center text-caption text-grey">
                                    Mensajes no leídos
                                </div>
                            </div>

                            <!-- Contenedor flex para alineación del mensaje -->
                            <div
                                class="d-flex"
                                :class="
                                    msg.sender_id === authUserId
                                        ? 'justify-end'
                                        : 'justify-start'
                                "
                            >
                                <!-- Burbuja de mensaje -->
                                <div
                                    class="d-flex flex-column"
                                    style="max-width: 75%"
                                >
                                    <v-sheet
                                        :color="
                                            msg.sender_id === authUserId
                                                ? 'blue-lighten-4'
                                                : 'green-lighten-4'
                                        "
                                        class="pa-3 rounded-lg"
                                    >
                                        <span class="black--text">{{
                                            msg.message
                                        }}</span>
                                    </v-sheet>
                                    <div
                                        class="text-caption text-secondary mt-1"
                                        :class="
                                            msg.sender_id === authUserId
                                                ? 'text-right'
                                                : 'text-left'
                                        "
                                    >
                                        {{ msg.created_at }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-center">
                        <v-text-field
                            v-model="newMessage"
                            label="Escribe un mensaje"
                            @keyup.enter="sendMessage"
                            append-inner-icon="mdi-send"
                            @click:append-inner="sendMessage"
                            hide-details
                            outlined
                            dense
                            class="message-input"
                            style="margin-right: 0px"
                        />
                    </div>
                </div>

                <v-alert
                    v-else
                    color="light-grey"
                    class="d-flex align-center justify-center"
                    style="height: 550px; width: 100%"
                >
                    <div
                        class="d-flex align-center"
                        style="
                            position: absolute;
                            left: 50%;
                            transform: translateX(-50%);
                        "
                    >
                        <v-icon class="mr-2">mdi-message-text</v-icon>
                        <span>Selecciona un chat</span>
                    </div>
                </v-alert>
            </v-col>
        </v-row>

        <!-- MODAL INVITAR -->
        <v-dialog v-model="showInviteDialog" max-width="400">
            <v-card>
                <v-card-title>Invitar usuario a nuevo chat</v-card-title>
                <v-card-text>
                    <v-text-field
                        v-model="inviteEmail"
                        label="Correo electrónico"
                        type="email"
                        required
                    />
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn text @click="showInviteDialog = false" color="grey"
                        >Cancelar</v-btn
                    >
                    <v-btn color="primary" @click="sendInvitation"
                        >Enviar</v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Diálogo de confirmación para eliminar chat -->
        <v-dialog v-model="showDeleteDialog" max-width="400">
            <v-card>
                <v-card-title>Eliminar chat</v-card-title>
                <v-card-text>
                    ¿Estás seguro de que quieres eliminar este chat? Esta acción
                    no se puede deshacer.
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn text @click="showDeleteDialog = false" color="grey"
                        >Cancelar</v-btn
                    >
                    <v-btn color="error" @click="deleteChat">Eliminar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>

    <!--MENSAJE INFO-->
    <v-snackbar v-model="snackbarSucces" :timeout="3000" color="green">{{
        infoMsgSucces
    }}</v-snackbar>
    <!--MENSAJE INFO-->
    <v-snackbar v-model="snackbarError" :timeout="3000" color="error">{{
        infoMsgError
    }}</v-snackbar>
</template>

<script>
import axios from "axios";
import AuthLayout from "../Layouts/AuthLayout.vue";

export default {
    layout: AuthLayout,
    props: {
        chats: {
            type: Array,
            required: true,
        },
        authUserId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            currentChat: null,
            messages: [],
            newMessage: "",
            pollingInterval: null,
            firstUnreadMessageId: null,
            showInviteDialog: false,
            inviteEmail: "",
            showDeleteDialog: false,
            chatToDelete: null,
            loadingChat: false,
            snackbarSucces: false,
            snackbarError: false,
            infoMsgSucces: "",
            infoMsgError: "",
        };
    },
    methods: {
        selectChat(chat) {
            this.loadingChat = true;
            this.currentChat = chat;

            // Guarda el ID del primer mensaje no leído ANTES de marcarlos como leídos
            if (chat.unread_ids?.length > 0) {
                this.firstUnreadMessageId = chat.unread_ids[0];
            } else {
                this.firstUnreadMessageId = null;
            }

            this.fetchMessages();

            // Marcar como leídos los mensajes no leídos
            if (chat.unread_ids && chat.unread_ids.length > 0) {
                axios
                    .post("/chat/mark-messages-read", {
                        message_ids: chat.unread_ids,
                    })
                    .then(() => {
                        chat.unread = 0;
                        chat.unread_ids = [];
                    })
                    .catch((err) => {
                        console.error(
                            "Error al marcar mensajes como leídos:",
                            err
                        );
                        this.infoMsgError =
                            "Error al marcar mensajes como leídos";
                        this.snackbarError = true;
                    });
            }

            this.loadingChat = false;

            // Reiniciar polling
            if (this.pollingInterval) clearInterval(this.pollingInterval);

            this.pollingInterval = setInterval(this.fetchMessages, 1500); // cada 1,5s
        },
        fetchMessages() {
            console.log("fetch messages...");

            if (!this.currentChat) return;

            const wasAtBottom = this.isScrolledToBottom();

            axios.get(`/chat/${this.currentChat.id}/messages`).then((res) => {
                this.messages = res.data.chatMessages;

                this.$nextTick(() => {
                    if (wasAtBottom) {
                        this.scrollToBottom();
                    }
                });
            });
        },
        sendMessage() {
            if (!this.newMessage.trim() || !this.currentChat) return;

            // Guarda el mensaje temporalmente
            const tempMessage = {
                id: Date.now(), // ID temporal
                message: this.newMessage,
                sender_id: this.authUserId,
                created_at: new Date().toLocaleTimeString(),
            };

            // Agrega el mensaje localmente (sin esperar al servidor)
            this.messages.push(tempMessage);
            this.newMessage = ""; // Limpia el campo
            this.scrollToBottom();

            axios
                .post(`/chat/send-message`, {
                    text: tempMessage.message,
                    chat_id: this.currentChat.id,
                })
                .then(() => {
                    this.fetchMessages(); // Sincroniza con el servidor
                })
                .catch((err) => {
                    console.error("Error al enviar mensaje: ", err);
                    this.infoMsgError = "Hubo un error al enviar mensaje";
                    this.snackbarError = true;
                    // Opcional: Remover el mensaje temporal si falla
                    this.messages = this.messages.filter(
                        (m) => m.id !== tempMessage.id
                    );
                });
        },
        sendInvitation() {
            if (!this.inviteEmail.trim()) return;

            axios
                .post("/chat/invite", { email: this.inviteEmail })
                .then((res) => {
                    this.showInviteDialog = false;
                    this.inviteEmail = "";

                    const newChat = {
                        id: res.data.chat_id,
                        name: res.data.user_name_invited,
                        unread: 0,
                    };

                    this.chats.push(newChat);
                    this.selectChat(newChat);

                    this.infoMsgSucces = "Invitación enviada correctamente";
                    this.snackbarSucces = true;
                })
                .catch((err) => {
                    console.error("Error al enviar invitación: ", err);
                    this.infoMsgError = "Hubo un error al enviar la invitación";
                    this.snackbarError = true;
                });
        },

        isScrolledToBottom() {
            const container = this.$refs.messagesContainer;
            if (!container) return true;

            return (
                container.scrollHeight - container.scrollTop <=
                container.clientHeight + 50
            );
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        confirmDeleteChat(chat) {
            this.chatToDelete = chat;
            this.showDeleteDialog = true;
        },
        deleteChat() {
            if (!this.chatToDelete) return;

            axios
                .delete(`/chat/${this.chatToDelete.id}/delete`)
                .then(() => {
                    const index = this.chats.findIndex(
                        (c) => c.id === this.chatToDelete.id
                    );
                    if (index !== -1) {
                        this.chats.splice(index, 1);
                    }

                    if (
                        this.currentChat &&
                        this.currentChat.id === this.chatToDelete.id
                    ) {
                        this.currentChat = null;
                        this.messages = [];
                        clearInterval(this.pollingInterval);
                    }

                    this.showDeleteDialog = false;
                    this.chatToDelete = null;

                    this.infoMsgSucces = "Chat eliminado correctamente";
                    this.snackbarSucces = true;
                })
                .catch((err) => {
                    console.error("Error al eliminar chat: ", err);
                    this.infoMsgError = "Hubo un error al eliminar el chat";
                    this.snackbarError = true;
                });
        },

        shouldShowUnreadDiv(msg, index) {
            return (
                this.firstUnreadMessageId !== null &&
                msg.id === this.firstUnreadMessageId
            );
        },
    },
    beforeUnmount() {
        if (this.pollingInterval) clearInterval(this.pollingInterval);
    },
};
</script>

<style scoped>
/* Asegura que los mensajes largos se ajusten */
.v-sheet {
    word-wrap: break-word;
    white-space: pre-wrap;
}

/* Color para tus mensajes */
.messageUser {
    background-color: #c6fa9f !important;
}

/* Color para mensajes del otro */
.messageOther {
    background-color: #cdf7ff !important;
}

.unread-divider {
    width: 100%;
    margin-bottom: 8px;
}

.custom-delete-btn:hover {
    background-color: #575757 !important;
}

.custom-file-input:hover {
    background-color: #636363;
    color: white;
    border-radius: 100%;
}
</style>
