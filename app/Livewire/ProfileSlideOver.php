<?php

namespace App\Livewire;

use App\Support\ProfileForm;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Edits the logged-in person's profile in a slide-over, so the admin does not navigate away.
 * E-mail and password stay on the full profile page.
 */
class ProfileSlideOver extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    #[On('open-profile')]
    public function open(): void
    {
        $this->mountAction('edit');
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->modalHeading('Meu perfil')
            ->slideOver()
            ->modalWidth('3xl')
            ->modalSubmitActionLabel('Salvar')
            ->modalCancelActionLabel('Fechar')
            ->modalFooterActions(fn (Action $action): array => [
                $action->getModalSubmitAction(),
                $action->getModalCancelAction(),
                Action::make('fullPage')
                    ->label('Alterar e-mail ou senha')
                    ->color('gray')
                    ->url(filament()->getProfileUrl()),
            ])
            ->fillForm(fn (): array => [
                'name' => auth()->user()->name,
                ...(auth()->user()->information?->only(ProfileForm::INFORMATION_FIELDS) ?? []),
            ])
            ->form([
                Forms\Components\TextInput::make('name')->label('Nome')->required()->maxLength(255),
                ...ProfileForm::informationFields(),
            ])
            ->action(function (array $data): void {
                $user = auth()->user();
                $user->update(ProfileForm::saveInformation($user, $data));

                Notification::make()->success()->title('Perfil atualizado')->send();
            });
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                <x-filament-actions::modals />
            </div>
        BLADE;
    }
}
