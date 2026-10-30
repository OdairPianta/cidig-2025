<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $modelLabel = 'Página';

    protected static ?string $pluralModelLabel = 'Páginas do site';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Página')->columns(2)->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Forms\Set $set, ?string $state, string $operation) {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                Forms\Components\TextInput::make('slug')
                    ->label('Endereço da página')
                    ->helperText('Parte final do endereço: o site fica em /p/endereco. Use letras minúsculas e hífens.')
                    ->required()
                    ->maxLength(120)
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->prefix('/p/'),
                Forms\Components\Select::make('template')
                    ->label('Modelo da página')
                    ->options(Page::TEMPLATES)
                    ->default(Page::TEMPLATE_TEXT)
                    ->required()
                    ->live()
                    ->helperText('O modelo define quais campos aparecem e como a página é exibida.'),
                Forms\Components\Toggle::make('is_published')
                    ->label('Publicada')
                    ->helperText('Páginas não publicadas não abrem no site e saem do menu.')
                    ->default(false)
                    ->inline(false),
                Forms\Components\Textarea::make('summary')
                    ->label('Resumo')
                    ->helperText('Aparece logo abaixo do título.')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\MarkdownEditor::make('body')
                    ->label(fn (Get $get) => $get('template') === Page::TEMPLATE_TEXT ? 'Conteúdo' : 'Texto de apresentação (opcional)')
                    ->helperText('Escreva em Markdown (títulos com #, **negrito**, [texto](link), ![descrição](endereço da imagem)).')
                    ->toolbarButtons(['bold', 'italic', 'strike', 'link', 'heading', 'bulletList', 'orderedList', 'blockquote', 'attachFiles', 'undo', 'redo'])
                    ->fileAttachmentsDisk(\App\Support\Uploads::disk())
                    ->fileAttachmentsDirectory('pages/body')
                    ->fileAttachmentsVisibility('public')
                    ->required(fn (Get $get) => $get('template') === Page::TEMPLATE_TEXT)
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Itens da página')
                ->visible(fn (Get $get) => in_array($get('template'), [Page::TEMPLATE_PUBLICATIONS, Page::TEMPLATE_CASES, Page::TEMPLATE_OBSERVATORY], true))
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->label('')
                        ->orderColumn('position')
                        ->defaultItems(0)
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->collapsed()
                        ->cloneable()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->addActionLabel('Adicionar item')
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('Título')->required()->maxLength(255)->columnSpanFull(),
                            Forms\Components\TextInput::make('authors')
                                ->label('Autoria')
                                ->maxLength(255)
                                ->visible(fn (Get $get) => $get('../../template') === Page::TEMPLATE_PUBLICATIONS),
                            Forms\Components\TextInput::make('year')
                                ->label('Ano')
                                ->numeric()->minValue(1900)->maxValue(2100),
                            Forms\Components\TextInput::make('category')
                                ->label(fn (Get $get) => Page::CATEGORY_LABELS[$get('../../template')] ?? 'Categoria')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('url')
                                ->label('Link')
                                ->helperText('Endereço do texto completo, DOI ou página do estudo.')
                                ->url()->maxLength(2048),
                            Forms\Components\Textarea::make('description')->label('Descrição')->rows(3)->columnSpanFull(),
                            Forms\Components\FileUpload::make('image')
                                ->label('Imagem')
                                ->helperText('Capa ou ilustração (JPG ou PNG). Opcional.')
                                ->image()
                                ->disk(\App\Support\Uploads::disk())
                                ->directory('pages')
                                ->visibility('public')
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('template')->label('Modelo')->formatStateUsing(fn (string $state) => Page::TEMPLATES[$state] ?? $state)->badge(),
                Tables\Columns\TextColumn::make('slug')->label('Endereço')->prefix('/p/'),
                Tables\Columns\IconColumn::make('is_published')->label('Publicada')->boolean(),
            ])
            ->defaultSort('title')
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\Action::make('open')
                    ->label('Ver no site')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Page $record) => $record->url, true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }
}
