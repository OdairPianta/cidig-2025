<?php

namespace App\Filament\Resources\UserResource\Pages;

/**
 * The admin user form mixes `users` columns with the profile data kept in `user_informations`.
 * This splits them on save and loads them back on edit.
 */
trait SavesUserInformation
{
    private const INFORMATION_FIELDS = [
        'university_id', 'picture_url', 'linkedin_url', 'lattes_url', 'orcid_url',
        'recovery_email', 'minibio', 'description', 'title', 'roles', 'website',
    ];

    /** @var array<string, mixed> */
    private array $informationData = [];

    private function extractInformation(array $data): array
    {
        $this->informationData = array_intersect_key($data, array_flip(self::INFORMATION_FIELDS));

        return array_diff_key($data, $this->informationData);
    }

    private function persistInformation(): void
    {
        if ($this->informationData !== []) {
            $this->record->information()->updateOrCreate([], $this->informationData);
        }
    }
}
