<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Notifications\WelcomeNewUser;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;
use Throwable;

class CreateUser extends CreateRecord
{
    use SavesUserInformation;

    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->extractInformation($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    /**
     * Sends the new user a link to set a password and complete the profile.
     * A mail failure must not undo the registration, so it only warns the admin.
     */
    protected function afterCreate(): void
    {
        $this->persistInformation();

        try {
            $token = Password::broker()->createToken($this->record);
            $this->record->notify(new WelcomeNewUser($token));
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->warning()
                ->title('Usuário cadastrado, mas o e-mail não pôde ser enviado')
                ->body('Verifique a configuração de e-mail do servidor e reenvie o acesso pelo botão "Esqueci minha senha".')
                ->persistent()
                ->send();
        }
    }
}
