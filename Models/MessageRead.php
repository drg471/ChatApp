<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageRead extends Model
{
    protected $table = 'message_reads';
    protected $primaryKey = 'id';
    public $timestamps = false; // no hay updated_at ni created_at, solo read_at

    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'message_id' => 'integer',
        'user_id' => 'integer',
        'read_at' => 'datetime',
    ];

    // Relaciones

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
