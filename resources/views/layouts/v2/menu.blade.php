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
                        <a class="nav-link" href="{{ route('v2.index') }}">{{ __('Hme') }}</a>
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
                        >
                            {{ __('Login') }}
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a id="navbarDropdownHelp" class="nav-link" href="{{ route('filament.admin.auth.login') }}" role="button">
                            {{ __('Admin') }}
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