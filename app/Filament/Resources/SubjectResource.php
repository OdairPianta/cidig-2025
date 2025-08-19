<?php

namespace App\Filament\Resources;

use App\Filament\FilamentResourceBase;
use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class SubjectResource extends FilamentResourceBase
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $modelLabel = 'Verbetes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Checkbox::make('is_active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Verbete')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Criado em')->dateTime()->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado em')->dateTime()->dateTime()->sortable(),
                Tables\Columns\CheckboxColumn::make('is_active')->label('Ativo')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([])
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
