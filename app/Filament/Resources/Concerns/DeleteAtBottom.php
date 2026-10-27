<?php

namespace App\Filament\Resources\Concerns;

use Filament\Actions;

/**
 * Edit pages keep a Save button at the top and move the destructive Delete action to the bottom,
 * away from where people click first. Pages list their delete action(s) in getBottomDeleteActions().
 */
trait DeleteAtBottom
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('saveTop')
                ->label('Salvar alterações')
                ->action('save'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            ...$this->getBottomDeleteActions(),
        ];
    }

    /** @return array<int, Actions\Action> */
    protected function getBottomDeleteActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
