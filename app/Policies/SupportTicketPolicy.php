<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SupportTicket $supportTicket): bool
    {
        return $user->id === $supportTicket->user_id || $user->is_admin;
    }

    public function reply(User $user, SupportTicket $supportTicket): bool
    {
        return $user->id === $supportTicket->user_id || $user->is_admin;
    }
}
