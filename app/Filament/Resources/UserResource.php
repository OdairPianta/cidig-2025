<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Subject;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'Usuários';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conteúdo')
                    ->columns()
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => \Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->same('password_confirmation'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirmar Senha')
                            ->password()
                            ->same('password'),
                    ]),

                Forms\Components\Select::make('subjects')
                    ->label('Tema')
                    ->placeholder('Selecione os temas relacionadas')
                    ->relationship('subjects', 'name')
                    ->options(Subject::query()->isActive()->orderBy('name')->pluck('name', 'id'))
                    ->hidden(fn (User $record) => !auth()->user()->isAdmin())
                    ->required(),
                Forms\Components\Checkbox::make('is_active')
                    ->default(false)
                    ->hidden(fn (User $record) => !auth()->user()->isAdmin() || $record->id === auth()->id())
                    ->label('Ativo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\CheckboxColumn::make('is_active')
                    ->label('Ativo')
                    ->disabled(fn (User $record) => !auth()->user()->isAdmin() || $record->id === auth()->id())
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativo')
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->nullable(),
            ])
            ->actions([
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
