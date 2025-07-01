<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'id';
    public $timestamps = false; // solo tiene created_at, no updated_at

    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
        'created_at',
    ];

    protected $casts = [
        'chat_id' => 'integer',
        'sender_id' => 'integer',
        'message' => 'string',
        'created_at' => 'datetime',
    ];

    // Relaciones

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reads()
    {
        return $this->hasMany(MessageRead::class, 'message_id');
    }
}
