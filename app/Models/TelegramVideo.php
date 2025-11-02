<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TelegramVideo extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_id',
        'file_unique_id',
        'file_size',
        'duration',
        'width',
        'height',
        'file_name',
        'mime_type',
        'caption',
    ];
}
