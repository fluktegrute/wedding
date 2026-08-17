<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;

class PhotoPolicy
{
    /**
     * Every guest can browse the gallery.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Every guest can view any individual photo.
     */
    public function view(User $user, Photo $photo): bool
    {
        return true;
    }

    /**
     * Every guest can deposit a photo.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the uploader or an admin can edit a photo.
     */
    public function update(User $user, Photo $photo): bool
    {
        return $user->is_admin || $user->id === $photo->user_id;
    }

    /**
     * Only the uploader or an admin can remove a photo.
     */
    public function delete(User $user, Photo $photo): bool
    {
        return $user->is_admin || $user->id === $photo->user_id;
    }

    /**
     * Only an admin can feature a photo.
     */
    public function feature(User $user, Photo $photo): bool
    {
        return $user->is_admin;
    }

    /**
     * Only an admin can export a photo to Google Drive.
     */
    public function syncToGdrive(User $user, Photo $photo): bool
    {
        return $user->is_admin;
    }
}
