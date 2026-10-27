<?php

namespace App\Filament\Resources;

use App\Filament\FilamentResourceBase;
use App\Filament\Resources\TagResource\Pages;
use App\Filament\Resources\TagResource\RelationManagers;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TagResource extends FilamentResourceBase
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    protected static ?string $navigationGroup = 'Conteúdos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
Forms\Components\TextInput::make('name')
    ->required()
    ->label('Nome')
    ->maxLength(255)
    ->helperText('Escreva a tag no singular e sem abreviações. Tags repetidas (maiúsculas/minúsculas, plural, acentos) são bloqueadas.')
    ->rules([
        fn (?Tag $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
            $duplicate = Tag::findDuplicateOf((string) $value, $record?->getKey());

            if ($duplicate) {
                $fail("Já existe uma tag parecida: \"{$duplicate->name}\".");
            }
        },
    ])
    ->validationMessages([
        'required' => 'O nome da tag é obrigatório.',
    ]),
                Forms\Components\Checkbox::make('is_active')
                    ->label('Ativa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount(['resources', 'subjects']))
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resources_count')
                    ->label('Conteúdos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subjects_count')
                    ->label('Linhas de Pesquisa')
                    ->sortable(),
                Tables\Columns\CheckboxColumn::make('is_active')
                    ->label('Ativo')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Somente ativas')
                    ->query(fn (Builder $query) => $query->where('is_active', true)),
                Tables\Filters\Filter::make('duplicates')
                    ->label('Possíveis duplicatas')
                    ->query(fn (Builder $query) => $query->whereIn(
                        'normalized_name',
                        Tag::query()->select('normalized_name')->whereNotNull('normalized_name')->groupBy('normalized_name')->havingRaw('count(*) > 1')
                    )->orderBy('normalized_name')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('merge')
                        ->label('Mesclar tags selecionadas')
                        ->icon('heroicon-o-arrows-pointing-in')
                        ->requiresConfirmation()
                        ->modalDescription('Os conteúdos, linhas de pesquisa e séries das demais tags passarão para a tag escolhida, e as demais serão removidas.')
                        ->form(fn (\Illuminate\Support\Collection $records) => [
                            Forms\Components\Select::make('keep_id')
                                ->label('Manter a tag')
                                ->options($records->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data) {
                            $keep = $records->firstWhere('id', $data['keep_id']);
                            $merged = $keep->mergeFrom($records);

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title("{$merged} tag(s) mesclada(s) em \"{$keep->name}\".")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->hidden(fn (): bool => !auth()->user()->isAdmin()),
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Excluir')
                        ->hidden(fn (): bool => !auth()->user()->isAdmin()),
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
