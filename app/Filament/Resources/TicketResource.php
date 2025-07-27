<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Ajuda';

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
                Tables\Columns\TextColumn::make('created_at')->label('Criado em')->dateTime()->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado em')->dateTime()->dateTime()->sortable(),
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
                        default => 'success',
                    })
                    ->tooltip(fn (int $state) => Ticket::STATUSES_TEXTS[$state])
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\ViewAction::make()->label('Visualizar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'show' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
