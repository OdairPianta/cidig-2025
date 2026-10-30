<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MapProjectResource\Pages;
use App\Models\MapProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MapProjectResource extends Resource
{
    protected static ?string $model = MapProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $modelLabel = 'Projeto do mapa';

    protected static ?string $pluralModelLabel = 'Projetos do mapa';

    protected static ?string $navigationGroup = 'Conteúdos';

    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Publicação')->schema([
                Forms\Components\Toggle::make('is_approved')
                    ->label('Aprovado')
                    ->helperText('Só projetos aprovados e com coordenadas aparecem no mapa público (/mapa).'),
            ]),
            Forms\Components\Section::make('Projeto')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->label('Nome do projeto')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\TextInput::make('country')->label('País')->maxLength(255),
                Forms\Components\TextInput::make('city')->label('Cidade')->maxLength(255),
                Forms\Components\TextInput::make('proposers')->label('Proponente(s)')->maxLength(255),
                Forms\Components\TextInput::make('proposer_nature')->label('Natureza do proponente')->maxLength(255),
                Forms\Components\Textarea::make('purpose')->label('Finalidade')->rows(3)->columnSpanFull(),
                Forms\Components\Textarea::make('description')->label('Descrição')->rows(4)->columnSpanFull(),
                Forms\Components\Textarea::make('mapping')->label('Cartografia (coletivos humanos e não humanos)')->rows(4)->columnSpanFull(),
                Forms\Components\Textarea::make('nonhuman_influence')->label('Influência dos actantes não humanos')->rows(4)->columnSpanFull(),
                Forms\Components\TagsInput::make('themes')->label('Linhas temáticas')->columnSpanFull(),
                Forms\Components\TagsInput::make('tics')->label('TICs utilizadas')->columnSpanFull(),
                Forms\Components\TagsInput::make('actants')->label('Actantes do projeto')->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Posição no mapa')->columns(2)
                ->description('Em graus decimais, por exemplo -23.5338 e -46.6359. Pode corrigir aqui o que veio errado da planilha.')
                ->schema([
                    Forms\Components\TextInput::make('latitude')->label('Latitude')->numeric()->minValue(-90)->maxValue(90),
                    Forms\Components\TextInput::make('longitude')->label('Longitude')->numeric()->minValue(-180)->maxValue(180),
                ]),
            Forms\Components\Section::make('Links')->columns(2)->schema([
                Forms\Components\TextInput::make('website')->label('Site')->url()->maxLength(2048),
                Forms\Components\TextInput::make('instagram')->label('Instagram')->url()->maxLength(2048),
                Forms\Components\TextInput::make('facebook')->label('Facebook')->url()->maxLength(2048),
                Forms\Components\TextInput::make('tiktok')->label('TikTok')->url()->maxLength(2048),
                Forms\Components\Toggle::make('has_own_app')->label('Desenvolve tecnologia própria (app)'),
                Forms\Components\TextInput::make('app_url')->label('Endereço do app')->url()->maxLength(2048),
            ]),
            Forms\Components\Section::make('Pesquisador responsável')
                ->description('Só o nome aparece no mapa. Telefone e e-mail do formulário não são guardados na plataforma.')
                ->schema([
                    Forms\Components\TextInput::make('researcher_name')->label('Nome')->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Projeto')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('country')->label('País')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('city')->label('Cidade')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('coordinates_source')
                    ->label('Coordenadas')
                    ->badge()
                    ->state(fn (MapProject $record): string => ! $record->hasCoordinates() ? 'faltando' : ($record->coordinates_source === 'corrected' ? 'corrigidas' : 'ok'))
                    ->color(fn (string $state): string => match ($state) {
                        'ok' => 'success',
                        'corrigidas' => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\ToggleColumn::make('is_approved')->label('Aprovado'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved')->label('Aprovação'),
                Tables\Filters\Filter::make('needs_review')
                    ->label('Coordenadas faltando ou corrigidas')
                    ->query(fn ($query) => $query->where(fn ($q) => $q->whereNull('latitude')->orWhere('coordinates_source', 'corrected'))),
                Tables\Filters\SelectFilter::make('country')->label('País')
                    ->options(fn () => MapProject::query()->whereNotNull('country')->distinct()->orderBy('country')->pluck('country', 'country')->all()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Aprovar selecionados')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['is_approved' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unapprove')
                        ->label('Tirar do mapa')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_approved' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMapProjects::route('/'),
            'edit' => Pages\EditMapProject::route('/{record}/edit'),
        ];
    }
}
