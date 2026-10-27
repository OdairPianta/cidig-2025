<?php

namespace App\Console\Commands;

use App\Support\UploadsDiagnostics;
use Illuminate\Console\Command;

class CheckUploads extends Command
{
    protected $signature = 'uploads:check';

    protected $description = 'Checks that image uploads can work: PHP limits, the uploads disk and the public URL';

    public function handle(): int
    {
        $result = UploadsDiagnostics::run(checkUrl: ! app()->runningUnitTests());

        foreach ($result['rows'] as $row) {
            match ($row['status']) {
                'error' => $this->components->error("{$row['label']}: {$row['value']}"),
                'warn' => $this->components->warn("{$row['label']}: {$row['value']}"),
                default => $this->components->twoColumnDetail($row['label'], $row['value']),
            };
        }

        $this->newLine();
        $this->components->info('Note: the CLI reads a different php.ini than the web server. For the limits browsers get, open Admin > Diagnóstico de uploads.');

        return $result['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
