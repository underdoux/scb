<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any posts.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the post.
     */
    public function view(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can create posts.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the post.
     */
    public function update(User $user, Post $post): bool
    {
        if ($post->isPublished()) {
            return false;
        }

        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        if ($post->isPublished()) {
            return false;
        }

        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can schedule the post.
     */
    public function schedule(User $user, Post $post): bool
    {
        if ($post->isPublished() || $post->isScheduled()) {
            return false;
        }

        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can cancel the post schedule.
     */
    public function cancelSchedule(User $user, Post $post): bool
    {
        if (!$post->isScheduled()) {
            return false;
        }

        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can generate content for the post.
     */
    public function generateContent(User $user, Post $post): bool
    {
        if ($post->isPublished()) {
            return false;
        }

        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can generate variations of the post.
     */
    public function generateVariations(User $user, Post $post): bool
    {
        if ($post->isPublished()) {
            return false;
        }

        return $user->id === $post->user_id;
    }
}
