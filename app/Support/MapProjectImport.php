<?php

namespace App\Support;

use App\Models\MapProject;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Reads the CSV exported from the Observatório mapping form (Google Sheets: File > Download > CSV).
 *
 * The form was answered in Portuguese and in Spanish, so the sheet holds two blocks of columns and each
 * respondent fills in only one. Columns are found by their header text and the first filled block wins.
 * The collecting researcher's phone and e-mail are never read.
 */
class MapProjectImport
{
    /** Field => regexes over the normalised header (lowercase, no accents, only letters and digits). */
    private const COLUMNS = [
        'language' => ['/^responder$/'],
        'submitted_at' => ['/^carimbo de data/'],
        'name' => ['/nombre del proyecto/', '/nome do projeto/'],
        'country' => ['/pais de origen/', '/pais de origem/'],
        'city' => ['/ciudad de origen/', '/cidade de origem/'],
        'latitude' => ['/^latitud/'],
        'longitude' => ['/^longitud/'],
        'proposer_nature' => ['/natura.* del proponente/', '/natura.* do proponente/'],
        'proposers' => ['/^proponente/'],
        'purpose' => ['/proposito del proyecto/', '/finalidade do projeto/'],
        'mapping' => ['/mapeo del proyecto/', '/cartografia do projeto/'],
        'description' => ['/descripcion del proyecto/', '/descricao do projeto/'],
        'nonhuman_influence' => ['/actantes no humanos/', '/actantes nao humanos/'],
        'actants' => ['/cuales de los actores/', '/quais dos actantes/'],
        'tics' => ['/quais as tics/'],
        'instagram' => ['/instagra/'],
        'facebook' => ['/facebook/'],
        'tiktok' => ['/tik tok/'],
        'website' => ['/enlace del sitio/', '/link do site/'],
        'has_own_app' => ['/tecnologia propia/', '/tecnologia propria/'],
        'app_url' => ['/para acceder a la app/', '/acesso ao app/'],
        'themes' => ['/tematica/'],
        'researcher_name' => ['/nombre del investigador/', '/nome do pesquisador/'],
    ];

    private const LISTS = ['actants', 'tics', 'themes'];

    /** @return array{created:int, existing:int, skipped:int, without_coordinates:int} */
    public function import(string $path): array
    {
        $result = ['created' => 0, 'existing' => 0, 'skipped' => 0, 'without_coordinates' => 0];

        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \RuntimeException('Não foi possível abrir o arquivo.');
        }

        $header = fgetcsv($handle, 0, ',', '"', '');
        if (! $header) {
            fclose($handle);
            throw new \RuntimeException('O arquivo está vazio.');
        }

        $map = $this->mapColumns($header);
        if (empty($map['name'])) {
            fclose($handle);
            throw new \RuntimeException('Não encontrei a coluna com o nome do projeto. Use o CSV exportado do formulário de mapeamento.');
        }

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $data = $this->extract($row, $map);

            if (blank($data['name'] ?? null)) {
                $result['skipped']++;

                continue;
            }

            $key = $this->key($data);
            if (MapProject::where('external_key', $key)->exists()) {
                $result['existing']++;

                continue;
            }

            [$lat, $lng, $source] = $this->coordinates($data['latitude'] ?? '', $data['longitude'] ?? '');
            if ($lat === null) {
                $result['without_coordinates']++;
            }

            MapProject::create([
                'external_key' => $key,
                'name' => $data['name'],
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => $lat,
                'longitude' => $lng,
                'coordinates_source' => $source,
                'proposers' => $data['proposers'] ?? null,
                'proposer_nature' => $data['proposer_nature'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'mapping' => $data['mapping'] ?? null,
                'description' => $data['description'] ?? null,
                'nonhuman_influence' => $data['nonhuman_influence'] ?? null,
                'actants' => $data['actants'] ?? [],
                'tics' => $data['tics'] ?? [],
                'themes' => $data['themes'] ?? [],
                'website' => $data['website'] ?? null,
                'instagram' => $data['instagram'] ?? null,
                'facebook' => $data['facebook'] ?? null,
                'tiktok' => $data['tiktok'] ?? null,
                'has_own_app' => $this->isYes($data['has_own_app'] ?? ''),
                'app_url' => $data['app_url'] ?? null,
                'researcher_name' => $data['researcher_name'] ?? null,
                'source_language' => $this->language($data['language'] ?? null),
                'submitted_at' => $this->date($data['submitted_at'] ?? null),
                'is_approved' => false,
            ]);
            $result['created']++;
        }

        fclose($handle);

        return $result;
    }

    /**
     * Latitude and longitude as people typed them: "-334.513" (decimal point in the wrong place),
     * "23°32′50″ S" (degrees, minutes, seconds), "-23,5338" (comma). Returns [lat, lng, source] where source
     * is "sheet" when the numbers were usable as typed and "corrected" when they were converted or rescaled.
     *
     * @return array{0: ?float, 1: ?float, 2: ?string}
     */
    public function coordinates(string $rawLat, string $rawLng): array
    {
        [$lat, $latFixed] = $this->coordinate($rawLat, 90);
        [$lng, $lngFixed] = $this->coordinate($rawLng, 180);

        if ($lat === null || $lng === null || ($lat == 0 && $lng == 0)) {
            return [null, null, null];
        }

        return [round($lat, 6), round($lng, 6), ($latFixed || $lngFixed) ? 'corrected' : 'sheet'];
    }

    /** @return array{0: ?float, 1: bool} value and whether it had to be converted or rescaled */
    private function coordinate(string $raw, int $limit): array
    {
        $raw = trim(str_replace(['−', '–'], '-', $raw));
        if ($raw === '' || ! preg_match_all('/\d+(?:[.,]\d+)?/', $raw, $numbers)) {
            return [null, false];
        }

        $parts = array_map(fn ($n) => (float) str_replace(',', '.', $n), $numbers[0]);
        $fixed = false;

        if (count($parts) >= 2 && preg_match('/[°º′\'″"]/u', $raw)) {
            // Degrees, minutes, seconds
            $value = $parts[0] + ($parts[1] / 60) + (($parts[2] ?? 0) / 3600);
            $fixed = true;
        } elseif (count($parts) === 1) {
            $value = $parts[0];
        } else {
            return [null, false];
        }

        // Hemisphere letters: S and W (O = Oeste in Portuguese/Spanish) are negative.
        $negative = str_starts_with($raw, '-') || preg_match('/(?<![A-Za-z])[SWO](?![A-Za-z])/u', strtoupper($raw));
        if ($negative) {
            $value = -abs($value);
        }

        // "-334.513" is -33.4513: move the decimal point until it fits on the globe.
        $tries = 0;
        while (abs($value) > $limit && $tries++ < 4) {
            $value /= 10;
            $fixed = true;
        }

        return abs($value) > $limit ? [null, false] : [$value, $fixed];
    }

    private function mapColumns(array $header): array
    {
        $map = [];

        foreach ($header as $index => $title) {
            $normal = trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii((string) $title))));

            foreach (self::COLUMNS as $field => $patterns) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $normal)) {
                        $map[$field][] = $index;

                        continue 3;
                    }
                }
            }
        }

        return $map;
    }

    private function extract(array $row, array $map): array
    {
        $data = [];

        foreach ($map as $field => $indexes) {
            foreach ($indexes as $index) {
                $value = $this->clean($row[$index] ?? '');
                if ($value !== '') {
                    $data[$field] = in_array($field, self::LISTS, true) ? $this->splitList($value) : $value;
                    break;
                }
            }
        }

        return $data;
    }

    private function clean(string $value): string
    {
        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        }

        return trim(preg_replace('/^\xEF\xBB\xBF/', '', $value));
    }

    /** Splits "A (x, y), B" on the commas that are not inside parentheses. */
    private function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/,(?![^()]*\))/u', $value))));
    }

    private function key(array $data): string
    {
        return sha1(Str::lower(Str::ascii(($data['name'] ?? '').'|'.($data['country'] ?? '').'|'.($data['city'] ?? ''))));
    }

    private function isYes(string $value): bool
    {
        return (bool) preg_match('/^(sim|si|sí|yes|s)\b/i', Str::ascii(trim($value)));
    }

    private function language(?string $value): ?string
    {
        return match (true) {
            blank($value) => null,
            Str::startsWith(Str::lower(Str::ascii($value)), 'port') => 'pt',
            Str::startsWith(Str::lower(Str::ascii($value)), 'esp') => 'es',
            default => Str::lower(substr($value, 0, 10)),
        };
    }

    private function date(?string $value): ?Carbon
    {
        try {
            return $value ? Carbon::createFromFormat('d/m/Y H:i:s', trim($value)) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
