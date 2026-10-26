<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Filament\Resources\VideoResource\RelationManagers;
use App\Models\Video;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $modelLabel = 'Vídeo';

    protected static ?string $pluralModelLabel = 'Vídeos';

    protected static ?string $navigationGroup = 'Conteúdos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sobre o vídeo (origem)')
                    ->description('Dados do vídeo original, independentes do uso na plataforma.')
                    ->schema([
Forms\Components\TextInput::make('video_id')
    ->label('Link ou ID do Vídeo (YouTube)')
    ->required()
    ->helperText('Cole o link completo do YouTube ou apenas o ID (ex: dQw4w9WgXcQ).')
    ->dehydrateStateUsing(fn (?string $state) => Video::extractYoutubeId($state) ?? $state)
    ->rules([
        fn (?Video $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
            $id = Video::extractYoutubeId($value);

            if ($id === null) {
                $fail('Link ou ID do YouTube inválido.');
                return;
            }

            $exists = Video::where('video_id', $id)
                ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                ->exists();

            if ($exists) {
                $fail('Este vídeo já está cadastrado.');
            }
        },
    ])
    ->validationMessages([
        'required' => 'O link ou ID do vídeo é obrigatório.',
    ]),
                        

Forms\Components\TextInput::make('source_author')
    ->label('Autoria')
    ->helperText('Pessoa física ou organização responsável pelo vídeo.')
    ->maxLength(255),

Forms\Components\TextInput::make('source_title')
    ->label('Título do vídeo')
    ->maxLength(255),

Forms\Components\TextInput::make('source_channel')
    ->label('Canal de publicação')
    ->maxLength(255),

Forms\Components\TextInput::make('source_year')
    ->label('Ano de publicação')
    ->numeric()
    ->minValue(1900)
    ->maxValue((int) date('Y') + 1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Uso na plataforma')
                    ->schema([
                        Forms\Components\Select::make('subject_id')
                            ->label('Verbete')
                            ->placeholder('Selecione um verbete')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload(),

Forms\Components\TextInput::make('title')
    ->label('Título na plataforma')
    ->maxLength(255)
    ->columnSpanFull()
    ->validationMessages([
        'max' => 'O título não pode ter mais que 255 caracteres.',
    ]),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição na plataforma')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Configurações de exibição')
                    ->schema([
                        Forms\Components\Checkbox::make('is_featured')
                            ->label('Vídeo em Destaque')
                            ->helperText('Marque se este vídeo deve aparecer em destaque'),
                        
                        Forms\Components\Checkbox::make('is_active')
                            ->label('Ativo')
                            ->default(true)
                            ->helperText('Marque para tornar o vídeo visível no site'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Responsáveis')
                    ->schema([
                        Forms\Components\Select::make('users')
                            ->label('Professores/pesquisadores vinculados')
                            ->placeholder('Selecione os professores ou pesquisadores')
                            ->relationship('users', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->getStateUsing(fn (Video $record) => $record->title ?: $record->source_title ?: $record->video_id)
                    ->searchable(['title', 'source_title', 'video_id'])
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn (Video $record) => $record->title ?: $record->source_title),

                Tables\Columns\TextColumn::make('source_author')
                    ->label('Autoria')
                    ->searchable()
                    ->placeholder('—')
                    ->limit(30),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Verbete')
                    ->placeholder('—')
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Vinculados')
                    ->badge()
                    ->separator(',')
                    ->limit(2),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destaque')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([ 
                
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Em Destaque'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativo'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Visualizar')
                    ->modalHeading(fn (Video $record) => $record->title ?: $record->source_title ?: 'Vídeo')
                    ->infolist([
                        Infolists\Components\TextEntry::make('source_author')->label('Autoria')->copyable()->placeholder('—'),
                        Infolists\Components\TextEntry::make('source_title')->label('Título do vídeo')->copyable()->placeholder('—'),
                        Infolists\Components\TextEntry::make('source_channel')->label('Canal de publicação')->copyable()->placeholder('—'),
                        Infolists\Components\TextEntry::make('source_year')->label('Ano de publicação')->copyable()->placeholder('—'),
                        Infolists\Components\TextEntry::make('title')->label('Título na plataforma')->copyable()->placeholder('—'),
                        Infolists\Components\TextEntry::make('video_id')
                            ->label('Link do vídeo')
                            ->formatStateUsing(fn (string $state) => "https://www.youtube.com/watch?v={$state}")
                            ->url(fn (Video $record) => "https://www.youtube.com/watch?v={$record->video_id}", true)
                            ->copyable(),
                        Infolists\Components\TextEntry::make('description')->label('Descrição na plataforma')->copyable()->placeholder('—'),
                        Infolists\Components\TextEntry::make('subject.name')->label('Verbete')->placeholder('—'),
                        Infolists\Components\TextEntry::make('users.name')->label('Professores/pesquisadores')->badge()->placeholder('—'),
                        Infolists\Components\TextEntry::make('createdBy.name')->label('Cadastrado por')->placeholder('—'),
                        Infolists\Components\IconEntry::make('is_featured')->label('Em destaque')->boolean(),
                        Infolists\Components\IconEntry::make('is_active')->label('Ativo')->boolean(),
                    ]),
                
                Tables\Actions\EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->label('Excluir')
                        ->hidden(fn (): bool => !auth()->user()->isAdmin()),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
        
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }
}