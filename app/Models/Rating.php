<?php
// app/Models/Rating.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'movie_id',
        'series_id',
        'episode_id',
        'rating',
        'review_text',
        'spoiler_flag'
    ];

    protected $casts = [
        'rating' => 'integer',
        'spoiler_flag' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    // Validation
    public static function rules(): array
    {
        return [
            'rating' => 'required|integer|between:1,10',
            'review_text' => 'nullable|string|max:1000',
            'spoiler_flag' => 'boolean',
        ];
    }
}
