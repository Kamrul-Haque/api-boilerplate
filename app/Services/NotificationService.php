<?php

namespace App\Services;

use App\Enums\VerificationCodeIdentifierKey;
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
    public function sendVerificationCode(string $identifierKey, string $identifierValue, string $code): void
    {
        if ($identifierKey === VerificationCodeIdentifierKey::EMAIL->value || $identifierKey === VerificationCodeIdentifierKey::BOTH->value) {
            Notification::route('mail', $identifierValue)->notify(new SendVerificationCode($code));
        }

        if ($identifierKey === VerificationCodeIdentifierKey::PHONE->value || $identifierKey === VerificationCodeIdentifierKey::BOTH->value) {
            Notification::route('vonage', $identifierValue)->notify(new SendVerificationCode($code));
        }
    }
}
