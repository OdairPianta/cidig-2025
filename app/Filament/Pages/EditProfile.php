<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use App\Models\University;
use App\Support\ProfileLinks;

class EditProfile extends BaseEditProfile
{
    private const ROLES = [
        "professor" => "Professor",
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
                    ->label('Vínculo Institucional')
                    ->placeholder('Selecione um vínculo institucional')
                    ->options(University::query()->where('is_active', true)->pluck('name', 'id'))
                    ->required(),
                    
                Forms\Components\FileUpload::make('picture_url')
                    ->label('Foto de perfil')
                    ->image()
                    ->disk(\App\Support\Uploads::disk())
                    ->imageEditor()
                    ->imageEditorAspectRatios(['1:1'])
                    ->helperText('Foto quadrada (1:1), JPG ou PNG, até 10 MB. Use o editor para recortar.')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp', 'image/bmp'])
                    ->directory('images/team')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                    
                Forms\Components\Section::make('Links')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        ...ProfileLinks::fields(),
                    ]),
                    
                Forms\Components\TextInput::make('recovery_email')
                    ->label('E-mail de recuperação')
                    ->email()
                    ->helperText('E-mail pessoal usado, além do e-mail de acesso, para recuperar a senha.'),

                Forms\Components\Textarea::make('minibio')
                    ->label('Mini Bio')
                    ->rows(3),
                    
                Forms\Components\Textarea::make('description')
                    ->label('Currículo Completo')
                    ->rows(3),
                
Forms\Components\Select::make('roles')
    ->label('Funções')
    ->placeholder('Selecione as funções')
    ->multiple()
    ->options(self::ROLES)
    ->required()
    ->validationMessages([
        'required' => 'Pelo menos uma função deve ser selecionada.',
    ]),
                    
Forms\Components\TextInput::make('website')
    ->label('Site')
    ->url()
    ->validationMessages([
        'url' => 'O site deve ser uma URL válida.',
    ]),
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
            'orcid_url', 'recovery_email', 'minibio', 'description', 'title', 'roles', 'website'
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