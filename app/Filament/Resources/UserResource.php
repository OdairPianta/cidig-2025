<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Subject;
use App\Models\User;
use App\Support\ProfileLinks;
use App\Models\University;
use Filament\Forms;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    private const ROLES = [
        "professor" => "Professor",
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
    ->required(fn (string $operation): bool => $operation === 'create')
    ->validationMessages([
        'required' => 'A senha é obrigatória.',
        'same' => 'A confirmação de senha deve ser igual à senha.',
    ]),
Forms\Components\TextInput::make('password_confirmation')
    ->label('Confirmar Senha')
    ->password()
    ->same('password')
    ->dehydrated(false)
    ->required(fn (string $operation): bool => $operation === 'create')
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
                        Forms\Components\TextInput::make('recovery_email')
                            ->label('E-mail de recuperação')
                            ->email()
                            ->helperText('E-mail pessoal usado, além do e-mail de acesso, para recuperar a senha.'),
                        Forms\Components\Select::make('university_id')
                            ->label('Instituição')
                            ->placeholder('Selecione ou cadastre uma instituição')
                            ->helperText('Não encontrou? Use o "+" para cadastrar uma nova instituição.')
                            ->options(fn () => University::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome da instituição')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(fn (array $data) => University::findOrCreateByName($data['name'])->getKey()),
                        
                        Forms\Components\FileUpload::make('picture_url')
                            ->label('Foto de perfil')
                            ->image()
                            ->disk(\App\Support\Uploads::disk())
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->helperText('Foto quadrada (1:1), JPG ou PNG, até 10 MB. Use o editor para recortar.')
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
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
...ProfileLinks::fields(),
                    ]),
                    
                Forms\Components\Section::make('Biografia e Currículo')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Textarea::make('minibio')
    ->label('Mini Bio')
    ->rows(3)
    ->minLength(90)
    ->helperText('Resumo curto (mínimo de 90 caracteres), exibido nos cartões e listas da equipe.')
    ->validationMessages([
        'min' => 'A mini bio deve ter pelo menos 90 caracteres.',
    ]),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Currículo Completo')
                            ->helperText('Texto completo e opcional, exibido na página de perfil.')
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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('information.university.name')
                    ->label('Instituição')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\CheckboxColumn::make('is_active')
                    ->label('Ativo')
                    ->disabled(fn (User $record) => !auth()->user()->isAdmin() || $record->id === auth()->id())
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\Filter::make('order')
                    ->label('Ordenar por')
                    ->form([
                        Forms\Components\Select::make('order_by')
                            ->label('Ordenar por')
                            ->placeholder('Padrão (ordem alfabética)')
                            ->options([
                                'name_asc' => 'Ordem alfabética (A–Z)',
                                'name_desc' => 'Ordem alfabética (Z–A)',
                                'created_desc' => 'Perfis criados recentemente',
                                'created_asc' => 'Perfis mais antigos',
                                'updated_desc' => 'Atualizados recentemente',
                            ]),
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['order_by'] ?? null) {
                            'name_asc' => $query->reorder('name'),
                            'name_desc' => $query->reorder('name', 'desc'),
                            'created_desc' => $query->reorder('created_at', 'desc'),
                            'created_asc' => $query->reorder('created_at'),
                            'updated_desc' => $query->reorder('updated_at', 'desc'),
                            default => $query,
                        };
                    })
                    ->indicateUsing(fn (array $data): ?string => ($data['order_by'] ?? null) ? 'Ordenação personalizada' : null),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativo')
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Visualizar')->url(null)->modalWidth('4xl'),
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
            Infolists\Components\Section::make('Dados Básicos')->columns(2)->schema([
                Infolists\Components\TextEntry::make('name')->label('Nome')->copyable(),
                Infolists\Components\TextEntry::make('email')->label('Email')->copyable(),
                Infolists\Components\IconEntry::make('is_active')->label('Ativo')->boolean(),
                Infolists\Components\TextEntry::make('subjects.name')->label('Temas')->badge(),
            ]),
            Infolists\Components\Section::make('Informações Adicionais')->columns(2)->schema([
                Infolists\Components\ImageEntry::make('information.picture_link')->label('Foto de perfil')->circular(),
                Infolists\Components\TextEntry::make('information.university.name')->label('Instituição')->copyable(),
                Infolists\Components\TextEntry::make('information.title')->label('Título')->copyable(),
                Infolists\Components\TextEntry::make('information.website')->label('Website')->copyable(),
                Infolists\Components\TextEntry::make('information.lattes_url')->label('Lattes')->copyable(),
                Infolists\Components\TextEntry::make('information.orcid_url')->label('ORCID')->copyable(),
                Infolists\Components\TextEntry::make('information.linkedin_url')->label('LinkedIn')->copyable(),
                Infolists\Components\TextEntry::make('information.minibio')->label('Minibio')->columnSpanFull()->copyable(),
                Infolists\Components\TextEntry::make('information.description')->label('Currículo Completo')->columnSpanFull()->copyable(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }
}