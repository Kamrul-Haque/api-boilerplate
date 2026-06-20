<?php

namespace App\Listeners;

use App\Events\SendVerificationCodeEvent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendVerificationCodeListener implements ShouldQueue
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
    public function handle(SendVerificationCodeEvent $event): void
    {
        $this->notificationService->sendVerificationCode($event->identifierKey, $event->identifierValue, $event->code);
    }
}
