<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketComment;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;


class ManageTicketReplies extends ManageRelatedRecords
{
    protected static string $resource = TicketResource::class;

    protected static string $relationship = 'comments';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    public function getTitle(): string|Htmlable
    {
        return "Comentários";
    }

    public function getBreadcrumb(): string
    {
        return 'Comentários';
    }

    public static function getNavigationLabel(): string
    {
        return 'Comentários';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->label('Descrição')
                    ->required(),
                Forms\Components\Hidden::make('comment_type')
                    ->default(1),
                Forms\Components\Hidden::make('ticket_id')
                    ->default($this->record->id),
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->user()->id),
            ])
            ->columns(1);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                TextEntry::make('createdBy.name')
                    ->label('Criado por'),
                TextEntry::make('description')
                    ->label('Conteúdo')
                    ->markdown(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->description(fn (TicketComment $record) => 'por ' . $record->createdBy->name)
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('comment_type')
                    ->label('Tipo')
                    ->icon(fn (int $state) => match ($state) {
                        1 => 'heroicon-o-user',
                        default => 'heroicon-o-cog',
                    })
                    ->color(fn (int $state) => match ($state) {
                        1 => 'info',
                        default => 'success',
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Adicionar comentário'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Visualizar'),
                Tables\Actions\EditAction::make()->label('Editar')
                    ->hidden(fn (TicketComment $record): bool => $this->shouldHiddeAction($record)),
                Tables\Actions\DeleteAction::make()->label('Excluir')->hidden(fn (TicketComment $record): bool => $this->shouldHiddeAction($record)),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    private function shouldHiddeAction(TicketComment $record): bool
    {
        if ($record->comment_type == TicketComment::TYPE_SYSTEM_COMMENT) {
            return true;
        }

        if (auth()->user()->isAdmin()) {
            return false;
        }

        return $record->created_by !== auth()->id();
    }
}
