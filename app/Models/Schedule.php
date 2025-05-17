<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'scheduled_time',
        'retry_count',
        'last_attempt',
        'status',
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'last_attempt' => 'datetime',
    ];

    /**
     * Get the post that owns the schedule.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Check if the schedule is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the schedule is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if the schedule is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the schedule has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the schedule should be retried
     */
    public function shouldRetry(int $maxRetries = 3): bool
    {
        return $this->hasFailed() && $this->retry_count < $maxRetries;
    }

    /**
     * Increment the retry count
     */
    public function incrementRetryCount(): void
    {
        $this->retry_count++;
        $this->last_attempt = now();
        $this->save();
    }
}
