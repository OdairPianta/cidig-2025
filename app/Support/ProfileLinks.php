<?php

namespace App\Support;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;

/**
 * Lattes, ORCID and LinkedIn fields accept whatever people paste (full URL, bare id, trailing
 * slashes, tracking parameters) and store the canonical profile URL the public pages link to.
 */
class ProfileLinks
{
    public static function lattes(?string $input): ?string
    {
        if (preg_match('/(?<!\d)(\d{16})(?!\d)/', preg_replace('/[\s.\-]/', '', (string) $input), $m)) {
            return 'http://lattes.cnpq.br/' . $m[1];
        }

        return null;
    }

    public static function orcid(?string $input): ?string
    {
        if (! preg_match('/(?<![\dX])(\d{4})-?(\d{4})-?(\d{4})-?(\d{3}[\dXx])(?![\dXx])/', (string) $input, $m)) {
            return null;
        }

        return 'https://orcid.org/' . "{$m[1]}-{$m[2]}-{$m[3]}-" . strtoupper($m[4]);
    }

    public static function linkedin(?string $input): ?string
    {
        $input = trim((string) $input);

        if (preg_match('~linkedin\.com/(?:in|pub)/([^/?#\s]+)~i', $input, $m)) {
            $slug = $m[1];
        } elseif (preg_match('/^[\p{L}\p{N}\-_%.]{3,100}$/u', $input) && preg_match('/\p{L}/u', $input)) {
            // Bare handle such as "maria-silva-123"; it must contain letters, otherwise it is not a LinkedIn id.
            $slug = $input;
        } else {
            return null;
        }

        return 'https://www.linkedin.com/in/' . rawurlencode(rawurldecode($slug));
    }

    /** @return array<int, Component> */
    public static function fields(): array
    {
        return [
            self::field('linkedin_url', 'LinkedIn', [self::class, 'linkedin'], 'o endereço do perfil (ex.: https://www.linkedin.com/in/seu-nome) ou apenas o nome do perfil, com letras'),
            self::field('lattes_url', 'Lattes', [self::class, 'lattes'], 'o link do currículo ou apenas o número de 16 dígitos (ex.: 1234567890123456)'),
            self::field('orcid_url', 'ORCID', [self::class, 'orcid'], 'o link ou apenas o número no formato 0000-0000-0000-0000'),
        ];
    }

    private static function field(string $name, string $label, callable $normalize, string $hint): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->helperText("Cole {$hint}. Ajustamos o restante automaticamente.")
            ->live(onBlur: true)
            ->afterStateUpdated(function (?string $state, Set $set) use ($name, $normalize) {
                if (filled($state) && ($clean = $normalize($state))) {
                    $set($name, $clean);
                }
            })
            ->rules([
                fn (): Closure => function (string $attribute, $value, Closure $fail) use ($label, $normalize, $hint) {
                    if (filled($value) && ! $normalize($value)) {
                        $fail("O {$label} informado não é válido. Informe {$hint}.");
                    }
                },
            ])
            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? ($normalize($state) ?? $state) : null);
    }
}
