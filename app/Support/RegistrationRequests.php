<?php

namespace App\Support;

use App\Models\Subject;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\RegistrationRequested;
use App\Notifications\WelcomeNewUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * "Solicitação de cadastro": a visitor asks for access, which creates a locked user and a ticket.
 * The ticket goes to the responsible of the chosen research line, or to the admin otherwise.
 */
class RegistrationRequests
{
    public static function submit(string $name, string $email, ?int $subjectId, ?string $message): Ticket
    {
        $reviewers = static::reviewersFor($subjectId);
        $reviewer = $reviewers->first();
        $email = strtolower($email);

        $ticket = DB::transaction(function () use ($name, $email, $subjectId, $message, $reviewer) {
            // A rejected applicant keeps the locked account, so applying again reuses it.
            $user = User::where('email', $email)->first() ?? new User(['email' => $email, 'password' => Hash::make(Str::random(40))]);
            $user->name = $name;
            $user->forceFill(['is_active' => false, 'pending_approval' => true])->save();

            $line = $subjectId ? Subject::find($subjectId) : null;

            return Ticket::create([
                'ticket_type' => Ticket::TYPE_REGISTRATION,
                'title' => "Solicitação de cadastro: {$name}",
                'description' => collect([
                    "Nome: {$name}",
                    "E-mail: {$user->email}",
                    $line ? "Linha de pesquisa: {$line->name}" : null,
                    $message ? "Mensagem: {$message}" : null,
                ])->filter()->implode("\n"),
                'status' => Ticket::STATUS_OPEN,
                'created_by' => $user->id,
                'responsible_id' => $reviewer->id,
                'subject_id' => $line?->id,
            ]);
        });

        foreach ($reviewers as $recipient) {
            try {
                $recipient->notify(new RegistrationRequested($ticket));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $ticket;
    }

    /** True while the person already has a request waiting for a decision. */
    public static function hasOpenRequest(string $email): bool
    {
        return Ticket::where('ticket_type', Ticket::TYPE_REGISTRATION)
            ->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS])
            ->whereHas('createdBy', fn ($q) => $q->where('email', strtolower($email)))
            ->exists();
    }

    /** Every active responsible of the research line, falling back to the admin. */
    public static function reviewersFor(?int $subjectId): Collection
    {
        $responsibles = $subjectId
            ? Subject::find($subjectId)?->users()->wherePivot('is_active', true)->where('users.is_active', true)->orderBy('users.id')->get()
            : null;

        return $responsibles?->isNotEmpty() ? $responsibles : collect([User::getAdminUser()]);
    }

    public static function canReview(User $user, Ticket $ticket): bool
    {
        return $ticket->ticket_type === Ticket::TYPE_REGISTRATION
            && $ticket->status === Ticket::STATUS_OPEN
            && ($user->isAdmin() || static::reviewersFor($ticket->subject_id)->contains('id', $user->id));
    }

    public static function approve(Ticket $ticket, User $reviewer): void
    {
        $user = $ticket->createdBy;

        DB::transaction(function () use ($ticket, $user, $reviewer) {
            $user->forceFill(['is_active' => true, 'pending_approval' => false, 'email_verified_at' => $user->email_verified_at ?? now()])->save();

            if ($ticket->subject_id) {
                $user->subjects()->syncWithoutDetaching([$ticket->subject_id]);
            }

            $ticket->update(['status' => Ticket::STATUS_DONE, 'updated_by' => $reviewer->id]);
        });

        try {
            $user->notify(new WelcomeNewUser(Password::broker()->createToken($user)));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function reject(Ticket $ticket, User $reviewer): void
    {
        $ticket->update(['status' => Ticket::STATUS_CLOSED_NOT_DONE, 'updated_by' => $reviewer->id]);
    }
}
