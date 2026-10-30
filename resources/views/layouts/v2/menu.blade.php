<nav class="navbar navbar-expand-md navbar-light menu-navbar shadow-sm p-0">
    <div class="container">
        <a class="navbar-brand" href="{{ route('v2.index') }}">
            <img src="{{ url('/images/logos/logo-2023.png') }}" height="32px" alt="Logo Plataforma Cidadania Digital">
        </a>

        <button class="navbar-toggler"
                role="button"
                data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="{{ __('Toggle navigation') }}"
        >
            <span class="navbar-toggler-icon"> <i class="fa-solid fa-bars"></i></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">

            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav">
                @if(isset($menuItems) && $menuItems->count() > 0)
                    @foreach($menuItems as $menuItem)
                        <li class="nav-item {{ $loop->last ? 'mr-4' : '' }}">
                            <a class="nav-link" 
                               href="{{ $menuItem->link }}"
                               
                            >
                                {{ $menuItem->slug }}
                            </a>
                        </li>
                    @endforeach
                @else
                    <!-- Fallback para itens padrão caso não haja itens no banco -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('v2.index') }}">{{ __('Página inicial') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('v2.about') }}">{{ __('Sobre nós') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('v2.team') }}">{{ __('Equipe') }}</a>
                    </li>
                    <li class="nav-item mr-4">
                        <a class="nav-link" href="#" target="_blank">{{ __('Atopos') }}</a>
                    </li>
                @endif
                
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link login-button"
                           href="{{ route('filament.admin.auth.login') }}"
                           role="button"
                           aria-controls="site-login-panel"
                           aria-expanded="{{ $errors->hasBag('siteLogin') ? 'true' : 'false' }}"
                           onclick="event.preventDefault(); window.toggleSiteLogin();"
                        >
                            {{ __('Login') }}
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a id="navbarDropdownHelp" class="nav-link" href="{{ route('filament.admin.auth.login') }}" role="button">
                            {{ auth()->user()->name }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a id="navbarDropdown" class="nav-link" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                           document.getElementById('logout-form').submit();"
                        >
                            {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

@guest
    {{-- Slide-down login; the link above still leads to the full login page without JavaScript. --}}
    @php($loginErrors = $errors->getBag('siteLogin'))
    <div id="site-login-panel" class="site-login-panel {{ $loginErrors->any() ? 'is-open' : '' }}">
        <div class="container">
            <form method="POST" action="{{ route('site.login') }}" class="site-login-form">
                @csrf
                <input type="email" name="email" value="{{ old('email') }}" placeholder="E-mail" autocomplete="username" required>
                <input type="password" name="password" placeholder="Senha" autocomplete="current-password" required>
                <button type="submit">Entrar</button>
            </form>
            @if($loginErrors->any())
                <p class="site-login-error" role="alert">{{ $loginErrors->first('email') }}</p>
            @endif
            <p class="site-login-links">
                <a href="{{ route('filament.admin.auth.password-reset.request') }}">Esqueci minha senha</a>
                <a href="{{ route('filament.admin.auth.register') }}">Solicitar cadastro</a>
            </p>
        </div>
    </div>
    <style>
        /* Positioned with a z-index so the page's absolutely positioned background image cannot cover it. */
        .site-login-panel { position: relative; z-index: 1030; max-height: 0; overflow: hidden; background-color: #fff; color: #212529; border-bottom: 0 solid #e2e8f0; transition: max-height .3s ease; }
        .site-login-panel.is-open { max-height: 220px; border-bottom-width: 1px; box-shadow: 0 8px 24px rgba(0, 0, 0, .35); }
        .site-login-panel .container { padding-top: 16px; padding-bottom: 12px; }
        .site-login-form { display: flex; flex-wrap: wrap; gap: 8px; }
        .site-login-form input { flex: 1 1 200px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .site-login-form button { padding: 8px 24px; border: 0; border-radius: 8px; color: #f1f5f9; font-weight: 500; background: linear-gradient(90deg, #ff4d00 0%, #f04949 100%); }
        .site-login-error { margin: 8px 0 0; color: #b91c1c; font-size: 14px; }
        .site-login-links { margin: 8px 0 0; font-size: 14px; }
        .site-login-links a { margin-right: 16px; }
    </style>
    <script>
        window.toggleSiteLogin = function () {
            var panel = document.getElementById('site-login-panel');
            var open = panel.classList.toggle('is-open');
            document.querySelector('.login-button').setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) { panel.querySelector('input[name=email]').focus(); }
        };
    </script>
@endguest
