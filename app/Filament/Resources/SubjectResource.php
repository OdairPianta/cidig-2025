<?php

namespace App\Filament\Resources;

use App\Filament\FilamentResourceBase;
use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use App\Models\Video;
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
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Título'),
                        Forms\Components\TextInput::make('slug'),
                        Forms\Components\Checkbox::make('is_active')
                            ->label('Ativo'),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(5)
                            ->autosize()
                            ->columnSpanFull()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Imagens')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Imagem')
                            ->image()
                            ->acceptedFileTypes(['image/bmp','image/jpeg', 'image/x-png', 'image/png', 'image/gif'])
                            ->directory('images/team')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable(),
                        Forms\Components\FileUpload::make('background-image')
                            ->label('Imagem de Fundo')
                            ->image()
                            ->acceptedFileTypes(['image/bmp','image/jpeg', 'image/x-png', 'image/png', 'image/gif'])
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
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Verbete')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('video.title')
                    ->label('Vídeo Principal')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->video?->title),
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}