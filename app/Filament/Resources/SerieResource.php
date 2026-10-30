<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SerieResource\Pages;
use App\Filament\Resources\SerieResource\RelationManagers;
use App\Models\Resource as ResourceModel;
use App\Models\Serie;
use App\Models\Video;
use Filament\Forms;
use Filament\Infolists;
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
    ->label('Endereço (URL)')
    ->helperText('Parte final do link da série, ex.: "minha-serie" → /webseries/minha-serie. Use apenas letras minúsculas, números e hífens.')
    ->columnSpanFull()
    ->live(debounce: 500)
    ->required()
    ->unique(ignoreRecord: true)
    ->alphaDash()
    ->validationMessages([
        'required' => 'O endereço (URL) é obrigatório.',
        'unique' => 'Já existe uma série com este endereço.',
    ]),
Forms\Components\Placeholder::make('slug_preview')
    ->label('Como o link vai ficar')
    ->columnSpanFull()
    ->content(fn (Forms\Get $get) => \App\Support\UrlPreview::html('/webseries/', $get('slug'), 'minha-serie')),
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
                    ->preload()
                    ->default(fn () => [auth()->id()]),
                Forms\Components\Checkbox::make('is_active')
                    ->label('Ativo'),
                Forms\Components\FileUpload::make('thumbnail')
                    ->label('Miniatura')
                    ->helperText('Arraste a imagem para cá ou clique para escolher. Formato recomendado: 1280 x 720 px (16:9), JPG ou PNG, até 10 MB. Imagens maiores são reduzidas automaticamente.')
                    ->image()
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1280')
                    ->imageResizeTargetHeight('720')
                    ->imageResizeUpscale(false)
                    ->imageEditor()
                    ->imageEditorAspectRatios(['16:9', '4:3'])
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
                    ->disk(\App\Support\Uploads::disk())
                    ->directory('resources')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                Forms\Components\FileUpload::make('background_image')
                    ->label('Imagem de Fundo')
                    ->helperText('Formato recomendado: 1920 x 1080 px (16:9), JPG ou PNG, até 10 MB. Imagens maiores são reduzidas automaticamente.')
                    ->image()
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->imageResizeUpscale(false)
                    ->imageEditor()
                    ->imageEditorAspectRatios(['16:9', '4:3'])
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
                    ->disk(\App\Support\Uploads::disk())
                    ->directory('resources')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                Forms\Components\TextInput::make('background_credit')
                    ->label('Crédito da Imagem de Fundo'),
                Forms\Components\Repeater::make('episodes')
                    ->label('Vídeos da Série')
                    ->helperText('Cada vídeo é um bloco com título e descrição. Use a descrição para contextualizar o episódio e indicar o professor responsável, quando houver.')
                    ->addActionLabel('Adicionar vídeo')
                    ->defaultItems(0)
                    ->columnSpanFull()
                    ->collapsible()
                    ->reorderableWithButtons()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->dehydrated(false)
                    ->saveRelationshipsUsing(fn (Serie $record, ?array $state) => static::syncEpisodes($record, $state ?? []))
                    ->schema([
                        Forms\Components\TextInput::make('video')
                            ->label('Link ou ID do Vídeo (YouTube)')
                            ->required()
                            ->helperText('Cole o link completo do YouTube ou apenas o ID.')
                            ->rules([
                                fn () => function (string $attribute, $value, \Closure $fail) {
                                    if (Video::extractYoutubeId($value) === null) {
                                        $fail('Link ou ID do YouTube inválido.');
                                    }
                                },
                            ])
                            ->validationMessages(['required' => 'Informe o link ou o ID do vídeo.']),
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->validationMessages(['required' => 'Informe o título do vídeo.']),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    /** @return array<int, array{video: string, title: ?string, description: ?string}> */
    public static function episodesFor(Serie $serie): array
    {
        return $serie->videos->map(fn ($video) => [
            'video' => $video->video_id,
            'title' => $video->pivot->title ?: $video->title,
            'description' => $video->pivot->description ?: $video->description,
        ])->all();
    }

    /**
     * Attaches the form's video blocks in order, creating the video record the first time a
     * YouTube id is used so the same link can never trigger a duplicate-key error.
     *
     * @param  array<int, array{video: string, title: string, description: ?string}>  $blocks
     */
    public static function syncEpisodes(Serie $serie, array $blocks): void
    {
        $sync = [];

        foreach (array_values($blocks) as $position => $block) {
            $youtubeId = Video::extractYoutubeId($block['video'] ?? null);
            if ($youtubeId === null) {
                continue;
            }

            $video = Video::firstOrCreate(
                ['video_id' => $youtubeId],
                ['title' => $block['title'] ?? null, 'description' => $block['description'] ?? null, 'is_active' => true, 'created_by' => auth()->id()],
            );

            $sync[$video->getKey()] = [
                'title' => $block['title'] ?? null,
                'description' => $block['description'] ?? null,
                'position' => $position + 1,
            ];
        }

        $serie->videos()->sync($sync);
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
                Tables\Actions\ViewAction::make()->label('Visualizar')->url(null)->modalWidth('4xl'),
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
            Infolists\Components\Section::make('Websérie')->columns(2)->schema([
                Infolists\Components\TextEntry::make('title')->label('Título')->copyable(),
                Infolists\Components\TextEntry::make('slug')->label('Endereço (URL)')->copyable(),
                Infolists\Components\IconEntry::make('is_active')->label('Ativo')->boolean(),
                Infolists\Components\TextEntry::make('users.name')->label('Responsáveis')->badge(),
                Infolists\Components\TextEntry::make('description')->label('Descrição')->columnSpanFull()->copyable(),
                Infolists\Components\RepeatableEntry::make('videos')->label('Vídeos')->columnSpanFull()->schema([
                    Infolists\Components\TextEntry::make('pivot.title')->label('Título')->copyable(),
                    Infolists\Components\TextEntry::make('video_id')->label('YouTube')->copyable(),
                    Infolists\Components\TextEntry::make('pivot.description')->label('Descrição')->columnSpanFull()->placeholder('—'),
                ])->columns(2),
                Infolists\Components\ImageEntry::make('thumbnail')->label('Miniatura')->disk(\App\Support\Uploads::disk()),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeries::route('/'),
            'create' => Pages\CreateSerie::route('/create'),
            'view' => Pages\ViewSerie::route('/{record}'),
            'edit' => Pages\EditSerie::route('/{record}/edit'),
        ];
    }
        
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    /**
     * Professors only see the series they are responsible for.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->isAdmin()) {
            return $query;
        }

        return $query->whereHas('users', fn (Builder $users) => $users->whereKey(auth()->id()));
    }
}
