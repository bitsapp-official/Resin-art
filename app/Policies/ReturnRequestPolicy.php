<?php

namespace App\Policies;

use App\Models\ReturnRequest;
use App\Models\User;

class ReturnRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ReturnRequest $returnRequest): bool
    {
        return $user->id === $returnRequest->user_id || $user->is_admin;
    }

    public function create(User $user): bool
    {
        return true;
    }
}
