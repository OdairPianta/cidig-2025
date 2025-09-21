<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('filament.admin.pages.dashboard'), navigate: true);
    }
}; ?>

<div class="fi-simple-layout flex min-h-screen flex-col items-center">
    <div class="fi-simple-main-ctn flex w-full flex-grow items-center justify-center">
        <main class="fi-simple-main my-16 w-full bg-white px-6 py-12 shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:max-w-lg sm:rounded-xl sm:px-12">
            <div class="fi-simple-header mb-12 text-center">
                <h1 class="fi-simple-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Laravel
                </h1>
                <p class="fi-simple-header-subheading mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Sign up
                </p>
            </div>

            <form wire:submit="register" class="fi-form space-y-6">
                <!-- Name -->
                <div class="fi-fo-field-wrp">
                    <div class="fi-fo-field-wrp-label">
                        <label class="fi-fo-field-wrp-label-text inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">
                            Full name
                            <span class="fi-fo-field-wrp-label-required text-danger-600 dark:text-danger-400">*</span>
                        </label>
                    </div>
                    <div class="fi-input-wrp">
                        <input
                            wire:model="name"
                            id="name"
                            name="name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            class="fi-input block w-full border-0 py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500 dark:focus:ring-primary-500 sm:text-sm sm:leading-6 bg-gray-50 dark:bg-white/5"
                            placeholder="Seu nome completo"
                        />
                    </div>
                    @error('name')
                        <div class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="fi-fo-field-wrp">
                    <div class="fi-fo-field-wrp-label">
                        <label class="fi-fo-field-wrp-label-text inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">
                            Email address
                            <span class="fi-fo-field-wrp-label-required text-danger-600 dark:text-danger-400">*</span>
                        </label>
                    </div>
                    <div class="fi-input-wrp">
                        <input
                            wire:model="email"
                            id="email"
                            name="email"
                            type="email"
                            required
                            autocomplete="email"
                            class="fi-input block w-full border-0 py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500 dark:focus:ring-primary-500 sm:text-sm sm:leading-6 bg-gray-50 dark:bg-white/5"
                            placeholder="email@exemplo.com"
                        />
                    </div>
                    @error('email')
                        <div class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="fi-fo-field-wrp">
                    <div class="fi-fo-field-wrp-label">
                        <label class="fi-fo-field-wrp-label-text inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">
                            Password
                            <span class="fi-fo-field-wrp-label-required text-danger-600 dark:text-danger-400">*</span>
                        </label>
                    </div>
                    <div class="fi-input-wrp relative">
                        <input
                            wire:model="password"
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="fi-input block w-full border-0 py-1.5 pr-12 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500 dark:focus:ring-primary-500 sm:text-sm sm:leading-6 bg-gray-50 dark:bg-white/5"
                        />
                        <button
                            type="button"
                            tabindex="-1"
                            class="fi-input-wrp-action absolute inset-y-0 end-0 flex h-full items-center pe-3 text-gray-400 hover:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:text-gray-400 cursor-pointer"
                            onclick="togglePassword('password')"
                        >
                            <svg class="fi-input-wrp-action-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="fi-fo-field-wrp">
                    <div class="fi-fo-field-wrp-label">
                        <label class="fi-fo-field-wrp-label-text inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">
                            Confirm password
                            <span class="fi-fo-field-wrp-label-required text-danger-600 dark:text-danger-400">*</span>
                        </label>
                    </div>
                    <div class="fi-input-wrp relative">
                        <input
                            wire:model="password_confirmation"
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="fi-input block w-full border-0 py-1.5 pr-12 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500 dark:focus:ring-primary-500 sm:text-sm sm:leading-6 bg-gray-50 dark:bg-white/5"
                        />
                        <button
                            type="button"
                            tabindex="-1"
                            class="fi-input-wrp-action absolute inset-y-0 end-0 flex h-full items-center pe-3 text-gray-400 hover:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:text-gray-400 cursor-pointer"
                            onclick="togglePassword('password_confirmation')"
                        >
                            <svg class="fi-input-wrp-action-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <div class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="fi-form-actions">
                    <button
                        type="submit"
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm w-full cursor-pointer bg-red-600 hover:bg-red-500 focus-visible:ring-red-500/50 dark:bg-red-500 dark:hover:bg-red-400 dark:focus-visible:ring-red-400/50 text-white"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove class="fi-btn-label">
                            Sign up
                        </span>
                        <span wire:loading class="fi-btn-label">
                            Creating account...
                        </span>
                    </button>
                </div>
            </form>

            <!-- Login Link -->
            <div class="fi-simple-footer mt-12 text-center">
                <p class="fi-simple-footer-text text-sm text-gray-600 dark:text-gray-400">
                    Already have an account?
                    <a href="{{ route('filament.admin.auth.login') }}" 
                       class="fi-simple-footer-link text-primary-600 hover:text-primary-500 dark:text-primary-400"
                       wire:navigate>
                        Sign in
                    </a>
                </p>
            </div>
        </main>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
    field.setAttribute('type', type);
}
</script>