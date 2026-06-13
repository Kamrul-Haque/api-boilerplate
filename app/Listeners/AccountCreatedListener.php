<?php

namespace App\Listeners;

use App\Events\AccountCreatedEvent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountCreatedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(AccountCreatedEvent $event): void
    {
        $this->notificationService->sendAccountCreatedMail($event->user, $event->password);
    }
}
