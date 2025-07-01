<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatViewController extends Controller
{

    protected $chatService;

    public function __construct(ChatService $chatService){
        $this->chatService = $chatService;
    }

    public function show()
    {
        $user = Auth::user();

        $chats = $this->chatService->getAllChats($user);

        return Inertia::render('ChatApp', [
            'chats' => $chats,
            'authUserId' => $user->id,
        ]);
    }

    public function getChatMessages($chatId){
        $user = Auth::user();

        $chatMessages = $this->chatService->getChatMessages($chatId, $user);

        return response()->json(['chatMessages' => $chatMessages]);
    }

    public function sendChatMessage(Request $request){
        $data = $request->validate([
            'text' => 'required|string',
            'chat_id' => 'required|integer',
        ]);

        $user = Auth::user();

        $chat = $this->chatService->saveMessageSended($data['chat_id'], $user ,$data['text'] );

        if(!$chat){
            return response()->json(['error' => 'Error al guardar el mensaje enviado'], 500);
        }

        return response()->json(['succes' => true], 201);
    }

    public function sendChatInvitation(Request $request){
        $requestData = $request->validate([
            'email' => 'required', 'email'
        ]);

        $user = Auth::user();

        $chatData = $this->chatService->sendChatInvitation($user, $requestData['email']);

        if(!$chatData){
            return response()->json(['error' => 'Error al enviar invitación de chat'], 500);
        }

        return response()->json([
            'succes' => true,
            'chat_id' => $chatData['chatId'],
            'user_name_invited' => $chatData['userNameInvited'],
        ],201 );
    }

    public function markMessagesAsRead(Request $request){

        $validateData = $request->validate([
            'message_ids' => 'required|array',
        ]);

        $messagesUnreadIds = $validateData['message_ids'];

        $user = Auth::user();

        $markedMessagesRead = $this->chatService->setChatMessagesRead($messagesUnreadIds, $user->id);

        if(!$markedMessagesRead){
            return response()->json(['error' => 'Error al marcar mensajes del chat como leídos.'], 500);
        }

        return response()->json([
            'succes' => true,
        ]);
    }

    public function deleteUsersChat ($chatId){
        $deletedChat = $this->chatService->deleteChat($chatId);

        if(!$deletedChat){
            return response()->json(['error' => 'Error al eliminar el chat.'], 500);
        }

        return response()->json([
            'succes' => true,
            'chat_id' => $chatId
        ]);
    }
}
