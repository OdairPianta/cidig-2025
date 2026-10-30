<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name', '2023 Cidadania Digital') }}</title>

<!-- Fonts -->
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

<!-- Styles -->
<link href="{{ mix('css/app.css') }}" rel="stylesheet">
<link href="{{ mix('css/app-2023.css') }}" rel="stylesheet">
<link href="{{ mix('css/footer.css') }}" rel="stylesheet">
<link href="{{ asset('css/contrast.css', !app()->isLocal()) }}?v={{ filemtime(public_path('css/contrast.css')) }}" rel="stylesheet">
<link href="{{ asset('css/fa/css/all.css', !app()->isLocal()) }}" rel="stylesheet">
<link href="{{ asset('css/select2/select2.min.css', !app()->isLocal()) }}" rel="stylesheet" />
<link href="{{ asset('css/select2/select2.min.css', !app()->isLocal()) }}" rel="stylesheet" />
<link href="{{ asset('css/slick/slick.css', !app()->isLocal()) }}" rel="stylesheet" />
<link href="{{ asset('css/slick/slick-theme.css', !app()->isLocal()) }}" rel="stylesheet" />

<link href="{{ asset('/fontawesome/css/fontawesome.css', !app()->isLocal()) }}" rel="stylesheet">
<link href="{{ asset('/fontawesome/css/brands.css', !app()->isLocal()) }}" rel="stylesheet">
<link href="{{ asset('/fontawesome/css/solid.css', !app()->isLocal()) }}" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">

<!-- Scripts -->
<script src="{{ mix('js/initial-load.js') }}" defer></script>
<script src="{{ mix('js/app.js') }}" defer></script>
<script src="{{ mix('js/app-2023.js') }}" defer></script>
<script src="{{ asset('js/bootstrap-select.min.js', !app()->isLocal()) }}" defer></script>
<script src="{{ asset('js/select2/select2.min.js', !app()->isLocal()) }}" defer></script>
<script src="{{ asset('js/jquery-ui.min.js', !app()->isLocal()) }}" defer></script>
<script src="{{ asset('js/slick/slick.min.js', !app()->isLocal()) }}" defer></script>

<!-- Meta Pixel Code -->
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1281934086512661');
    fbq('track', 'PageView');
</script>
<noscript>
    <img
        height="1"
        width="1"
        style="display:none"
        src="https://www.facebook.com/tr?id=1281934086512661&ev=PageView&noscript=1"
    />
</noscript>
<!-- End Meta Pixel Code -->


@stack('css')
