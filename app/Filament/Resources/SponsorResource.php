<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SponsorResource\Pages;
use App\Filament\Resources\SponsorResource\RelationManagers;
use App\Models\Sponsor;
use Filament\Forms;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SponsorResource extends Resource
{
    protected static ?string $model = Sponsor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Parceiro';

    protected static ?string $pluralModelLabel = 'Parceiros';

    protected static ?string $navigationGroup = 'Conteúdos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Parceiro')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'O nome do patrocinador é obrigatório.',
                                'max' => 'O nome não pode ter mais que 255 caracteres.',
                            ]),
                        
                        Forms\Components\Repeater::make('logos')
                            ->label('Logos')
                            ->relationship('logos')
                            ->helperText('Envie várias versões do logo (ex.: fundo claro, fundo escuro, horizontal) e marque qual está "em uso" no site. Orientações: PNG com fundo transparente, formato horizontal de aproximadamente 400 x 200 px, até 10 MB.')
                            ->addActionLabel('Adicionar versão do logo')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => ($state['label'] ?? null) ?: 'Logo')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->label('Descrição da versão')
                                    ->placeholder('Ex.: Fundo escuro, Horizontal, Versão 2024')
                                    ->maxLength(255)
                                    ->live(onBlur: true),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Em uso no site')
                                    ->helperText('Somente uma versão fica em uso.')
                                    ->inline(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get, Forms\Components\Component $component) {
                                        if (! $state) {
                                            return;
                                        }

                                        $current = $component->getContainer()->getStatePath(isAbsolute: false);
                                        foreach (array_keys($get('../../logos') ?? []) as $key) {
                                            if ($key !== $current) {
                                                $set("../../logos.{$key}.is_active", false);
                                            }
                                        }
                                    }),
                                Forms\Components\FileUpload::make('path')
                                    ->label('Arquivo')
                                    ->required()
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
                                    ->disk(\App\Support\Uploads::disk())
                                    ->directory('sponsors')
                                    ->visibility('public')
                                    ->preserveFilenames()
                                    ->downloadable()
                                    ->openable()
                                    ->columnSpanFull()
                                    ->validationMessages([
                                        'required' => 'Envie o arquivo do logo.',
                                        'image' => 'O arquivo deve ser uma imagem válida.',
                                    ]),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->tooltip(fn (Sponsor $record) => $record->name)
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Visualizar'),
                Tables\Actions\EditAction::make(),
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

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Parceiro')->columns(2)->schema([
                Infolists\Components\TextEntry::make('name')->label('Nome')->copyable(),
                Infolists\Components\ImageEntry::make('image')->label('Logo em uso')->disk(\App\Support\Uploads::disk()),
                Infolists\Components\RepeatableEntry::make('logos')->label('Versões do logo')->columnSpanFull()->schema([
                    Infolists\Components\ImageEntry::make('url')->label('')->height(60),
                    Infolists\Components\TextEntry::make('label')->label('Descrição')->placeholder('—'),
                    Infolists\Components\IconEntry::make('is_active')->label('Em uso')->boolean(),
                ])->columns(3),
                Infolists\Components\TextEntry::make('created_at')->label('Criado em')->dateTime('d/m/Y H:i'),
                Infolists\Components\TextEntry::make('updated_at')->label('Atualizado em')->dateTime('d/m/Y H:i'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSponsors::route('/'),
            'create' => Pages\CreateSponsor::route('/create'),
            'view' => Pages\ViewSponsor::route('/{record}'),
            'edit' => Pages\EditSponsor::route('/{record}/edit'),
        ];
    }
        
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }
}