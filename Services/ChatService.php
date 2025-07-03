<?php

namespace App\Services;

use App\Exceptions\ChatServiceException;
use App\Repositories\ChatRepository;
use Exception;
use Illuminate\Support\Facades\Crypt;

class ChatService
{
    public function __construct(protected ChatRepository $chatRepository) {}

    public function getAllChats($user)
    {
        try {
            $chats = $this->chatRepository->getAllChatsByUser($user);

            return $chats;
        } catch (Exception $e) {
            throw new ChatServiceException("Error obtener los chats del usuario.", 0, $e);
        }
    }

    public function getChatMessages($chatId, $beforeId)
    {
        try {
            $result = $this->chatRepository->getLastChatMessagesByChatId($chatId, $beforeId);

            $chatMessagesDecrypted = [];

            foreach($result['messages'] as $messageEncrypted){

                $chatMessagesDecrypted [] = [
                    'id' => $messageEncrypted['id'],
                    'chat_id' => $messageEncrypted['chat_id'],
                    'sender_id' => $messageEncrypted['sender_id'],
                    'message' => Crypt::decryptString($messageEncrypted['message']),
                    'created_at' => $messageEncrypted['created_at'],
                ];
            }

            return [
                'messages' => $chatMessagesDecrypted, 
                'has_previous_messages' => $result['has_previous_messages'],
            ];
        } catch (Exception $e) {
            throw new ChatServiceException("Error obtener los mensajes del chat {$chatId}: ", 0, $e);
        }
    }

    public function saveSentMessage($chatId, $userSender, $message)
    {
        try {
            $messageEncrypted = Crypt::encryptString($message);

            $saveMessage = $this->chatRepository->saveMessage($chatId, $userSender, $messageEncrypted);

            return $saveMessage;
        } catch (Exception $e) {
            throw new ChatServiceException("Error guardar el mensaje enviado del chat {$chatId}: ", 0, $e);
        }
    }

    public function sendChatInvitation($user, $emailInvitation)
    {
        try {
            $chatData  = $this->chatRepository->sendChatInvitationByEmail($user, $emailInvitation);

            return $chatData;
        } catch (Exception $e) {
            throw new ChatServiceException("Error enviar invitación a chat al usuario {$emailInvitation}: ", 0, $e);
        }
    }

    public function setChatMessagesRead($messagesUnreadIds, $userId)
    {
        try {
            $messagesMarkAsRead = $this->chatRepository->setChatMessagesReadByMessagesUnreadIds($messagesUnreadIds, $userId);

            return $messagesMarkAsRead;
        } catch (Exception $e) {
            throw new ChatServiceException("Error marcar mensajes como leídos con id's {$messagesUnreadIds}: ", 0, $e);
        }
    }

    public function deleteChat($chatId)
    {
        try {
            $deletedChat  = $this->chatRepository->delete($chatId);

            return $deletedChat;
        } catch (Exception $e) {
            throw new ChatServiceException("Error al eliminar el chat {$chatId}: ", 0, $e);
        }
    }
}
