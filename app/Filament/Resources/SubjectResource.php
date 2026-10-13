<?php

namespace App\Filament\Resources;

use App\Filament\FilamentResourceBase;
use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use App\Models\Tag;
use App\Models\Video;
use Filament\Forms;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class SubjectResource extends FilamentResourceBase
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $modelLabel = 'Verbete';

    protected static ?string $pluralModelLabel = 'Verbetes';

    protected static ?string $navigationGroup = 'Conteúdos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
Forms\Components\TextInput::make('name')
    ->required()
    ->label('Título')
    ->validationMessages([
        'required' => 'O título é obrigatório.',
    ]),
                        Forms\Components\TextInput::make('slug')
                            ->label('Endereço (URL)')
                            ->helperText('Parte final do link do verbete. Use letras minúsculas, números e hífens (ex.: "ecologia-da-informacao").')
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->validationMessages(['unique' => 'Já existe um verbete com este endereço.']),
                        Forms\Components\Checkbox::make('is_active')
                            ->label('Ativo'),
Forms\Components\Textarea::make('description')
    ->label('Descrição')
    ->rows(5)
    ->autosize()
    ->columnSpanFull()
    ->required()
    ->validationMessages([
        'required' => 'A descrição é obrigatória.',
    ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tags')
                    ->schema([
                        Forms\Components\Select::make('tags')
                            ->label('Tags do verbete')
                            ->relationship('tags', 'name', fn ($query) => $query->isActive()->orderBy('name'))
                            ->helperText('Digite para buscar ou criar uma tag (Enter). Tags repetidas são reaproveitadas automaticamente.')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nova tag')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(fn (array $data) => Tag::findOrCreate(['name' => $data['name'], 'is_active' => true])->getKey()),
                    ]),

                Forms\Components\Section::make('Imagens')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Imagem')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
                            ->directory('images/team')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable(),
                        Forms\Components\FileUpload::make('background-image')
                            ->label('Imagem de Fundo')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
                            ->directory('images/team')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable(),
                        Forms\Components\TextInput::make('background-credit')
                            ->label('Crédito da Imagem de Fundo')
                            ->helperText('Ex: victor-garcia/unsplash')
                            ->placeholder('nome-fotografo/plataforma'),
                            Forms\Components\Select::make('video_id')
                            ->label('Vídeo')
                            ->relationship('video', 'title')
                            ->getOptionLabelFromRecordUsing(fn (Video $record): string => "{$record->title} ({$record->video_id})")
                            ->searchable(['title', 'video_id'])
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('video_id')
                                    ->label('ID do Vídeo (YouTube)')
                                    ->required()
                                    ->helperText('Ex: dQw4w9WgXcQ (apenas o ID, não a URL completa)'),
                                Forms\Components\TextInput::make('title')
                                    ->label('Título')
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Descrição')
                                    ->rows(3),
                                Forms\Components\Checkbox::make('is_featured')
                                    ->label('Vídeo em Destaque')
                                    ->default(true),
                                Forms\Components\Checkbox::make('is_active')
                                    ->label('Ativo')
                                    ->default(true),
                            ])
                            ->helperText('Selecione o vídeo que será usado para apresentar este verbete'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Verbete')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Endereço (URL)')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('video.title')
                    ->label('Vídeo Principal')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->video?->title)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),
                Tables\Filters\Filter::make('order')
                    ->form([
                        Forms\Components\Select::make('order_by')
                            ->label('Ordenar por')
                            ->placeholder('Ordem alfabética')
                            ->options([
                                'created_desc' => 'Últimos verbetes adicionados',
                                'updated_desc' => 'Atualizados recentemente',
                                'content_desc' => 'Últimos conteúdos acrescentados',
                                'posts_desc' => 'Últimas postagens dos responsáveis',
                            ]),
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['order_by'] ?? null) {
                            'created_desc' => $query->reorder('created_at', 'desc'),
                            'updated_desc' => $query->reorder('updated_at', 'desc'),
                            'content_desc' => $query->reorder()->orderByDesc(
                                \App\Models\Resource::select('created_at')->whereColumn('resources.subject_id', 'subjects.id')->latest()->limit(1)
                            ),
                            'posts_desc' => $query->reorder()->orderByDesc(
                                \App\Models\Post::select('posts.created_at')
                                    ->join('subject_user', 'subject_user.user_id', '=', 'posts.user_id')
                                    ->whereColumn('subject_user.subject_id', 'subjects.id')
                                    ->latest('posts.created_at')->limit(1)
                            ),
                            default => $query,
                        };
                    })
                    ->indicateUsing(fn (array $data): ?string => ($data['order_by'] ?? null) ? 'Ordenação personalizada' : null),
            ])
            ->actions([
Tables\Actions\EditAction::make()->label('Editar'),
Tables\Actions\ViewAction::make()->label('Visualizar'),

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
            Infolists\Components\Section::make('Verbete')->columns(2)->schema([
                Infolists\Components\TextEntry::make('name')->label('Nome')->copyable(),
                Infolists\Components\TextEntry::make('slug')->label('Endereço (URL)')->copyable(),
                Infolists\Components\IconEntry::make('is_active')->label('Ativo')->boolean(),
                Infolists\Components\TextEntry::make('video.video_id')->label('Vídeo (YouTube)')->copyable(),
                Infolists\Components\TextEntry::make('description')->label('Descrição')->columnSpanFull()->copyable(),
                Infolists\Components\ImageEntry::make('image')->label('Imagem')->disk('public'),
                Infolists\Components\TextEntry::make('background-credit')->label('Crédito da imagem de fundo')->copyable(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'view' => Pages\ViewSubject::route('/{record}'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}