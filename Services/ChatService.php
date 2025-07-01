<?php

namespace App\Services;

use App\Repositories\ChatRepository;
use Exception;

class ChatService
{
    protected $chatRepository;

    public function __construct(ChatRepository $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function getAllChats($user)
    {
        try {
            $chats = $this->chatRepository->getAllChatsByUser($user);

            return $chats;
        } catch (Exception $e) {
            throw new Exception("Error obtener los chats del usuario.");
        }
    }

    public function getChatMessages($chatId, $user)
    {
        try {
            $chatMessages = $this->chatRepository->getChatMessagesByChatId($chatId);

            return $chatMessages;
        } catch (Exception $e) {
            throw new Exception("Error obtener los mensajes del chat {$chatId}: " . $e->getMessage());
        }
    }

    public function saveMessageSended($chatId, $userSender, $message){
        try {
            $saveMessage = $this->chatRepository->saveMessage($chatId, $userSender, $message);

            return $saveMessage;
        } catch (Exception $e) {
            throw new Exception("Error guardar el mensaje enviado del chat {$chatId}: " . $e->getMessage());
        }
    }

    public function sendChatInvitation($user, $emailInvitation){
        try {
            $chatData  = $this->chatRepository->sendChatInvitationByEmail($user, $emailInvitation);

            return $chatData;
        } catch (Exception $e) {
            throw new Exception("Error enviar invitación a chat al usuario {$emailInvitation}: " . $e->getMessage());
        }
    }

    public function setChatMessagesRead($messagesUnreadIds, $userId){
        try{
            $messagesMarkAsRead = $this->chatRepository->setChatMessagesReadByMessagesUnreadIds($messagesUnreadIds, $userId);

            return $messagesMarkAsRead;
        } catch (Exception $e) {
            throw new Exception("Error marcar mensajes como leídos con id's {$messagesUnreadIds}: " . $e->getMessage());
        }
    }

    public function deleteChat($chatId){
        try{
            $deletedChat  = $this->chatRepository->delete($chatId);

            return $deletedChat ;
        } catch (Exception $e) {
            throw new Exception("Error al eliminar el chat {$chatId}: " . $e->getMessage());
        }
    }
}
