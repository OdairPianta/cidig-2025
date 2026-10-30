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

    protected static string $ownerColumn = 'user_id';

    protected static ?string $modelLabel = 'Postagem';

    protected static ?string $pluralModelLabel = 'Postagens';

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
    ->live(onBlur: true)
    ->required()
    ->validationMessages([
        'required' => 'O título do post é obrigatório.',
    ]),
                        Forms\Components\TextInput::make('slug')
                            ->label('Endereço (URL)')
                            ->columnSpanFull()
                            ->live(debounce: 500)
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText('Parte final do link da postagem (ex.: "meu-primeiro-post"). Deixe vazio para gerar automaticamente a partir do título.')
                            ->validationMessages([
                                'unique' => 'Já existe uma postagem com este endereço.',
                            ]),
                        Forms\Components\Placeholder::make('slug_preview')
                            ->label('Como o link vai ficar')
                            ->columnSpanFull()
                            ->content(function (Forms\Get $get) {
                                $author = User::find($get('user_id'));
                                $prefix = '/' . (\Illuminate\Support\Str::slug($author?->name ?? '') ?: 'autor') . '/';
                                $slug = \Illuminate\Support\Str::slug((string) ($get('slug') ?: $get('title')));

                                return \App\Support\UrlPreview::html($prefix, $slug, 'gerado-a-partir-do-titulo');
                            }),
Forms\Components\Tabs::make('Conteúdo')
    ->columnSpanFull()
    ->tabs([
        Forms\Components\Tabs\Tab::make('Escrever')
            ->icon('heroicon-o-pencil-square')
            ->schema([
                Forms\Components\MarkdownEditor::make('content')
                    ->label('Conteúdo')
                    ->helperText('Escreva em Markdown (títulos com #, **negrito**, [texto](link), ![descrição](endereço da imagem)). Para incorporar um vídeo, cole o código <iframe> do YouTube ou do Vimeo. Veja o resultado na aba "Pré-visualizar".')
                    ->minHeight('24rem')
                    ->toolbarButtons(['bold', 'italic', 'strike', 'link', 'heading', 'bulletList', 'orderedList', 'blockquote', 'codeBlock', 'attachFiles', 'undo', 'redo'])
                    ->fileAttachmentsDisk(\App\Support\Uploads::disk())
                    ->fileAttachmentsDirectory('posts/body')
                    ->fileAttachmentsVisibility('public')
                    ->live(onBlur: true)
                    ->required()
                    ->validationMessages([
                        'required' => 'O conteúdo do post é obrigatório.',
                    ]),
            ]),
        Forms\Components\Tabs\Tab::make('Pré-visualizar')
            ->icon('heroicon-o-eye')
            ->schema([
                Forms\Components\Placeholder::make('preview')
                    ->hiddenLabel()
                    ->content(fn (Forms\Get $get) => new \Illuminate\Support\HtmlString(
                        '<div class="prose max-w-none dark:prose-invert">' .
                        (filled($get('content')) ? Post::renderContent($get('content')) : '<p><em>Nada para mostrar ainda. Escreva o texto na aba "Escrever".</em></p>') .
                        '</div>'
                    )),
            ]),
    ]),
Forms\Components\FileUpload::make('image_url')
    ->label('Imagem Destacada')
    ->helperText('Tamanho recomendado: 1200 x 630 px (proporção 1,91:1), JPG, PNG ou WebP, até 10 MB. A imagem é exibida com altura máxima de 400 px, sem distorção.')
    ->image()
    ->imageEditor()
    ->imageEditorAspectRatios(['1.91:1', '16:9', '4:3'])
    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
    ->disk(\App\Support\Uploads::disk())
    ->directory('posts')
    ->visibility('public')
    ->preserveFilenames()
    ->downloadable()
    ->openable()
    ->validationMessages([
        'image' => 'O arquivo deve ser uma imagem válida.',
    ]),
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
                            ->live()
                            ->required()
                                ->validationMessages([
        'required' => 'O autor do post é obrigatório.',
    ]),
                        Forms\Components\TagsInput::make('author_names')
                            ->label('Autoria (opcional)')
                            ->helperText('Para dar crédito a mais de uma pessoa. Digite o nome e pressione Enter (ou ";") para confirmar cada autor; nomes já cadastrados aparecem como sugestão.')
                            ->placeholder('Nome do autor')
                            ->splitKeys([';'])
                            ->suggestions(fn () => \App\Models\Author::query()->orderBy('name')->limit(200)->pluck('name')->all())
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Data de Publicação')
                            ->helperText('Deixe vazio para usar a data atual. Uma data futura agenda a publicação: o post só aparece no site a partir dela.'),
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
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Status')
                    ->icon(fn (Post $record) => $record->is_published ? 'heroicon-o-check-circle' : 'heroicon-o-pencil')
                    ->color(fn (Post $record) => $record->is_published ? 'success' : 'warning')
                    ->tooltip(fn (Post $record) => $record->is_published ? 'Publicado' : 'Rascunho'),
                Tables\Columns\TextColumn::make('archived_at')
                    ->label('Arquivado em')
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('archived')
                    ->label('Arquivadas')
                    ->placeholder('Ativas')
                    ->trueLabel('Arquivadas')
                    ->falseLabel('Ativas')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('archived_at'),
                        false: fn ($query) => $query->whereNull('archived_at'),
                        blank: fn ($query) => $query->whereNull('archived_at'),
                    ),
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
                Tables\Actions\ViewAction::make()->label('Visualizar')->url(null)->modalWidth('4xl'),
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\Action::make('publicView')
                    ->label('Ver no site')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Post $record) => $record->public_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Post $record) => $record->is_published && ! $record->isArchived() && $record->public_url),
                Tables\Actions\Action::make('archive')
                    ->label('Arquivar')
                    ->icon('heroicon-o-archive-box')
                    ->requiresConfirmation()
                    ->modalDescription('A postagem deixará de aparecer no site, mas poderá ser restaurada depois.')
                    ->visible(fn (Post $record) => ! $record->isArchived())
                    ->action(fn (Post $record) => $record->update(['archived_at' => now()])),
                Tables\Actions\Action::make('unarchive')
                    ->label('Desarquivar')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->visible(fn (Post $record) => $record->isArchived())
                    ->action(fn (Post $record) => $record->update(['archived_at' => null])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->label('Excluir')
                    ->hidden(fn (): bool => !auth()->user()->isAdmin()),
                ]),
            ]);
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist->schema([
            \Filament\Infolists\Components\Section::make('Postagem')->columns(2)->schema([
                \Filament\Infolists\Components\TextEntry::make('title')->label('Título')->copyable()->columnSpanFull(),
                \Filament\Infolists\Components\TextEntry::make('user.name')->label('Autor'),
                \Filament\Infolists\Components\TextEntry::make('public_url')->label('Link público')->copyable()->url(fn (Post $record) => $record->public_url, true),
                \Filament\Infolists\Components\TextEntry::make('published_at')->label('Publicado em')->dateTime(),
                \Filament\Infolists\Components\TextEntry::make('archived_at')->label('Arquivado em')->dateTime()->placeholder('—'),
                \Filament\Infolists\Components\ImageEntry::make('image_url')->label('Imagem destacada')->disk(\App\Support\Uploads::disk()),
                \Filament\Infolists\Components\TextEntry::make('content')->label('Conteúdo')->html()->columnSpanFull(),
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
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}