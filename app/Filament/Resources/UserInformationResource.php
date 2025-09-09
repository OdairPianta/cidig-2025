<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserInformationResource\Pages;
use App\Filament\Resources\UserInformationResource\RelationManagers;
use App\Models\UserInformation;
use App\Models\University;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserInformationResource extends Resource
{
    private const ROLES = [
        "researcher" => "Pesquisador",
        "designer" => "Designer",
        "coordinator" => "Coordenador",
        "developer" => "Desenvolvedor",
    ]; 

    protected static ?string $model = UserInformation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuário')
                    ->placeholder('Selecione um usuário')
                    ->options(User::all()->pluck('name', 'id')) // Correção aqui
                    ->required(),
                    
                Forms\Components\Select::make('university_id')
                    ->label('Universidade')
                    ->placeholder('Selecione uma universidade')
                    ->options(University::query()->where('is_active', true)->pluck('name', 'id'))
                    ->required(),
                    
                Forms\Components\FileUpload::make('picture_url')
                    ->label('Foto de perfil')
                    ->image()
                    ->acceptedFileTypes(['image/bmp','image/jpeg', 'image/x-png', 'image/png', 'image/gif'])
                    ->directory('images/team')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                    
                Forms\Components\Section::make('Links')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('linkedin_url') // Correção: era kinkedin_url
                            ->label('LinkedIn')
                            ->url(),
                        Forms\Components\TextInput::make('lattes_url')
                            ->label('Lattes')
                            ->url(),
                        Forms\Components\TextInput::make('orcid_url') // Correção: estava duplicado lattes_url
                            ->label('ORCID')
                            ->url(),
                    ]),
                    
                Forms\Components\Textarea::make('minibio')
                    ->label('Mini Bio')
                    ->rows(3),
                    
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3),
                    
                Forms\Components\TextInput::make('title')
                    ->label('Título'),
                    
                Forms\Components\Select::make('roles')
                    ->label('Funções')
                    ->placeholder('Selecione as funções')
                    ->multiple()
                    ->options(self::ROLES)
                    ->required(),
                    
                Forms\Components\TextInput::make('website')
                    ->label('Site')
                    ->url(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Id do usuário')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('university.name')
                    ->label('Universidade')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles')
    ->label('Funções')
    ->formatStateUsing(
        function ($state) {
            // Handle null or non-string values
            if (!is_string($state) || empty($state)) {
                return '';
            }
            
            $roleNames = [];
            $roleValues = explode(',', $state);
            
            foreach ($roleValues as $role) {
                $role = trim($role);
                // Check if the role exists in the ROLES array
                if (!empty($role) && isset(self::ROLES[$role])) {
                    $roleNames[] = self::ROLES[$role];
                }
            }
            
            return implode(', ', $roleNames);
        }
    )
    ->sortable()
    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
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
            'index' => Pages\ListUserInformation::route('/'),
            'create' => Pages\CreateUserInformation::route('/create'),
            'edit' => Pages\EditUserInformation::route('/{record}/edit'),
        ];
    }
    
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }
}
