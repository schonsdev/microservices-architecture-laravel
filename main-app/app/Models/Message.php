<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    const TYPE_TEXT = 'text';
    const TYPE_AUDIO = 'audio';


    protected $fillable = [
        'type',
        'message',
        'audio_link'
    ];


}
