<?php

namespace App\Filament\Resources;

use App\Filament\FilamentResourceBase;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Storage;

class PostResource extends FilamentResourceBase
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $modelLabel = 'Posts';

    protected static ?string $navigationGroup = 'Conteúdos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conteúdo do Post')
                    ->columns()
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->columnSpanFull()
                            ->helperText('Deixe vazio para gerar automaticamente'),
                        Forms\Components\RichEditor::make('content')
                            ->label('Conteúdo')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Imagem Destacada')
                            ->acceptedFileTypes(['image/bmp','image/jpeg', 'image/x-png', 'image/png', 'image/gif'])
                            ->disk('public')
                            ->directory('posts')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable(),
                        Forms\Components\Checkbox::make('is_published')
                            ->label('Publicar Post')
                            ->helperText('Marque para publicar o post imediatamente'),
                    ]),
                Forms\Components\Section::make('Configurações')
                    ->columns()
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Autor')
                            ->placeholder('Selecione o autor')
                            ->relationship('user', 'name')
                            ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                            ->default(auth()->id())
                            ->required(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Data de Publicação')
                            ->helperText('Deixe vazio para usar a data atual'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->tooltip(fn (Post $record) => $record->title)
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Autor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Status')
                    ->icon(fn (Post $record) => $record->is_published ? 'heroicon-o-check-circle' : 'heroicon-o-pencil')
                    ->color(fn (Post $record) => $record->is_published ? 'success' : 'warning')
                    ->tooltip(fn (Post $record) => $record->is_published ? 'Publicado' : 'Rascunho'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publicado')
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->nullable(),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Autor')
                    ->placeholder('Todos')
                    ->multiple()
                    ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Visualizar'),
                Tables\Actions\EditAction::make()->label('Editar'),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'show' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}