<?php

namespace App\Filament\Pages\Auth;

use App\Support\PasswordRecovery;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Support\Facades\Password;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    protected function getCredentialsFromFormData(array $data): array
    {
        return ['email' => PasswordRecovery::accountEmailFor($data['email'] ?? null) ?? $data['email']];
    }

    protected function getFailureNotification(string $status): ?Notification
    {
        return Notification::make()
            ->title($status === Password::INVALID_USER ? PasswordRecovery::NOT_FOUND : PasswordRecovery::THROTTLED)
            ->danger();
    }

    protected function getSentNotification(string $status): ?Notification
    {
        return Notification::make()
            ->title(PasswordRecovery::SENT)
            ->success();
    }
}
