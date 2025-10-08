<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Filament\Resources\VideoResource\RelationManagers;
use App\Models\Video;
use Filament\Forms;
use Filament\Forms\Form;
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
                Forms\Components\Section::make('Informações do Vídeo')
                    ->schema([
                        Forms\Components\Select::make('subject_id')
                            ->label('Assunto')
                            ->placeholder('Selecione um assunto')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload(),
                        
Forms\Components\TextInput::make('video_id')
    ->label('ID do Vídeo (YouTube)')
    ->required()
    ->helperText('Ex: dQw4w9WgXcQ (apenas o ID, não a URL completa)')
    ->validationMessages([
        'required' => 'O ID do vídeo é obrigatório.',
    ]),
                        

Forms\Components\TextInput::make('title')
    ->label('Título')
    ->maxLength(255)
    ->columnSpanFull()
    ->validationMessages([
        'max' => 'O título não pode ter mais que 255 caracteres.',
    ]),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Configurações')
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
                
                Forms\Components\Section::make('Autores')
                    ->schema([
                        Forms\Components\Select::make('users')
                            ->label('Autores do Vídeo')
                            ->placeholder('Selecione os autores')
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
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn (Video $record) => $record->title),
                
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Autores')
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
                    ->url(fn (Video $record) => "https://www.youtube.com/watch?v={$record->video_id}")
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-play'),
                
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