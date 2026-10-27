<?php

namespace App\Support;

use App\Models\User;

/**
 * Teachers rarely read their institutional mailbox, so a personal "recovery email" can be
 * registered. Both addresses identify the account on the "forgot password" forms.
 */
class PasswordRecovery
{
    public const NOT_FOUND = 'Não encontramos nenhum cadastro com este e-mail. Verifique o endereço digitado ou fale com a coordenação da plataforma.';
    public const SENT = 'Enviamos o link para redefinir sua senha. Verifique sua caixa de entrada (e o spam).';
    public const THROTTLED = 'Aguarde alguns instantes antes de pedir um novo link.';

    /** The account e-mail for a login or recovery address, or null when nobody has it. */
    public static function accountEmailFor(?string $email): ?string
    {
        $email = mb_strtolower(trim((string) $email));
        if ($email === '') {
            return null;
        }

        return User::query()
            ->where(fn ($q) => $q->whereRaw('lower(email) = ?', [$email])
                ->orWhereHas('information', fn ($i) => $i->whereRaw('lower(recovery_email) = ?', [$email])))
            ->value('email');
    }
}
