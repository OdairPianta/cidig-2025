<?php

namespace App\Filament\Resources\MapProjectResource\Pages;

use App\Filament\Resources\MapProjectResource;
use App\Support\MapProjectImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListMapProjects extends ListRecords
{
    protected static string $resource = MapProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Importar CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Importar respostas do formulário')
                ->modalDescription('Na planilha de respostas: Arquivo > Fazer download > Valores separados por vírgula (.csv). Projetos já importados são ignorados e os novos entram como não aprovados.')
                ->modalSubmitActionLabel('Importar')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Arquivo CSV')
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->maxSize(5120)
                        ->disk('local')
                        ->directory('map-imports')
                        ->visibility('private'),
                ])
                ->action(function (array $data) {
                    $path = Storage::disk('local')->path($data['file']);

                    try {
                        $result = (new MapProjectImport)->import($path);
                    } catch (\Throwable $e) {
                        Notification::make()->title('Importação não concluída')->body($e->getMessage())->danger()->send();

                        return;
                    } finally {
                        Storage::disk('local')->delete($data['file']);
                    }

                    $body = "{$result['existing']} já existiam, {$result['skipped']} linhas sem nome ignoradas, {$result['without_coordinates']} sem coordenadas utilizáveis. Revise e aprove para publicar no mapa.";
                    if ($result['failed']) {
                        $body .= " {$result['failed']} linhas não puderam ser salvas (linhas do CSV: ".implode(', ', array_slice($result['failed_rows'], 0, 10)).').';
                    }

                    Notification::make()
                        ->title("{$result['created']} projetos importados")
                        ->body($body)
                        ->status($result['failed'] ? 'warning' : 'success')
                        ->send();
                }),
        ];
    }
}
