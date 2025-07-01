<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';
    protected $primaryKey = 'id';
    public $timestamps = true; // usa created_at y updated_at automáticos

    protected $fillable = [
        'user_one_id',
        'user_two_id',
    ];

    protected $casts = [
        'user_one_id' => 'integer',
        'user_two_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones

    // Usuario uno (quien tiene el menor id)
    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    // Usuario dos (quien tiene el mayor id)
    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    // Mensajes relacionados al chat
    public function messages()
    {
        return $this->hasMany(Message::class, 'chat_id');
    }
}