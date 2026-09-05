<?php

namespace App\Filament\Livewire;

use Filament\Facades\Filament;
use Filament\Notifications\Livewire\DatabaseNotifications as BaseDatabaseNotifications;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Attributes\On;

class DatabaseNotifications extends BaseDatabaseNotifications
{
    public static bool $isPaginated = false;

    public function getUser(): Model | Authenticatable | null
    {
        return Filament::auth()->user();
    }

    public function getPollingInterval(): ?string
    {
        return Filament::getDatabaseNotificationsPollingInterval();
    }

    public function getTrigger(): View
    {
        return view('filament-panels::components.topbar.database-notifications-trigger');
    }

    /**
     * Show ONLY unread notifications in the drawer.
     */
    public function getNotificationsQuery(): Builder | Relation
    {
        $user = $this->getUser();

        if (! $user) {
            abort(401);
        }

        /** @phpstan-ignore-next-line */
        return $user->unreadNotifications()->where('data->format', 'filament');
    }

    /**
     * When admin clicks a notification card or its "mark as read" action,
     * we delete the record entirely so it vanishes from the list immediately.
     */
    #[On('markedNotificationAsRead')]
    public function markNotificationAsRead(string $id): void
    {
        $user = $this->getUser();
        if ($user) {
            $user->notifications()->where('id', $id)->delete();
            $this->dispatch('close-notification', id: $id);
        }
    }

    /**
     * X button close — delete the notification.
     */
    #[On('notificationClosed')]
    public function removeNotification(string $id): void
    {
        $user = $this->getUser();
        if ($user) {
            $user->notifications()->where('id', $id)->delete();
            $this->dispatch('close-notification', id: $id);
        }
    }

    /**
     * "Clear" button — delete ALL notifications for this admin.
     */
    public function clearNotifications(): void
    {
        $user = $this->getUser();
        if ($user) {
            $user->notifications()->where('data->format', 'filament')->delete();
            $this->dispatch('close-modal', id: 'database-notifications');
        }
    }

    /**
     * "Mark all as read" — DELETE all notifications so the drawer empties instantly.
     */
    public function markAllNotificationsAsRead(): void
    {
        $user = $this->getUser();
        if ($user) {
            $user->notifications()->where('data->format', 'filament')->delete();
            $this->dispatch('close-modal', id: 'database-notifications');
        }
    }
}
