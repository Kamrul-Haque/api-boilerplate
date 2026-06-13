<?php

namespace App\Services;

use App\Mail\NewAccountCreated;
use App\Models\User;
use App\Notifications\SendVerificationCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send account created mail.
     */
    public function sendAccountCreatedMail(User $user, string $password): void
    {
        Mail::to($user->email)->send(new NewAccountCreated($user, $password));
    }

    /**
     * Send verification code.
     */
    public function sendVerificationCode(string $email, int $code): void
    {
        Notification::route('mail', $email)->notify(new SendVerificationCode($code));
    }
}
