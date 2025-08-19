<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Ajuda';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Radio::make('ticket_type')
                    ->label('Tipo')
                    ->options(Ticket::TYPES_TEXTS)
                    ->inline()
                    ->inlineLabel(false)
                    ->default(request()->query('ticket_type') ?? 2)
                    ->required(),
                Forms\Components\Radio::make('status')
                    ->label('Status')
                    ->options(Ticket::STATUSES_OPTIONS)
                    ->inline()
                    ->inlineLabel(false)
                    ->default(0)
                    ->required(),
                Forms\Components\Select::make('responsible_id')
                    ->label('Responsável')
                    ->placeholder('Selecione um responsável')
                    ->options(User::query()->where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->rows(5)
                    ->autosize()
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('responsible.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->description(fn (Ticket $record) => 'por ' . $record->createdBy->name)
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->description(fn (Ticket $record) => 'por ' . ($record->updatedBy?->name ?? 'N/A'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->icon(fn (int $state) => match ($state) {
                        0 => 'heroicon-o-document',
                        1 => 'heroicon-o-document-magnifying-glass',
                        default => 'heroicon-o-document-check',
                    })
                    ->color(fn (int $state) => match ($state) {
                        0 => 'info',
                        1 => 'warning',
                        2 => 'success',
                        default => 'gray',
                    })
                    ->tooltip(fn (int $state) => Ticket::STATUSES_TEXTS[$state])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        0 => 'Aberto',
                        1 => 'Em progresso',
                        2 => 'Resolvido',
                        3 => 'Fechado - Não realizado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar')->hidden(fn (Ticket $record): bool => self::shouldHiddeAction($record)),
                Tables\Actions\ViewAction::make()->label('Visualizar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn (Ticket $record): string => Pages\ViewTicket::getUrl(['record' => $record])
            );
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $actions[] = Pages\ViewTicket::class;
        if (!self::shouldHiddeAction($page->getRecord())) {
            $actions[] = Pages\EditTicket::class;
        }
        $actions[] = Pages\ManageTicketReplies::class;

        return $page->generateNavigationItems($actions);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'show' => Pages\ViewTicket::route('/{record}'),
            'comments' => Pages\ManageTicketReplies::route('/{record}/comments'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    private static function shouldHiddeAction(Ticket $record): bool
    {
        if (auth()->user()->isAdmin()) {
            return false;
        }

        if ($record->status !== Ticket::STATUS_OPEN) {
            return true;
        }

        return $record->created_by !== auth()->id();
    }
}
