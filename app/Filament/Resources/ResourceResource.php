<?php

namespace App\Filament\Resources;

use App\Filament\FilamentResourceBase;
use App\Filament\Resources\ResourceResource\Pages;
use App\Filament\Resources\ResourceResource\RelationManagers;
use App\Filament\Resources\SubjectResource\RelationManagers\ResourcesRelationManager;
use App\Models\Resource as ResourceModel;
use App\Models\Subject;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ResourceResource extends FilamentResourceBase
{
    protected static ?string $model = ResourceModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $modelLabel = 'Conteúdos';


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
                            ->options(auth()->user()->subjects()->pluck('name', 'id'))
                            ->relationship('subject', 'name')
                            ->columns()
                            ->required(),
                        Forms\Components\Select::make('format_id')
                            ->label('Tipo de Conteúdo')
                            ->placeholder('Selecione um tipo de conteúdo')
                            ->options(ResourceModel::FORMAT_TYPES)
                            ->columns()
                            ->required(),
                        Forms\Components\Select::make('tags')
                            ->label('Relações (Tags Relacionadas)')
                            ->placeholder('Selecione as tags relacionadas')
                            ->multiple()
                            ->relationship('tags', 'name')
                            ->options(Tag::query()->isActive()->orderBy('name')->pluck('name', 'id'))
                            ->columns()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->columnSpanFull()
                            ->rows(5)
                            ->required(),
                        Forms\Components\TextInput::make('source')
                            ->label('Fonte (Link)'),
                        Forms\Components\FileUpload::make('source_file')
                            ->label('Arquivo (em PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->storeFiles()
                            ->preserveFilenames()
                            ->afterStateHydrated(function ($component, $state) {
                                // Optional: preload existing file names if editing
                            })
                            ->dehydrated(false)
                            ->live()
                            ->downloadable(),
                        Forms\Components\TextInput::make('coverage')
                            ->label('Cobertura (Tempo/Espacial)'),
                        Forms\Components\Checkbox::make('published_at')
                            ->label('Publicar Conteúdo'),
                    ]),
                Forms\Components\Section::make('Propriedade Intelectual')
                    ->columns()
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('author')
                            ->label('Autor')
                            ->required(),
                        Forms\Components\TextInput::make('publisher')
                            ->label('Editora ou Canal de Publicação')
                            ->required(),
                        Forms\Components\TextInput::make('contributor')
                            ->label('Co-Autor ou Contribuidor'),
                        Forms\Components\TextInput::make('Direitos')
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
                            ->label('Idioma do conteúdo'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Criado em')->dateTime()->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('published_at')->label('Publicado em')->dateTime()->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\ViewAction::make()->label('Visualizar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListResources::route('/'),
            'create' => Pages\CreateResource::route('/create'),
            'show' => Pages\ViewResource::route('/{record}'),
            'edit' => Pages\EditResource::route('/{record}/edit'),
        ];
    }


    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
