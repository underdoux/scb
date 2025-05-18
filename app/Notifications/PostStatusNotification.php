<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Post $post;
    protected string $status;
    protected ?string $message;
    protected ?array $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(Post $post, string $status, ?string $message = null, ?array $data = [])
    {
        $this->post = $post;
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting($this->getGreeting());

        // Add post content preview
        $message->line('Post Content Preview:')
            ->line(substr($this->post->content, 0, 100) . '...');

        // Add status specific information
        switch ($this->status) {
            case 'published':
                $message->line('Your post has been successfully published!')
                    ->action('View Post', $this->data['post_url'] ?? route('posts.show', $this->post));
                break;

            case 'failed':
                $message->error()
                    ->line('There was an issue publishing your post.')
                    ->line('Error: ' . ($this->message ?? 'Unknown error'))
                    ->action('View Details', route('posts.show', $this->post));
                break;

            case 'scheduled':
                $message->line('Your post has been scheduled for publishing.')
                    ->line('Scheduled Time: ' . $this->data['scheduled_time'])
                    ->action('View Schedule', route('posts.show', $this->post));
                break;

            case 'cancelled':
                $message->line('Your scheduled post has been cancelled.')
                    ->action('View Post', route('posts.show', $this->post));
                break;
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->post->id,
            'platform' => $this->post->platform,
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }

    /**
     * Get the notification subject
     */
    protected function getSubject(): string
    {
        $platform = ucfirst($this->post->platform);
        
        return match ($this->status) {
            'published' => "Your {$platform} Post Has Been Published",
            'failed' => "Failed to Publish Your {$platform} Post",
            'scheduled' => "Your {$platform} Post Has Been Scheduled",
            'cancelled' => "Your Scheduled {$platform} Post Has Been Cancelled",
            default => "Update on Your {$platform} Post",
        };
    }

    /**
     * Get the notification greeting
     */
    protected function getGreeting(): string
    {
        return match ($this->status) {
            'published' => 'Great news!',
            'failed' => 'Heads up!',
            'scheduled' => 'Just to confirm,',
            'cancelled' => 'As requested,',
            default => 'Hi there,',
        };
    }
}
