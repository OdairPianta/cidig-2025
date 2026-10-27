<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketComment;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTicket extends EditRecord
{
    use \App\Filament\Resources\Concerns\DeleteAtBottom;

    protected static string $resource = TicketResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Editar';
    }
    
    public function getTitle(): string
    {
        return 'Editar Ticket';
    }

    protected function getBottomDeleteActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function handleRecordUpdate(Ticket|Model $record, array $data): Ticket
    {
        $newStatus = (int) $data['status'];
        $comment = new TicketComment([
            'description' => 'Mudou o status do ticket de "' . $record->getStatusText() . '" para "' . Ticket::STATUSES_TEXTS[$newStatus] . '"',
            'comment_type' => TicketComment::TYPE_SYSTEM_COMMENT,
            'created_by' => auth()->id(),
        ]);

        $record->update($data);
        $record->comments()->save($comment);

        return $record;
    }
}