<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'remember_token',
    ];

    public function getFullProfile(): array
    {
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'university' => $this->information->university->name ?? null,
        'picture_url' => $this->information->picture_url,
        'minibio' => $this->information->minibio,
        'roles' => $this->information->roles,
        // adicione outros campos conforme necessário
    ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $with = ['information']; // Isso carrega automaticamente as informações do usuário
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected $attributes = [
        'is_admin' => false,
        'is_active' => false,
    ];

    // ...existing code (mantenha todos os outros métodos como estavam)...
    
    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->implode('');
    }

    public function resources()
    {
        return $this->hasMany(Resource::class, 'created_by');
    }

    /**
     * @return BelongsToMany
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    /**
     * @return HasMany
     */
    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    /**
     * @return HasMany
     */
    public function receivedTickets()
    {
        return $this->hasMany(Ticket::class, 'responsible_id');
    }

    public function information()
    {
        return $this->hasOne(UserInformation::class);
    }

    public function videos()
    {
        return $this->belongsToMany(Video::class, 'video_user');
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * @return Builder|Model
     */
    public static function getAdminUser()
    {
        return \App\Models\User::query()
            ->firstWhere('is_admin', '=', true);
    }

    public function getWikiAccessToken()
    {
        return $this->wiki_access_token;
    }

    public function getWikiUrl()
    {
        $baseUrl = env('WIKI_BASE_URL');

        if($this->getWikiAccessToken()) {
            $baseUrl .= 'api.php?action=loginWithToken&format=json&loginToken=' . $this->getWikiAccessToken();
        }

        return $baseUrl;
    }

    public function hasWikiAccess()
    {
        return $this->getWikiAccessToken();
    }

    public function getSlugAttribute()
    {
        return Str::slug($this->name, '-');
    }

    public static function scopeWithRole($query, $role)
    {
        return $query->whereHas('information', function ($q) use ($role) {
            $q->whereJsonContains('roles', $role);
        });
    }

    public static function getCoordinators(): Collection
    {
        return User::getUsersByRole('coordinator');
    }

    public static function getResearchers(): Collection
    {
        return User::getUsersByRole('researcher');
    }

    public static function getDesigners(): Collection
    {
        return User::getUsersByRole('designer');
    }

    public static function getDevelopers(): Collection {
        return User::getUsersByRole('developer');
    }

    private static function getUsersByRole(string $role): Collection {
        return User::with('information.university')
        ->withRole($role)
        ->where('is_active', 1)  // Add this line to filter active users only
        ->orderBy('name')
        ->get();
    }   

    public static function getPersonMergedData($person): array
{
    // Se $person já é um array (dados vindos do banco), usa diretamente
    if (is_array($person)) {
        return [
            'id' => $person['id'] ?? null,
            'name' => $person['name'] ?? '',
            'slug' => $person['slug'] ?? '',
            'university' => $person['university'] ?? '',
            'picture_url' => $person['picture_url'] ?? '/images/team/default.jpg',
            'lattes_url' => $person['lattes_url'] ?? null,
            'orcid_url' => $person['orcid_url'] ?? null,
            'linkedin_url' => $person['linkedin_url'] ?? null,
            'minibio' => $person['minibio'] ?? '',
            'description' => $person['description'] ?? '',
            'title' => $person['title'] ?? null,
            'website' => $person['website'] ?? null,
        ];
    }
    
    // Se $person é uma string (nome), busca no banco
    if (is_string($person)) {
        $user = self::where('name', $person)->with('information')->first();
        
        if (!$user) {
            return [
                'id' => null,
                'name' => $person,
                'slug' => '',
                'university' => '',
                'picture_url' => '/images/team/default.jpg',
                'lattes_url' => null,
                'orcid_url' => null,
                'linkedin_url' => null,
                'minibio' => '',
                'description' => '',
                'title' => null,
                'website' => null,
            ];
        }
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'slug' => $user->slug,
            'university' => $user->information->university->name ?? '',
            'picture_url' => $user->information->picture_url ?? '/images/team/default.jpg',
            'lattes_url' => $user->information->lattes_url,
            'orcid_url' => $user->information->orcid_url,
            'linkedin_url' => $user->information->linkedin_url,
            'minibio' => $user->information->minibio,
            'description' => $user->information->description,
            'title' => $user->information->title,
            'website' => $user->information->website,
        ];
    }
    
    // Se $person é um objeto User
    if ($person instanceof User) {
        return [
            'id' => $person->id,
            'name' => $person->name,
            'slug' => $person->slug,
            'university' => $person->information->university->name ?? '',
            'picture_url' => $person->information->picture_url ?? '/images/team/default.jpg',
            'lattes_url' => $person->information->lattes_url,
            'orcid_url' => $person->information->orcid_url,
            'linkedin_url' => $person->information->linkedin_url,
            'minibio' => $person->information->minibio,
            'description' => $person->information->description,
            'title' => $person->information->title,
            'website' => $person->information->website,
        ];
    }
    
    return [];
}

    public static function getInvitedResearchers(): Collection {
        return User::getUsersByRole('invited');
    }


    public static function getSlugFrom(string $name): string {
        return Str::slug($name, '-');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }


    public function getPictureLink(): string
    {
        if (!$this->information || !$this->information->picture_url) {
            return '';
        }

        return url($this->information->picture_url);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
