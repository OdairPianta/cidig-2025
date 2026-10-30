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

    protected static ?string $navigationGroup = 'Sistema';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
Forms\Components\Radio::make('ticket_type')
    ->label('Tipo')
    ->options(Ticket::TYPES_OPTIONS)
    ->inline()
    ->inlineLabel(false)
    ->default(request()->query('ticket_type') ?? 2)
    ->required()
    ->validationMessages([
        'required' => 'O tipo do ticket é obrigatório.',
    ]),
Forms\Components\Radio::make('status')
    ->label('Status')
    ->options(Ticket::STATUSES_OPTIONS)
    ->inline()
    ->inlineLabel(false)
    ->default(0)
    ->required()
    ->validationMessages([
        'required' => 'O status é obrigatório.',
    ]),
Forms\Components\Select::make('responsible_id')
    ->label('Responsável')
    ->placeholder('Selecione um responsável')
    ->options(User::query()->where('is_active', true)->pluck('name', 'id'))
    ->searchable()
    ->required()
    ->validationMessages([
        'required' => 'O responsável é obrigatório.',
    ]),
Forms\Components\TextInput::make('title')
    ->label('Título')
    ->columnSpanFull()
    ->required()
    ->validationMessages([
        'required' => 'O título é obrigatório.',
    ]),
Forms\Components\MarkdownEditor::make('description')
    ->label('Descrição')
    ->helperText('Descreva o que precisa ser corrigido. Dá para usar Markdown e anexar imagens (botão de clipe).')
    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'blockquote', 'attachFiles', 'undo', 'redo'])
    ->fileAttachmentsDisk(\App\Support\Uploads::disk())
    ->fileAttachmentsDirectory('tickets/body')
    ->fileAttachmentsVisibility('public')
    ->columnSpanFull()
    ->required()
    ->validationMessages([
        'required' => 'A descrição é obrigatória.',
    ]),
Forms\Components\TextInput::make('page_url')
    ->label('Link da página com o problema')
    ->helperText('Abra a página onde o problema acontece e cole o endereço do navegador aqui.')
    ->url()
    ->maxLength(2048)
    ->columnSpanFull(),
Forms\Components\FileUpload::make('attachment')
    ->label('Captura de tela')
    ->helperText('Opcional: uma imagem mostrando o problema.')
    ->image()
    ->disk(\App\Support\Uploads::disk())
    ->directory('tickets')
    ->visibility('public')
    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Criado por')
                    ->searchable()
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
                    ->sortable(query: fn ($query, string $direction) => $query->orderByRaw(Ticket::PRIORITY_SQL . ' ' . $direction)),
                Tables\Columns\TextColumn::make('responsible.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->description(fn (Ticket $record) => 'por ' . ($record->updatedBy?->name ?? 'N/A'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // Status priority, unless the "Ordenar por" filter already chose an order.
            ->defaultSort(fn ($query) => filled($query->toBase()->orders)
                ? $query
                : $query->orderByRaw(Ticket::PRIORITY_SQL)->orderByDesc('created_at'))
            ->filters([
                Tables\Filters\Filter::make('order')
                    ->label('Ordenar por')
                    ->form([
                        Forms\Components\Select::make('order_by')
                            ->label('Ordenar por')
                            ->placeholder('Padrão (prioridade do status)')
                            ->options([
                                'created_desc' => 'Criados recentemente',
                                'created_asc' => 'Criados há mais tempo',
                                'updated_desc' => 'Atualizados recentemente',
                            ]),
                    ])
                    // baseQuery: ordering set inside ->query() is lost, filters run in a nested where().
                    ->baseQuery(fn ($query, array $data) => match ($data['order_by'] ?? null) {
                        'created_desc' => $query->reorder('created_at', 'desc'),
                        'created_asc' => $query->reorder('created_at'),
                        'updated_desc' => $query->reorder('updated_at', 'desc'),
                        default => $query,
                    })
                    ->indicateUsing(fn (array $data): ?string => ($data['order_by'] ?? null) ? 'Ordenação personalizada' : null),
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
                Tables\Actions\ViewAction::make()->label('Visualizar')->url(null)->modalWidth('4xl')
                    ->extraModalFooterActions(fn (Ticket $record): array => [
                        Tables\Actions\Action::make('open')
                            ->label('Abrir página do ticket')
                            ->url(Pages\ViewTicket::getUrl(['record' => $record]))
                            ->color('gray'),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->label('Excluir')
                    ->hidden(fn (): bool => !auth()->user()->isAdmin()),
                ]),
            ])
            // Row clicks open the same pop-up as "Visualizar" instead of leaving the list.
            ->recordUrl(null)
            ->recordAction('view');
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist->schema([
            \Filament\Infolists\Components\Section::make()->columns(2)->schema([
                \Filament\Infolists\Components\TextEntry::make('title')->label('Título')->columnSpanFull()->copyable(),
                \Filament\Infolists\Components\TextEntry::make('ticket_type')->label('Tipo')->formatStateUsing(fn (int $state) => Ticket::TYPES_TEXTS[$state] ?? '—'),
                \Filament\Infolists\Components\TextEntry::make('status')->label('Status')->badge()->formatStateUsing(fn (int $state) => Ticket::STATUSES_TEXTS[$state] ?? '—'),
                \Filament\Infolists\Components\TextEntry::make('createdBy.name')->label('Criado por'),
                \Filament\Infolists\Components\TextEntry::make('responsible.name')->label('Responsável'),
                \Filament\Infolists\Components\TextEntry::make('created_at')->label('Criado em')->dateTime(),
                \Filament\Infolists\Components\TextEntry::make('updated_at')->label('Atualizado em')->dateTime(),
                \Filament\Infolists\Components\TextEntry::make('page_url')->label('Página com o problema')->url(fn (Ticket $record) => $record->page_url, true)->placeholder('—')->columnSpanFull(),
                \Filament\Infolists\Components\TextEntry::make('description')->label('Descrição')->markdown()->columnSpanFull(),
                \Filament\Infolists\Components\ImageEntry::make('attachment')->label('Captura de tela')->disk(\App\Support\Uploads::disk())->columnSpanFull()->visible(fn (Ticket $record) => filled($record->attachment)),
            ]),
        ]);
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

    /** Non-admins only see the tickets they created or are responsible for. */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->isAdmin()) {
            return $query;
        }

        $lines = auth()->user()->subjects()->wherePivot('is_active', true)->pluck('subjects.id');

        return $query->where(fn ($q) => $q
            ->where('created_by', auth()->id())
            ->orWhere('responsible_id', auth()->id())
            // Every responsible of a research line sees that line's registration requests.
            ->orWhere(fn ($q) => $q->where('ticket_type', Ticket::TYPE_REGISTRATION)->whereIn('subject_id', $lines)));
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
