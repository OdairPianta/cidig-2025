<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Downloads an image from a public URL into the public disk (used by "thumbnail from a link").
 */
class ImageDownloader
{
    public const MAX_BYTES = 10 * 1024 * 1024;

    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    /**
     * @return string path relative to the public disk
     *
     * @throws InvalidArgumentException with a message suitable for showing to the user
     */
    public function store(string $url, string $directory): string
    {
        $url = trim($url);
        $parts = parse_url($url);

        if (! $parts || ! in_array($parts['scheme'] ?? null, ['http', 'https'], true) || empty($parts['host'])) {
            throw new InvalidArgumentException('Informe um link de imagem válido (http ou https).');
        }

        $this->assertPublicHost($parts['host']);

        try {
            $response = Http::timeout(15)->withHeaders(['Accept' => 'image/*'])->get($url);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException('Não foi possível baixar a imagem deste link.');
        }

        if (! $response->successful()) {
            throw new InvalidArgumentException('Não foi possível baixar a imagem deste link.');
        }

        $body = $response->body();

        if (strlen($body) > self::MAX_BYTES) {
            throw new InvalidArgumentException('A imagem é muito grande. O tamanho máximo é 10 MB.');
        }

        // Trust the file contents, not the header or extension.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($body);

        if (! isset(self::EXTENSIONS[$mime])) {
            throw new InvalidArgumentException('O link não é uma imagem JPG, PNG, GIF ou WebP.');
        }

        $path = trim($directory, '/') . '/' . Str::uuid() . '.' . self::EXTENSIONS[$mime];
        Storage::disk('public')->put($path, $body, 'public');

        return $path;
    }

    private function assertPublicHost(string $host): void
    {
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new InvalidArgumentException('Este endereço não é permitido.');
        }
    }
}
