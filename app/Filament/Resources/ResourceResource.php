<?php

namespace App\Filament\Resources;

use App\Filament\FilamentResourceBase;
use App\Filament\Resources\ResourceResource\Pages;
use App\Models\Resource as ResourceModel;
use App\Models\Author;
use App\Models\Tag;
use App\Models\User;
use Filament\Forms;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Storage;

class ResourceResource extends FilamentResourceBase
{
    protected static ?string $model = ResourceModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $modelLabel = 'Conteúdo de Linha de Pesquisa';

    protected static ?string $pluralModelLabel = 'Conteúdos de Linhas de Pesquisa';

    protected static ?string $navigationGroup = 'Conteúdos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conteúdo')
                    ->columns()
                    ->collapsible()
                    ->schema([
 Forms\Components\Select::make('subject_id')
    ->label('Assunto')
    ->placeholder('Selecione um assunto')
    ->relationship('subject', 'name')
    ->options(fn () => auth()->user()->isAdmin()
        ? \App\Models\Subject::query()->orderBy('name')->pluck('name', 'id')
        : auth()->user()->subjects()->orderBy('name')->pluck('name', 'id'))
    ->searchable()
    ->helperText('Linha de pesquisa à qual este conteúdo pertence. Para criar uma nova linha de pesquisa, use o menu "Linhas de Pesquisa".')
    ->required()
    ->validationMessages([
        'required' => 'O assunto é obrigatório.',
    ]),
                        Forms\Components\Select::make('format_id')
                            ->label('Tipo de Conteúdo')
                            ->placeholder('Selecione um tipo de conteúdo')
                            ->options(ResourceModel::FORMAT_TYPES)
                            ->columns()
                            ->required(),
                        Forms\Components\Select::make('tags')
                            ->label('Relações (Tags Relacionadas)')
                            ->placeholder('Selecione as tags relacionadas')
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
                            ->createOptionUsing(fn (array $data) => Tag::findOrCreate(['name' => $data['name'], 'is_active' => true])->getKey())
                            ->columns()
                            ->required(),
Forms\Components\TextInput::make('title')
    ->label('Título')
    ->columnSpanFull()
    ->required()
    ->validationMessages([
        'required' => 'O título é obrigatório.',
    ]),
Forms\Components\Textarea::make('description')
    ->label('Descrição')
    ->required()
    ->validationMessages([
        'required' => 'A descrição é obrigatória.',
    ]),
Forms\Components\TextInput::make('source')
    ->label('Fonte (Link)')
    ->required()
    ->validationMessages([
        'required' => 'A fonte é obrigatória.',
    ]),
                        Forms\Components\FileUpload::make('uploaded_file')
                            ->label('Arquivo (em PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk(\App\Support\Uploads::disk())
                            ->directory('resources')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
                            ->helperText(fn ($record) => $record && $record->uploadedFile
                                ? new HtmlString('Arquivo atual: <a href="' . \App\Support\Uploads::storage()->url($record->uploadedFile->path) . '" target="_blank">' . $record->uploadedFile->original_name . '</a>')
                                : 'No file uploaded yet.'
                            ),
                        Forms\Components\TextInput::make('coverage')
                            ->label('Cobertura (Tempo/Espacial)'),
                        Forms\Components\Checkbox::make('published_at')
                            ->label('Publicar Conteúdo'),
                    ]),
                Forms\Components\Section::make('Propriedade Intelectual')
                    ->columns()
                    ->collapsible()
                    ->schema([
Forms\Components\Select::make('authors')
    ->label('Autoria')
    ->placeholder('Selecione ou crie os autores')
    ->relationship('authors', 'name')
    ->helperText('Digite para buscar ou criar um autor. Autores repetidos são reaproveitados automaticamente.')
    ->multiple()
    ->searchable()
    ->preload()
    ->createOptionForm([
        Forms\Components\TextInput::make('name')
            ->label('Novo autor')
            ->required()
            ->maxLength(255),
    ])
    ->createOptionUsing(fn (array $data) => Author::findOrCreateByName($data['name'])->getKey())
    ->required()
    ->validationMessages([
        'required' => 'Informe ao menos um autor.',
    ]),
Forms\Components\TextInput::make('publisher')
    ->label('Editora ou Canal de Publicação')
    ->required()
    ->validationMessages([
        'required' => 'A editora é obrigatória.',
    ]),
                        Forms\Components\TextInput::make('contributor')
                            ->label('Co-Autor ou Contribuidor'),
                        Forms\Components\TextInput::make('copy_rights')
                            ->label('Direitos'),
                    ]),
                Forms\Components\Section::make('Instanciação')
                    ->columns()
                    ->collapsible()
                    ->schema([
                        Forms\Components\DatePicker::make('original_date')
                            ->label('Data'),
                        Forms\Components\TextInput::make('format')
                            ->label('Formato'),
                        Forms\Components\TextInput::make('identifier')
                            ->label('Identificador'),
Forms\Components\TextInput::make('language')
    ->label('Idioma do conteúdo')
    ->required()
    ->validationMessages([
        'required' => 'O idioma é obrigatório.',
    ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->tooltip(fn (ResourceModel $record) => $record->title)
                    ->icon(fn (ResourceModel $record) => $record->getNewFormatIcon())
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('id')
                    ->label('Status')
                    ->icon(fn (ResourceModel $record) => $record->published_at ? 'heroicon-o-check-circle' : 'heroicon-o-pencil')
                    ->color(fn (ResourceModel $record) => $record->published_at ? 'success' : 'warning')
                    ->tooltip(fn (ResourceModel $record) => $record->published_at ? 'Publicado' : 'Rasgunho'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('published_at')
                    ->label('Publicado')
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->nullable(),
                Tables\Filters\SelectFilter::make('created_by')
                    ->label('Criado por')
                    ->placeholder('Todos')
                    ->multiple()
                    ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
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
            Infolists\Components\Section::make('Conteúdo')->columns(2)->schema([
                Infolists\Components\TextEntry::make('title')->label('Título')->copyable()->columnSpanFull(),
                Infolists\Components\TextEntry::make('subject.name')->label('Linha de Pesquisa')->copyable(),
                Infolists\Components\TextEntry::make('format_id')->label('Tipo de conteúdo')
                    ->formatStateUsing(fn ($state) => ResourceModel::FORMAT_TYPES[$state] ?? '—'),
                Infolists\Components\TextEntry::make('tags.name')->label('Tags')->badge()->placeholder('—'),
                Infolists\Components\TextEntry::make('published_at')->label('Publicado em')->dateTime('d/m/Y H:i')->placeholder('Não publicado'),
                Infolists\Components\TextEntry::make('description')->label('Descrição')->copyable()->columnSpanFull(),
                Infolists\Components\TextEntry::make('source')->label('Fonte (link)')->copyable()
                    ->url(fn (ResourceModel $record) => filled($record->source) ? $record->getSourceLink() : null, true)
                    ->columnSpanFull(),
                Infolists\Components\TextEntry::make('uploadedFile.original_name')->label('Arquivo (PDF)')
                    ->url(fn (ResourceModel $record) => $record->uploadedFile ? \App\Support\Uploads::storage()->url($record->uploadedFile->path) : null, true)
                    ->placeholder('Nenhum arquivo enviado'),
                Infolists\Components\TextEntry::make('coverage')->label('Cobertura (tempo/espacial)')->copyable()->placeholder('—'),
            ]),
            Infolists\Components\Section::make('Propriedade Intelectual')->columns(2)->schema([
                Infolists\Components\TextEntry::make('author')->label('Autor')->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('publisher')->label('Editora ou canal de publicação')->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('contributor')->label('Co-autor ou contribuidor')->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('copy_rights')->label('Direitos')->copyable()->placeholder('—'),
            ]),
            Infolists\Components\Section::make('Instanciação')->columns(2)->schema([
                Infolists\Components\TextEntry::make('original_date')->label('Data')->date('d/m/Y')->placeholder('—'),
                Infolists\Components\TextEntry::make('format')->label('Formato')->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('identifier')->label('Identificador')->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('language')->label('Idioma do conteúdo')->copyable()->placeholder('—'),
            ]),
            Infolists\Components\Section::make('Registro')->columns(3)->collapsed()->schema([
                Infolists\Components\TextEntry::make('user.name')->label('Criado por')->placeholder('—'),
                Infolists\Components\TextEntry::make('created_at')->label('Criado em')->dateTime('d/m/Y H:i'),
                Infolists\Components\TextEntry::make('updated_at')->label('Atualizado em')->dateTime('d/m/Y H:i'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResources::route('/'),
            'create' => Pages\CreateResource::route('/create'),
            'view' => Pages\ViewResource::route('/{record}'),
            'edit' => Pages\EditResource::route('/{record}/edit'),
        ];
    }


    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
