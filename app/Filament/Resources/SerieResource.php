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
    ->required()
    ->unique(ignoreRecord: true)
    ->alphaDash()
    ->validationMessages([
        'required' => 'O endereço (URL) é obrigatório.',
        'unique' => 'Já existe uma série com este endereço.',
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
                Forms\Components\TextInput::make('thumbnail_url')
                    ->label('Thumbnail a partir de um link')
                    ->helperText('Opcional: cole o link de uma imagem da internet (JPG, PNG, GIF ou WebP). Ela será baixada e preencherá o campo "Thumbnail" abaixo.')
                    ->url()
                    ->dehydrated(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, \Filament\Forms\Set $set) {
                        if (blank($state)) {
                            return;
                        }

                        try {
                            $path = app(\App\Services\ImageDownloader::class)->store($state, 'resources');
                            $set('thumbnail', [$path]);
                            $set('thumbnail_url', null);
                        } catch (\InvalidArgumentException $e) {
                            \Filament\Notifications\Notification::make()->danger()->title($e->getMessage())->send();
                        }
                    }),
                Forms\Components\FileUpload::make('thumbnail')
                    ->label('Thumbnail')
                    ->helperText('Formato recomendado: 1280 x 720 px (16:9), JPG ou PNG, até 10 MB.')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
                    ->disk(\App\Support\Uploads::disk())
                    ->directory('resources')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                Forms\Components\FileUpload::make('thumbnail_novo')
                    ->label('Thumbnail Novo')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
                    ->disk(\App\Support\Uploads::disk())
                    ->directory('resources')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                Forms\Components\FileUpload::make('background_image')
                    ->label('Imagem de Fundo')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
                    ->disk(\App\Support\Uploads::disk())
                    ->directory('resources')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                Forms\Components\FileUpload::make('background_img')
                    ->label('Background Img')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
                    ->disk(\App\Support\Uploads::disk())
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
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('video_id')
                            ->label('Link ou ID do Vídeo (YouTube)')
                            ->required()
                            ->helperText('Cole o link completo do YouTube ou apenas o ID.')
                            ->rules([
                                fn () => function (string $attribute, $value, \Closure $fail) {
                                    $id = Video::extractYoutubeId($value);

                                    if ($id === null) {
                                        $fail('Link ou ID do YouTube inválido.');
                                    } elseif (Video::where('video_id', $id)->exists()) {
                                        $fail('Este vídeo já está cadastrado. Selecione-o na lista.');
                                    }
                                },
                            ]),
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->maxLength(255),
                    ])
                    ->createOptionUsing(fn (array $data) => Video::create([
                        'video_id' => $data['video_id'],
                        'title' => $data['title'] ?? null,
                    ])->getKey()),
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

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Websérie')->columns(2)->schema([
                Infolists\Components\TextEntry::make('title')->label('Título')->copyable(),
                Infolists\Components\TextEntry::make('slug')->label('Endereço (URL)')->copyable(),
                Infolists\Components\IconEntry::make('is_active')->label('Ativo')->boolean(),
                Infolists\Components\TextEntry::make('users.name')->label('Responsáveis')->badge(),
                Infolists\Components\TextEntry::make('description')->label('Descrição')->columnSpanFull()->copyable(),
                Infolists\Components\TextEntry::make('videos.video_id')->label('Vídeos (YouTube)')->badge()->copyable(),
                Infolists\Components\ImageEntry::make('thumbnail')->label('Thumbnail')->disk(\App\Support\Uploads::disk()),
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
        return auth()->check() && auth()->user()->isAdmin();
    }
}
