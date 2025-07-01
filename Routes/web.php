<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatViewController;


//______________________________________________________________________________________________________________________________________________________
// CHAT
Route::get('/chat', [ChatViewController::class, 'show'])->name('chat');
Route::get('/chat/{chatId}/messages', [ChatViewController::class, 'getChatMessages'])->name('chat.getChatMessages');
Route::post('/chat/send-message', [ChatViewController::class, 'sendChatMessage'])->name('chat.sendChatMessage');
Route::post('/chat/invite', [ChatViewController::class, 'sendChatInvitation'])->name('chat.sendChatInvitation');
Route::delete('/chat/{chatId}/delete', [ChatViewController::class, 'deleteUsersChat'])->name('chat.deleteUsersChat');
Route::post('/chat/mark-messages-read', [ChatViewController::class, 'markMessagesAsRead'])->name('chat.markMessagesAsRead');
