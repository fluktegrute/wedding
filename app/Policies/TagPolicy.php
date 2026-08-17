<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    /**
     * Every guest can see the list of tags (to filter the gallery or tag their upload).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Every guest can view an individual tag.
     */
    public function view(User $user, Tag $tag): bool
    {
        return true;
    }

    /**
     * Only an admin can create a tag.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Only an admin can rename a tag.
     */
    public function update(User $user, Tag $tag): bool
    {
        return $user->is_admin;
    }

    /**
     * Only an admin can delete a tag.
     */
    public function delete(User $user, Tag $tag): bool
    {
        return $user->is_admin;
    }
}
