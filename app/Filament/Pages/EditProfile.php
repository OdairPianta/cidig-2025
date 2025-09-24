<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use App\Models\University;

class EditProfile extends BaseEditProfile
{
    private const ROLES = [
        "researcher" => "Pesquisador",
        "designer" => "Designer",
        "coordinator" => "Coordenador",
        "developer" => "Desenvolvedor",
    ];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

                Forms\Components\Select::make('university_id')
                    ->label('Universidade')
                    ->placeholder('Selecione uma universidade')
                    ->options(University::query()->where('is_active', true)->pluck('name', 'id'))
                    ->required(),
                    
                Forms\Components\FileUpload::make('picture_url')
                    ->label('Foto de perfil')
                    ->image()
                    ->acceptedFileTypes(['image/bmp','image/jpeg', 'image/x-png', 'image/png', 'image/gif'])
                    ->directory('images/team')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                    
                Forms\Components\Section::make('Links')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('linkedin_url')
                            ->label('LinkedIn')
                            ->url(),
                        Forms\Components\TextInput::make('lattes_url')
                            ->label('Lattes')
                            ->url(),
                        Forms\Components\TextInput::make('orcid_url')
                            ->label('ORCID')
                            ->url(),
                    ]),
                    
                Forms\Components\Textarea::make('minibio')
                    ->label('Mini Bio')
                    ->rows(3),
                    
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3),
                
                Forms\Components\Select::make('roles')
                    ->label('Funções')
                    ->placeholder('Selecione as funções')
                    ->multiple()
                    ->options(self::ROLES)
                    ->required(),
                    
                Forms\Components\TextInput::make('website')
                    ->label('Site')
                    ->url(),
            ])
            ->statePath('data');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);
        
        $user = auth()->user();
        if ($user->information) {
            $informationData = $user->information->toArray();
            $data = array_merge($data, $informationData);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $informationFields = [
            'university_id', 'picture_url', 'linkedin_url', 'lattes_url', 
            'orcid_url', 'minibio', 'description', 'title', 'roles', 'website'
        ];

        $informationData = [];
        foreach ($informationFields as $field) {
            if (array_key_exists($field, $data)) {
                $informationData[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        if (!empty($informationData)) {
            $user = auth()->user();
            
            if ($user->information) {
                $user->information->update($informationData);
            } else {
                $user->information()->create($informationData);
            }
        }

        return parent::mutateFormDataBeforeSave($data);
    }
}