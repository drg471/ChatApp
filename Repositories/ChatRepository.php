<?php

namespace App\Repositories;

use App\Models\Chat;
use App\Models\ChatInvitation;
use App\Models\MessageRead;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ChatRepository extends BaseRepository
{
    protected $model;

    public function __construct(Chat $chat)
    {
        $this->model = $chat;
    }

    public function getAllChatsByUser($user)
    {
        // Traemos los chats donde es user_one o user_two y cargamos mensajes y usuarios
        $chats = $this->model->where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with([
                'messages' => function ($query) {
                    $query->orderBy('created_at', 'asc');
                },
                'userOne:id,name',
                'userTwo:id,name',
            ])
            ->get()
            ->map(function ($chat) use ($user) {
                // Creamos un nombre para mostrar del otro usuario
                $otherUser = $chat->user_one_id === $user->id ? $chat->userTwo : $chat->userOne;

                // Último mensaje
                $lastMessage = $chat->messages->last();

                // ID de los mensajes de este chat que NO envió el usuario actual
                $messageIds = $chat->messages
                    ->where('sender_id', '!=', $user->id)
                    ->pluck('id');

                // IDs de mensajes que YA leyó este usuario
                $readMessageIds = MessageRead::where('user_id', $user->id)
                    ->whereIn('message_id', $messageIds)
                    ->pluck('message_id');

                // Total de mensajes sin leer
                $unread = $messageIds->diff($readMessageIds)->count();

                return [
                    'id' => $chat->id,
                    'name' => $otherUser->name,
                    'last_message' => $lastMessage ? $lastMessage->message : '',
                    'unread' => $unread,
                    'unread_ids' => $messageIds->diff($readMessageIds)->values(),
                ];
            });

        return $chats;
    }

    public function getChatMessagesByChatId($chatId)
    {
        $chat = $this->model->with([
            'messages' => function ($query) {
                $query->orderBy('created_at', 'asc')
                    ->with('sender:id,name');
            }
        ])->findOrFail($chatId);


        $messages = $chat->messages->toArray();

        foreach ($messages as &$msg) {
            $msg['created_at'] = (new \Carbon\Carbon($msg['created_at']))->format('H:i d-m-Y');
        }

        return $messages;
    }

    public function saveMessage($chatId, $userSender, $message)
    {
        $chat = $this->model->findOrFail($chatId);

        return $chat->messages()->create([
            'sender_id' => $userSender->id,
            'message' => $message,
        ]);
    }

    public function sendChatInvitationByEmail($userInviter, $emailInvitation)
    {
        $userInvited = User::where('email', $emailInvitation)->first();

        if ($userInvited && $userInvited->id == $userInviter->id) {
            throw new Exception("No puedes invitarte a ti mismo.");
        }

        if (!$userInvited) {
            throw new Exception("Usuario a invitar no encontrado.");
        }

        $userOneId = min($userInviter->id, $userInvited->id);
        $userTwoId = max($userInviter->id, $userInvited->id);

        $existingChat = $this->model->where('user_one_id', $userOneId)->where('user_two_id', $userTwoId)->first();

        if (!$existingChat) {
            $chat = Chat::create([
                'user_one_id' => $userOneId,
                'user_two_id' => $userTwoId,
            ]);
        } else {
            $chat = $existingChat;
        }

        $chatId = $chat->id;

        ChatInvitation::create([
            'inviter_id'    => $userInviter->id,
            'invitee_email' => $emailInvitation,
            'chat_id'       => $chatId,
            'accepted'      => 0,
        ]);

        return [
            'chatId' => $chatId,
            'userNameInvited' => $userInvited->name,
        ];
    }

    public function setChatMessagesReadByMessagesUnreadIds($messagesUnreadIds, $userId){
        $now = now();
        $rows = [];

        foreach($messagesUnreadIds as $messageId){
            $rows = [
                'message_id' => $messageId,
                'user_id'    => $userId,
                'read_at'    => $now,
            ];

            DB::table('message_reads')->insertOrIgnore($rows);
        }

        return true;
    }
}
