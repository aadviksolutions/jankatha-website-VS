<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin', 'editor');
    }

    public function view(User $user, News $news): bool
    {
        return $user->hasRole('admin', 'editor');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin', 'editor');
    }

    public function update(User $user, News $news): bool
    {
        return $user->hasRole('admin', 'editor');
    }

    public function delete(User $user, News $news): bool
    {
        return $user->hasRole('admin', 'editor');
    }

    public function publish(User $user, News $news): bool
    {
        return $user->hasRole('admin', 'editor');
    }
}
