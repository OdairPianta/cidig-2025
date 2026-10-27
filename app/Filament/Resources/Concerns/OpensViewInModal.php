<?php

namespace App\Filament\Resources\Concerns;

use Closure;

/**
 * List pages that show a record's metadata in a pop-up: clicking a row triggers the table's
 * "view" action instead of navigating to the resource's view page.
 */
trait OpensViewInModal
{
    protected function getTableRecordUrlUsing(): ?Closure
    {
        return fn (): ?string => null;
    }
}
