<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatInvitation extends Model
{
    protected $table = 'chat_invitations';
    protected $primaryKey = 'id';
    public $timestamps = false; // solo created_at no updated_at

    protected $fillable = [
        'inviter_id',
        'invitee_email',
        'chat_id',
        'accepted',
        'created_at',
    ];

    protected $casts = [
        'inviter_id' => 'integer',
        'invitee_email' => 'string',
        'chat_id' => 'integer',
        'accepted' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relaciones

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}