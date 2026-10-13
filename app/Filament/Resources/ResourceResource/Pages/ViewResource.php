<?php

namespace App\Filament\Resources\ResourceResource\Pages;

use App\Filament\Resources\ResourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResource extends ViewRecord
{
    protected static string $resource = ResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('copyAll')
                ->label('Copiar todos os dados')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->extraAttributes(fn () => [
                    'x-on:click' => 'window.navigator.clipboard.writeText(' . json_encode($this->getRecord()->toCopyText(), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) . ')'
                        . ".then(() => \$tooltip('Copiado!', { timeout: 1500 }))",
                ]),
            Actions\EditAction::make()->label('Editar'),
        ];
    }
}
