<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use App\Support\ProfileForm;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

                ...ProfileForm::informationFields(),
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
        return parent::mutateFormDataBeforeSave(ProfileForm::saveInformation(auth()->user(), $data));
    }
}
