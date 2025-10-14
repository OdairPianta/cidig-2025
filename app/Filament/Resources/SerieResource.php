<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SerieResource\Pages;
use App\Filament\Resources\SerieResource\RelationManagers;
use App\Models\Resource as ResourceModel;
use App\Models\Serie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class SerieResource extends Resource
{
    protected static ?string $model = Serie::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $modelLabel = 'Série';

    protected static ?string $pluralModelLabel = 'Série';

    protected static ?string $navigationGroup = 'Conteúdos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
Forms\Components\TextInput::make('title')
    ->label('Título')
    ->columnSpanFull()
    ->required()
    ->validationMessages([
        'required' => 'O título é obrigatório.',
    ]),
Forms\Components\TextInput::make('slug')
    ->label('Slug')
    ->columnSpanFull()
    ->required()
    ->validationMessages([
        'required' => 'O slug é obrigatório.',
    ]),
Forms\Components\Textarea::make('description')
    ->label('Descrição')
    ->columnSpanFull()
    ->required()
    ->validationMessages([
        'required' => 'A descrição é obrigatória.',
    ]),
                Forms\Components\Select::make('users')
                    ->label('Responsáveis')
                    ->relationship('users', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Forms\Components\Checkbox::make('is_active')
                    ->label('Ativo'),
                Forms\Components\FileUpload::make('thumbnail')
                    ->label('Thumbnail')
                    ->acceptedFileTypes(['image/bmp','image/jpeg', 'image/x-png', 'image/png', 'image/gif'])
                    ->disk('public')
                    ->directory('resources')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                Forms\Components\FileUpload::make('thumbnail_novo')
                    ->label('Thumbnail Novo')
                    ->acceptedFileTypes(['image/bmp','image/jpeg', 'image/x-png', 'image/png', 'image/gif'])
                    ->disk('public')
                    ->directory('resources')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                Forms\Components\FileUpload::make('background_image')
                    ->label('Imagem de Fundo')
                    ->acceptedFileTypes(['image/bmp','image/jpeg', 'image/x-png', 'image/png', 'image/gif'])
                    ->disk('public')
                    ->directory('resources')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                Forms\Components\FileUpload::make('background_img')
                    ->label('Background Img')
                    ->acceptedFileTypes(['image/bmp','image/jpeg', 'image/x-png', 'image/png', 'image/gif'])
                    ->disk('public')
                    ->directory('resources')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                Forms\Components\TextInput::make('background_credit')
                    ->label('Crédito do Background'),
                Forms\Components\Select::make('videos')
                    ->label('Vídeos da Série')
                    ->placeholder('Selecione os vídeos')
                    ->relationship('videos', 'title')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->title . ($record->video_id ? " ({$record->video_id})" : ''))
                    ->searchable(['title', 'video_id'])
                    ->multiple()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->tooltip(fn (Serie $record) => $record->title)
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Responsáveis')
                    ->formatStateUsing(function ($record) {
                        return $record->users->pluck('name')->join(', ');
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\CheckboxColumn::make('is_active')
                    ->label('Ativo')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeries::route('/'),
            'create' => Pages\CreateSerie::route('/create'),
            'edit' => Pages\EditSerie::route('/{record}/edit'),
        ];
    }
        
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }
}
