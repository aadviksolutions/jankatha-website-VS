<?php

namespace App\Policies;

use App\Models\CitizenSubmission;
use App\Models\User;

class CitizenSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('citizen', 'contributor', 'super_admin', 'admin', 'editor');
    }

    public function view(User $user, CitizenSubmission $submission): bool
    {
        return $this->isModerator($user) || $submission->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('citizen', 'contributor');
    }

    public function update(User $user, CitizenSubmission $submission): bool
    {
        return $submission->canBeEditedBy($user);
    }

    public function moderate(User $user, CitizenSubmission $submission): bool
    {
        return $this->isModerator($user);
    }

    private function isModerator(User $user): bool
    {
        return $user->hasRole('super_admin', 'admin', 'editor');
    }
}
