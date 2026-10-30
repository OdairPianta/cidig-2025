<?php

namespace App\Support;

use App\Models\University;
use App\Models\User;
use Filament\Forms;

/**
 * The profile fields kept in `user_informations`, shared by the profile page and the profile slide-over.
 */
class ProfileForm
{
    public const ROLES = [
        "professor" => "Professor",
        "researcher" => "Pesquisador",
        "designer" => "Designer",
        "coordinator" => "Coordenador",
        "developer" => "Desenvolvedor",
    ];

    public const INFORMATION_FIELDS = [
        'university_id', 'picture_url', 'linkedin_url', 'lattes_url',
        'orcid_url', 'recovery_email', 'minibio', 'description', 'title', 'roles', 'website',
    ];

    /** @return array<int, Forms\Components\Component> */
    public static function informationFields(): array
    {
        return [
                Forms\Components\Select::make('university_id')
                    ->label('Vínculo Institucional')
                    ->placeholder('Selecione um vínculo institucional')
                    ->options(University::query()->where('is_active', true)->pluck('name', 'id'))
                    ->required(),
                    
                Forms\Components\FileUpload::make('picture_url')
                    ->label('Foto de perfil')
                    ->image()
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('800')
                    ->imageResizeTargetHeight('800')
                    ->imageResizeUpscale(false)
                    ->disk(\App\Support\Uploads::disk())
                    ->imageEditor()
                    ->imageEditorAspectRatios(['1:1'])
                    ->helperText('Foto quadrada (1:1), JPG ou PNG, até 10 MB. Imagens maiores são reduzidas automaticamente. Use o editor para recortar.')
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
    ->options(ProfileForm::ROLES)
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
        ];
    }

    /** Saves the `user_informations` part of the submitted data and returns the rest. */
    public static function saveInformation(User $user, array $data): array
    {
        $information = array_intersect_key($data, array_flip(self::INFORMATION_FIELDS));

        if ($information !== []) {
            $user->information()->updateOrCreate([], $information);
        }

        return array_diff_key($data, $information);
    }
}
