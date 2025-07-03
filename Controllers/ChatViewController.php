<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Services\ChatService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatViewController extends Controller
{
    public function __construct(protected ChatService $chatService){}

    public function index()
    {
        $user = Auth::user();

        $chats = $this->chatService->getAllChats($user);

        return Inertia::render('ChatApp', [
            'chats' => $chats,
            'authUserId' => $user->id,
        ]);
    }

    public function getChatMessages($chatId){
        $beforeId = request()->query('before_id');

        $result = $this->chatService->getChatMessages($chatId, $beforeId);

        return ApiResponse::success([
            'messages' => $result['messages'],
            'has_previous_messages' => $result['has_previous_messages'],
        ], 'Mensajes obtenidos con éxito.', 200);
    }

    public function sendChatMessage(Request $request){
        $data = $request->validate([
            'text' => 'required|string',
            'chat_id' => 'required|integer',
        ]);

        $user = Auth::user();

        $chat = $this->chatService->saveSentMessage($data['chat_id'], $user ,$data['text'] );

        if(!$chat){
            return ApiResponse::error('Error al guardar el mensaje enviado', 500);
        }

        return ApiResponse::success(null, 'Mensaje enviado correctamente', 201);
    }

    public function sendChatInvitation(Request $request){
        $requestData = $request->validate([
            'email' => 'required', 'email'
        ]);

        $user = Auth::user();

        $chatData = $this->chatService->sendChatInvitation($user, $requestData['email']);

        if(!$chatData){
            return ApiResponse::error('Error al enviar invitación de chat', 500);
        }

        return ApiResponse::success($chatData, 'Invitación enviada con éxito.', 201);
    }

    public function markMessagesAsRead(Request $request){

        $validateData = $request->validate([
            'message_ids' => 'required|array',
        ]);

        $messagesUnreadIds = $validateData['message_ids'];

        $user = Auth::user();

        $markedMessagesRead = $this->chatService->setChatMessagesRead($messagesUnreadIds, $user->id);

        if(!$markedMessagesRead){
            return ApiResponse::error('Error al marcar mensajes del chat como leídos.', 500);
        }

        return ApiResponse::success(null, 'Mensajes marcados como leídos con éxito.', 200);
    }

    public function deleteChat ($chatId){
        $deletedChat = $this->chatService->deleteChat($chatId);

        if(!$deletedChat){
            return ApiResponse::error('Error al eliminar el chat.', 500);
        }

        return ApiResponse::success($chatId, 'Chat eliminado con éxito.', 200);
    }
}
