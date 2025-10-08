<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Subject;
use App\Models\User;
use App\Models\University;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    private const ROLES = [
        "researcher" => "Pesquisador",
        "designer" => "Designer",
        "coordinator" => "Coordenador",
        "developer" => "Desenvolvedor",
    ];

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

    protected static ?string $navigationGroup = 'Sistema';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados Básicos')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
Forms\Components\TextInput::make('name')
    ->label('Nome')
    ->required()
    ->validationMessages([
        'required' => 'O nome é obrigatório.',
    ]),
Forms\Components\TextInput::make('email')
    ->label('Email')
    ->email()
    ->required()
    ->validationMessages([
        'required' => 'O email é obrigatório.',
        'email' => 'O email deve ser um endereço válido.',
    ]),
Forms\Components\TextInput::make('password')
    ->label('Senha')
    ->password()
    ->dehydrateStateUsing(fn ($state) => \Hash::make($state))
    ->dehydrated(fn ($state) => filled($state))
    ->same('password_confirmation')
    ->required()
    ->validationMessages([
        'required' => 'A senha é obrigatória.',
        'same' => 'A confirmação de senha deve ser igual à senha.',
    ]),
Forms\Components\TextInput::make('password_confirmation')
    ->label('Confirmar Senha')
    ->password()
    ->same('password')
    ->required()
    ->validationMessages([
        'required' => 'Confirmar a senha é obrigatório.',
        'same' => 'A confirmação de senha deve ser igual à senha.',
    ]),

                    ]),

                Forms\Components\Section::make('Configurações')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('subjects')
                            ->label('Temas')
                            ->placeholder('Selecione os temas relacionados')
                            ->relationship('subjects', 'name')
                            ->options(Subject::query()->isActive()->orderBy('name')->pluck('name', 'id'))
                            ->multiple()
                            ->hidden(fn (?User $record) => !auth()->user()->isAdmin()),
                        Forms\Components\Checkbox::make('is_active')
                            ->default(false)
                            ->hidden(fn (?User $record) => !auth()->user()->isAdmin() || ($record && $record->id === auth()->id()))
                            ->label('Ativo'),
                    ]),
                Forms\Components\Section::make('Informações Adicionais')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('university_id')
                            ->label('Universidade')
                            ->placeholder('Selecione uma universidade')
                            ->options(University::query()->where('is_active', true)->pluck('name', 'id')),
                        
                        Forms\Components\FileUpload::make('picture_url')
                            ->label('Foto de perfil')
                            ->image()
                            ->acceptedFileTypes(['image/bmp','image/jpeg', 'image/x-png', 'image/png', 'image/gif'])
                            ->directory('images/team')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable(),
                    ]),
                    
                Forms\Components\Section::make('Links')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
Forms\Components\TextInput::make('linkedin_url')
    ->label('LinkedIn')
    ->url()
    ->validationMessages([
        'url' => 'O LinkedIn deve ser uma URL válida.',
    ]),
Forms\Components\TextInput::make('lattes_url')
    ->label('Lattes')
    ->url()
    ->validationMessages([
        'url' => 'O Lattes deve ser uma URL válida.',
    ]),
Forms\Components\TextInput::make('orcid_url')
    ->label('ORCID')
    ->url()
    ->validationMessages([
        'url' => 'O ORCID deve ser uma URL válida.',
    ]),
                    ]),
                    
                Forms\Components\Section::make('Biografia e Descrição')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Textarea::make('minibio')
    ->label('Mini Bio')
    ->rows(3)
    ->minLength(90)
    ->helperText('Mínimo de 90 caracteres')
    ->validationMessages([
        'min' => 'A mini bio deve ter pelo menos 90 caracteres.',
    ]),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3),
                        
                        Forms\Components\TextInput::make('title')
                            ->label('Título'),
                        
                        Forms\Components\Select::make('roles')
                            ->label('Funções')
                            ->placeholder('Selecione as funções')
                            ->multiple()
                            ->options(self::ROLES),
                        
Forms\Components\TextInput::make('website')
    ->label('Site')
    ->url()
    ->validationMessages([
        'url' => 'O site deve ser uma URL válida.',
    ]),
                    ]),
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
                Tables\Columns\TextColumn::make('information.university.name')
                    ->label('Universidade')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('information.roles')
                    ->label('Funções')
                    ->formatStateUsing(function ($state) {
                        if (!is_array($state) || empty($state)) {
                            return '';
                        }
                        
                        $roleNames = [];
                        foreach ($state as $role) {
                            if (isset(self::ROLES[$role])) {
                                $roleNames[] = self::ROLES[$role];
                            }
                        }
                        
                        return implode(', ', $roleNames);
                    })
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }
}