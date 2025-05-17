<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'type',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    /**
     * Get the user that owns the log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post associated with the log.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Create an info log
     */
    public static function info(string $message, array $context = [], ?int $userId = null, ?int $postId = null): self
    {
        return self::create([
            'type' => 'info',
            'message' => $message,
            'context' => $context,
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    /**
     * Create a warning log
     */
    public static function warning(string $message, array $context = [], ?int $userId = null, ?int $postId = null): self
    {
        return self::create([
            'type' => 'warning',
            'message' => $message,
            'context' => $context,
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    /**
     * Create an error log
     */
    public static function error(string $message, array $context = [], ?int $userId = null, ?int $postId = null): self
    {
        return self::create([
            'type' => 'error',
            'message' => $message,
            'context' => $context,
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    /**
     * Create a success log
     */
    public static function success(string $message, array $context = [], ?int $userId = null, ?int $postId = null): self
    {
        return self::create([
            'type' => 'success',
            'message' => $message,
            'context' => $context,
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }
}
