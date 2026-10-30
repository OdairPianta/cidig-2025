<?php

namespace App\Filament\Pages\Auth;

use App\Models\Subject;
use App\Support\RegistrationRequests;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Public sign-up becomes a request: nobody is logged in, and the ticket has to be authorised first.
 */
class RequestRegistration extends Register
{
    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(3);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        RegistrationRequests::submit($data['name'], $data['email'], $data['subject_id'] ?? null, $data['message'] ?? null);

        $this->form->fill();

        Notification::make()
            ->success()
            ->title('Solicitação enviada')
            ->body('Você receberá um e-mail para definir sua senha assim que o cadastro for autorizado.')
            ->persistent()
            ->send();

        return null;
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getSubjectFormComponent(),
                        Textarea::make('message')->label('Mensagem (opcional)')->rows(3)->maxLength(1000),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getSubjectFormComponent(): Component
    {
        return Select::make('subject_id')
            ->label('Linha de pesquisa')
            ->helperText('A solicitação é analisada por quem coordena a linha escolhida. Sem linha, ela vai para a administração.')
            ->options(fn () => Subject::query()->orderBy('name')->pluck('name', 'id'))
            ->searchable();
    }

    public function getTitle(): string | Htmlable
    {
        return 'Solicitar cadastro';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Solicitar cadastro';
    }

    public function getRegisterFormAction(): \Filament\Actions\Action
    {
        return parent::getRegisterFormAction()->label('Enviar solicitação');
    }
}
