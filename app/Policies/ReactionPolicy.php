<?php

namespace App\Policies;

use App\Models\Reaction;
use App\Models\User;

class ReactionPolicy
{
    /**
     * Every guest can view a photo's reactions.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Every guest can view an individual reaction.
     */
    public function view(User $user, Reaction $reaction): bool
    {
        return true;
    }

    /**
     * Every guest can react to a photo.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the author can change their reaction.
     */
    public function update(User $user, Reaction $reaction): bool
    {
        return $user->id === $reaction->user_id;
    }

    /**
     * The author or an admin can remove a reaction.
     */
    public function delete(User $user, Reaction $reaction): bool
    {
        return $user->is_admin || $user->id === $reaction->user_id;
    }
}
