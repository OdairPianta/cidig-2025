<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UniversityResource\Pages;
use App\Filament\Resources\UniversityResource\RelationManagers;
use App\Models\University;
use Filament\Forms;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UniversityResource extends Resource
{
    protected static ?string $model = University::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $modelLabel = 'Instituição';

    protected static ?string $pluralModelLabel = 'Instituições';

    protected static ?string $navigationGroup = 'Conteúdos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nome')
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->helperText(function (?University $record, Forms\Get $get) {
                        $similar = University::similarTo((string) $get('name'), $record?->getKey());

                        return $similar->isEmpty()
                            ? null
                            : 'Você quis dizer: ' . $similar->pluck('name')->join(' · ') . '? Se for a mesma instituição, edite a existente e adicione este nome em "Nomes alternativos".';
                    })
                    ->rules([
                        fn (?University $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                            $existing = University::findByName((string) $value);

                            if ($existing && (! $record || $existing->getKey() !== $record->getKey())) {
                                $fail("Esta instituição já está cadastrada como \"{$existing->name}\".");
                            }
                        },
                    ])
                    ->validationMessages([
                        'required' => 'O nome da instituição é obrigatório.',
                    ]),
                Forms\Components\TagsInput::make('aliases')
                    ->label('Nomes alternativos')
                    ->helperText('Outros nomes ou idiomas, ex.: "Politecnico di Torino" para "Instituição Politécnica de Turim". Pressione Enter após cada nome.'),
                Forms\Components\Checkbox::make('is_active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('aliases')
                    ->label('Nomes alternativos')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\CheckboxColumn::make('is_active')
                    ->label('Ativo')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')->query(fn (Builder $query) => $query->where('is_active', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Visualizar'),
                Tables\Actions\EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Instituição')->columns(2)->schema([
                Infolists\Components\TextEntry::make('name')->label('Nome')->copyable(),
                Infolists\Components\IconEntry::make('is_active')->label('Ativo')->boolean(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUniversities::route('/'),
            'create' => Pages\CreateUniversity::route('/create'),
            'view' => Pages\ViewUniversity::route('/{record}'),
            'edit' => Pages\EditUniversity::route('/{record}/edit'),
        ];
    }
        
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }
}
