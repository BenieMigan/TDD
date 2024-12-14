<?php

namespace App\Policies;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChirpPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given chirp can be updated by the user.
     */
    public function update(User $user, Chirp $chirp): bool
    {
        return $chirp->user_id === $user->id;
    }

    /**
     * Determine if the given chirp can be deleted by the user.
     */
    public function delete(User $user, Chirp $chirp): bool
    {
        return $chirp->user_id === $user->id;
    }
}
