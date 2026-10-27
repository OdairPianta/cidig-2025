<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use SavesUserInformation;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Excluir')
                ->modalDescription('O perfil será arquivado e deixará de aparecer no site, mas os conteúdos vinculados são preservados.')
                ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return array_merge($data, $this->record->information?->only(self::INFORMATION_FIELDS) ?? []);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->extractInformation($data);
    }

    protected function afterSave(): void
    {
        $this->persistInformation();
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
