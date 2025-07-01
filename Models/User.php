<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'name',
        'avatar',
        'bio',
        'location',
        'website',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'user_id');
    }

    public function followers()
    {
        return $this->belongsToMany(
            User::class,
            'followers',
            'follower_id',
            'followed_id',
        )->withTimestamps();
    }

    public function followings()
    {
        return $this->belongsToMany(
            User::class,
            'followers',
            'follower_id',
            'followed_id',
        )->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function personalAccessTokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value)
            ? Hash::make($value)
            : $value;
    }

    //******************************************************************************************************** 
    // CHAT

    // Chats donde es user_one (el usuario con menor id)
    public function chatsAsUserOne()
    {
        return $this->hasMany(Chat::class, 'user_one_id');
    }

    // Chats donde es user_two (el usuario con mayor id)
    public function chatsAsUserTwo()
    {
        return $this->hasMany(Chat::class, 'user_two_id');
    }

    // Todos los chats (combinación de los dos anteriores, usando un accesor)
    public function getChatsAttribute()
    {
        return $this->chatsAsUserOne->merge($this->chatsAsUserTwo);
    }

    // Mensajes enviados por el usuario
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Invitaciones enviadas por el usuario
    public function sentChatInvitations()
    {
        return $this->hasMany(ChatInvitation::class, 'inviter_id');
    }

    public function messageReads()
    {
        return $this->hasMany(MessageRead::class, 'user_id');
    }
}
