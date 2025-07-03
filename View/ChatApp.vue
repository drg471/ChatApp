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
                        <div
                            class="d-flex align-center justify-space-between"
                            style="width: 100%"
                        >
                            <!-- Izquierda: avatar + nombre -->
                            <div class="d-flex align-center">
                                <v-avatar
                                    color="primary"
                                    size="22"
                                    class="mr-2"
                                    style="font-size: 14px;"
                                >
                                    {{ chat.name.charAt(0) }}
                                </v-avatar>
                                <v-list-item-title style="font-size: 14px;">{{
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

                <!-- VENTANA DE CHAT -->
                <div
                    v-else-if="currentChat"
                    style="display: flex; flex-direction: column; height: 550px"
                >
                    <!-- cabecera de chat -->
                    <div
                        class="d-flex align-center bg-grey darken-1  py-4 px-2"
                        style="
                            border: 1px solid #9e9e9e;
                            border-bottom: none;
                            border-radius: 8px 8px 0 0;
                            padding: 8px;
                        "
                    >
                        <v-avatar color="primary" size="35" class="mr-2">
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
                            border: 1px solid #ddd;
                            padding: 10px;
                            border-top: none;
                            background: #fafafa;
                        "
                    >
                        <div
                            v-if="isLoadingMore"
                            class="loading-messages text-center py-4"
                            style="width: 100%"
                        >
                            <v-progress-circular
                                indeterminate
                                color="primary"
                                size="24"
                                width="2"
                                class="mr-2"
                            ></v-progress-circular>
                            <span class="text-caption text-grey"
                                >Cargando mensajes...</span
                            >
                        </div>
                        <div
                            v-if="!hasMoreMessages && messages.length > 0"
                            class="text-center py-2 text-caption text-grey"
                        >
                            <div>Inicio de la conversación</div>
                            <v-divider></v-divider>
                        </div>

                        <!-- Contenedor principal de mensajes (sin flex) -->
                        <div
                            v-for="(msg, index) in messages"
                            :key="msg.id"
                            :id="'msg-' + msg.id"
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
                                    :style="
                                        msg.sender_id === authUserId
                                            ? 'align-items: flex-end'
                                            : 'align-items: flex-start'
                                    "
                                >
                                    <v-sheet
                                        :color="
                                            msg.sender_id === authUserId
                                                ? 'blue-lighten-4'
                                                : 'green-lighten-4'
                                        "
                                        class="pa-3 rounded-lg"
                                        :style="{
                                            'white-space': 'pre-wrap',
                                            'margin-left':
                                                msg.sender_id === authUserId
                                                    ? '60px'
                                                    : '0',
                                            'margin-right':
                                                msg.sender_id !== authUserId
                                                    ? '60px'
                                                    : '0',
                                        }"
                                    >
                                        <span
                                            class="black--text"
                                            style="word-break: keep-all"
                                            >{{ msg.message }}</span
                                        >
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

                    <!-- Caja introducir mensaje -->
                    <div class="d-flex align-center">
                        <v-btn
                            color="secondary"
                            style="
                                height: 56px;
                                min-width: 60px;
                                padding: 0;
                                border-radius: 0 0 0 8px;
                            "
                            @click="toggleEmojiPicker"
                        >
                            <v-icon size="28">{{
                                showEmojiPicker
                                    ? "mdi-close"
                                    : "mdi-emoticon-happy-outline"
                            }}</v-icon>
                        </v-btn>

                        <v-text-field
                            v-model="newMessage"
                            label="Escribe un mensaje"
                            @keyup.enter="sendMessage"
                            append-inner-icon="mdi-send"
                            @click:append-inner="sendMessage"
                            hide-details
                            outlined
                            dense
                            style="flex-grow: 1"
                            ref="messageInput"
                            class="message-input"
                        />

                        <div
                            v-if="showEmojiPicker"
                            class="emoji-picker-wrapper"
                            ref="emojiPickerWrapper"
                        >
                            <emoji-picker
                                class="emoji-picker"
                                @emoji-click="onSelectEmoji"
                            ></emoji-picker>
                        </div>
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
            showEmojiPicker: false,
            chatToDelete: null,
            loadingChat: false,
            isLoadingMore: false,
            hasMoreMessages: true,
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
            this.hasMoreMessages = true;
            this.messages = [];

            if (chat.unread_ids?.length > 0) {
                this.firstUnreadMessageId = chat.unread_ids[0];
            } else {
                this.firstUnreadMessageId = null;
            }

            this.fetchMessages().then(() => {
                this.setupScrollListener();
                this.loadingChat = false;

                if (this.pollingInterval) clearInterval(this.pollingInterval);
                this.pollingInterval = setInterval(() => {
                    if (!this.isLoadingMore && this.isScrolledToBottom()) {
                        this.fetchMessages();
                    }
                }, 1500);
            });

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

            if (this.pollingInterval) clearInterval(this.pollingInterval);

            this.pollingInterval = setInterval(this.fetchMessages, 1500); // cada 1,5s
        },
        setupScrollListener() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.removeEventListener("scroll", this.handleScroll);
                    container.addEventListener("scroll", this.handleScroll);
                    console.log("Scroll listener setup complete");
                }
            });
        },
        async fetchMessages(beforeId = null) {
            console.log("fetching messages...");
            if (!this.currentChat) return;

            const container = this.$refs.messagesContainer;
            const prevScrollInfo = beforeId
                ? {
                      top: container?.scrollTop,
                      height: container?.scrollHeight,
                  }
                : null;

            const url = beforeId
                ? `/chat/${this.currentChat.id}/messages?before_id=${beforeId}`
                : `/chat/${this.currentChat.id}/messages`;

            try {
                const res = await axios.get(url);
                const newMessages = res.data.data.messages;
                this.hasMoreMessages = res.data.data.has_previous_messages;

                if (beforeId) {
                    const firstMessageIdBefore = this.messages[0]?.id;
                    this.messages = [...newMessages, ...this.messages];
                    this.hasMoreMessages = newMessages.length >= 20;

                    this.$nextTick(() => {
                        if (container && firstMessageIdBefore) {
                            const messageElement = container.querySelector(
                                `#msg-${firstMessageIdBefore}`
                            );
                            if (messageElement) {
                                container.scrollTop =
                                    messageElement.offsetTop - 20;
                            }
                        }
                    });
                } else {
                    this.messages = newMessages;
                    if (this.isScrolledToBottom()) {
                        this.scrollToBottom();
                    }
                }

                return newMessages;
            } catch (err) {
                console.error("Error fetching messages:", err);
                this.hasMoreMessages = false;
                this.isLoadingMore = false;
                return [];
            } finally {
                this.isLoadingMore = false;
            }
        },

        handleScroll() {
            const container = this.$refs.messagesContainer;
            if (!container || this.isLoadingMore || !this.hasMoreMessages)
                return;

            // Umbral scroll (100px) para verificar que hay mensajes
            const scrollThreshold = 100;
            if (
                container.scrollTop < scrollThreshold &&
                this.messages.length > 0
            ) {
                this.loadMoreMessages();
            }
        },

        loadMoreMessages() {
            if (
                this.isLoadingMore ||
                !this.hasMoreMessages ||
                this.messages.length === 0
            ) {
                return;
            }

            this.isLoadingMore = true;
            const oldestMessageId = this.messages[0].id;
            this.fetchMessages(oldestMessageId);
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

                    const responseData = res.data.data;

                    const newChat = {
                        id: responseData.chatId,
                        name: responseData.userNameInvited,
                        unread: 0,
                    };

                    this.chats.push(newChat);
                    this.selectChat(newChat);

                    this.infoMsgSucces =
                        res.data.message || "Invitación enviada correctamente";
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

            console.log("this.chatToDelete.id: ", this.chatToDelete.id);

            axios
                .delete(`/chat/${this.chatToDelete.id}/delete`)
                .then((res) => {
                    const deletedChatId = res.data.data;

                    if (
                        this.currentChat &&
                        this.currentChat.id == deletedChatId
                    ) {
                        this.currentChat = null;
                        this.messages = [];
                        if (this.pollingInterval) {
                            clearInterval(this.pollingInterval);
                            this.pollingInterval = null;
                        }
                    }

                    const index = this.chats.findIndex(
                        (c) => c.id == deletedChatId
                    );
                    if (index !== -1) {
                        this.chats.splice(index, 1);
                    }

                    this.showDeleteDialog = false;
                    this.chatToDelete = null;

                    this.infoMsgSucces =
                        res.data.message || "Chat eliminado correctamente";
                    this.snackbarSucces = true;
                })
                .catch((err) => {
                    console.error("Error al eliminar chat: ", err);
                    this.infoMsgError =
                        err.response?.data?.message ||
                        "Hubo un error al eliminar el chat";
                    this.snackbarError = true;
                });
        },

        shouldShowUnreadDiv(msg, index) {
            return (
                this.firstUnreadMessageId !== null &&
                msg.id === this.firstUnreadMessageId
            );
        },

        onSelectEmoji(event) {
            this.newMessage += event.detail.unicode;
            this.showEmojiPicker = false;
            this.$nextTick(() => {
                this.$refs.messageInput.focus();
            });
        },

        toggleEmojiPicker() {
            this.showEmojiPicker = !this.showEmojiPicker;
        },
    },
    mounted() {
        const container = this.$refs.messagesContainer;
        if (container) {
            container.addEventListener("scroll", this.handleScroll);
        }

        import("emoji-picker-element")
            .then(() => {
                console.log("Emoji Picker cargado correctamente");
            })
            .catch((err) => {
                console.error("Error al cargar Emoji Picker:", err);
            });
    },
    beforeUnmount() {
        const container = this.$refs.messagesContainer;
        if (container) {
            container.removeEventListener("scroll", this.handleScroll);
        }
        if (this.pollingInterval) clearInterval(this.pollingInterval);
    },
};
</script>

<style scoped>
.v-sheet {
    word-wrap: break-word;
    white-space: pre-wrap;
}

.messageUser {
    background-color: #c6fa9f !important;
}

.messageOther {
    background-color: #cdf7ff !important;
}

.unread-divider {
    width: 100%;
    margin-bottom: 8px;
}

.message-input ::v-deep(.v-field) {
    border-radius: 0 0 8px 0 !important;
}

.custom-delete-btn:hover {
    background-color: #575757 !important;
}

.custom-file-input:hover {
    background-color: #636363;
    color: white;
    border-radius: 100%;
}

.emoji-picker-wrapper {
    position: absolute;
    left: 1000;
    top: 200px;
}

.emoji-picker {
    width: 350px;
    height: 400px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    --emoji-size: 2rem;
    --num-columns: 6;
    --category-emoji-size: 1.3rem;
    --background: #e7e7e7;
    --border-color: #b6b6b6;
}

.v-sheet span {
    font-size: 16px;
}

.v-sheet span img.emoji {
    height: 1.5em !important;
    width: 1.5em !important;
    vertical-align: middle;
}

.v-sheet span img.emoji {
    height: 3em !important; /* Aumenta el tamaño de los emojis */
    width: 3em !important;
    vertical-align: middle;
    margin: 0 2px; /* Espaciado opcional alrededor de los emojis */
}
</style>
