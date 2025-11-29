<?php
// app/Models/LinkHealthCheck.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkHealthCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkable_type',
        'linkable_id',
        'is_working',
        'response_time_ms',
        'http_status',
        'error_message',
        'checked_at'
    ];

    protected $casts = [
        'is_working' => 'boolean',
        'checked_at' => 'datetime',
    ];

    // Relationships
    public function linkable()
    {
        return $this->morphTo();
    }
}
