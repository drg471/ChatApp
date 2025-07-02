<?php

namespace App\Services;

use App\Exceptions\ChatServiceException;
use App\Repositories\ChatRepository;
use Exception;

class ChatService
{
    public function __construct(protected ChatRepository $chatRepository){}

    public function getAllChats($user)
    {
        try {
            $chats = $this->chatRepository->getAllChatsByUser($user);

            return $chats;
        } catch (Exception $e) {
            throw new ChatServiceException("Error obtener los chats del usuario.", 0, $e);
        }
    }

    public function getChatMessages($chatId, $user)
    {
        try {
            $chatMessages = $this->chatRepository->getChatMessagesByChatId($chatId);

            return $chatMessages;
        } catch (Exception $e) {
            throw new ChatServiceException("Error obtener los mensajes del chat {$chatId}: ",0, $e);
        }
    }

    public function saveSentMessage($chatId, $userSender, $message){
        try {
            $saveMessage = $this->chatRepository->saveMessage($chatId, $userSender, $message);

            return $saveMessage;
        } catch (Exception $e) {
            throw new ChatServiceException("Error guardar el mensaje enviado del chat {$chatId}: ", 0, $e);
        }
    }

    public function sendChatInvitation($user, $emailInvitation){
        try {
            $chatData  = $this->chatRepository->sendChatInvitationByEmail($user, $emailInvitation);

            return $chatData;
        } catch (Exception $e) {
            throw new ChatServiceException("Error enviar invitación a chat al usuario {$emailInvitation}: ", 0, $e);
        }
    }

    public function setChatMessagesRead($messagesUnreadIds, $userId){
        try{
            $messagesMarkAsRead = $this->chatRepository->setChatMessagesReadByMessagesUnreadIds($messagesUnreadIds, $userId);

            return $messagesMarkAsRead;
        } catch (Exception $e) {
            throw new ChatServiceException("Error marcar mensajes como leídos con id's {$messagesUnreadIds}: ", 0, $e);
        }
    }

    public function deleteChat($chatId){
        try{
            $deletedChat  = $this->chatRepository->delete($chatId);

            return $deletedChat ;
        } catch (Exception $e) {
            throw new ChatServiceException("Error al eliminar el chat {$chatId}: " , 0, $e);
        }
    }
}
