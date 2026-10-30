<?php

namespace App\Filament;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class FilamentResourceBase extends Resource
{
    /** Column holding the owner, used to hide other people's records from non-admins. */
    protected static string $ownerColumn = 'created_by';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        if (auth()->user()->is_admin) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where(static::$ownerColumn, auth()->id());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->is_admin;
    }
}
