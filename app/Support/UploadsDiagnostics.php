<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Runs the checks behind "photo failed to upload" / "photo does not load". The PHP limits are
 * read from the current SAPI, so run it from the admin page (web) to see what browsers get;
 * the CLI reads a different php.ini.
 */
class UploadsDiagnostics
{
    public const MIN_LIMIT_BYTES = 12 * 1024 ** 2;

    /** @return array{ok: bool, rows: array<int, array{label: string, value: string, status: string}>} */
    public static function run(bool $checkUrl = true): array
    {
        $rows = [];
        $add = function (string $label, string $value, string $status = 'info') use (&$rows) {
            $rows[] = compact('label', 'value', 'status');
        };

        $limit = Uploads::serverLimitBytes();
        $add('SAPI', PHP_SAPI);
        $add('upload_max_filesize', (string) ini_get('upload_max_filesize'));
        $add('post_max_size', (string) ini_get('post_max_size'));
        $add(
            'Limite efetivo por arquivo',
            Uploads::humanBytes($limit) . ($limit < self::MIN_LIMIT_BYTES ? ' — abaixo de 12 MB: fotos comuns falham com "failed to upload"' : ''),
            $limit < self::MIN_LIMIT_BYTES ? 'error' : 'ok'
        );

        $disk = Uploads::disk();
        $add('Disco de uploads (UPLOADS_DISK)', $disk, $disk === 'public' ? 'warn' : 'ok');
        if ($disk === 'public') {
            $add('Atenção', 'O disco "public" fica no servidor da aplicação e é apagado a cada deploy na DigitalOcean. Use UPLOADS_DISK=spaces.', 'warn');
        }

        $path = 'diagnostics/uploads-check-' . bin2hex(random_bytes(4)) . '.txt';

        try {
            Uploads::storage()->put($path, 'ok', 'public');
            $add('Gravar arquivo', 'ok', 'ok');
            $add('Ler arquivo', Uploads::storage()->get($path) === 'ok' ? 'ok' : 'falhou', Uploads::storage()->get($path) === 'ok' ? 'ok' : 'error');

            $url = (string) Uploads::url($path);
            $add('URL pública', $url);

            if ($checkUrl && preg_match('#^https?://#i', $url)) {
                try {
                    $status = Http::timeout(10)->get($url)->status();
                } catch (Throwable $e) {
                    $status = $e->getMessage();
                }
                $add('Resposta da URL pública', (string) $status, $status === 200 ? 'ok' : 'error');
                if ($status !== 200) {
                    $add('Atenção', 'A URL não responde 200: a imagem sobe, mas "não carrega". No Spaces confira SPACES_URL e se os arquivos são públicos; no disco public confira o link storage.', 'error');
                }
            }

            Uploads::storage()->delete($path);
            $add('Apagar arquivo', 'ok', 'ok');
        } catch (Throwable $e) {
            $add('Erro no armazenamento', $e->getMessage(), 'error');
        }

        if (! app()->runningInConsole()) {
            // Behind a proxy this must be https, otherwise Livewire's signed upload URL is rejected (401).
            $add(
                'Conexão vista pela aplicação',
                request()->isSecure() ? 'https' : 'http — o proxy não está sendo confiado; o envio de arquivos falha com 401',
                request()->isSecure() ? 'ok' : 'error'
            );
        }

        $add('Uploads temporários do Livewire', config('livewire.temporary_file_upload.disk') ?? 'padrão (' . config('filesystems.default') . ')');
        $add('APP_URL', (string) config('app.url'));

        return ['ok' => ! collect($rows)->contains('status', 'error'), 'rows' => $rows];
    }
}
