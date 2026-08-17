<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Every guest can view a photo's comments.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Every guest can view an individual comment.
     */
    public function view(User $user, Comment $comment): bool
    {
        return true;
    }

    /**
     * Every guest can comment on a photo.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the author can edit their comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    /**
     * The author or an admin can remove a comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->is_admin || $user->id === $comment->user_id;
    }
}
