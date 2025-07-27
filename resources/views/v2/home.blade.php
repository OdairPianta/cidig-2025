@extends('layouts.v2.home')

@section('content')
    <div class="content-home">
        @if (session('status'))
            <div class="col-md-12">
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <div class="container">

            <hr>

            <div id="masterclass">
                <h2 class="home-title">Eventos</h2>

                <div class="row flex slider slider-masterclass">
                    @foreach($masterclasses as $masterclass)
                        <div class="col-12 mb-3 card-container">
                            <div class="card card-body p-0 masterclass-image">
                                <a class="access-link" href="{{ $masterclass['url'] }}" target="_blank">
                                    <img class="img-web" src="{{ url($masterclass['thumbnail']) }}" alt="{{ $masterclass['title'] }}" height="610px" width="100%">
                                    <img class="img-tablet" src="{{ url($masterclass['thumbnail-tablet']) }}" alt="{{ $masterclass['title'] }}" height="450px" width="100%">
                                    <img class="img-mobile" src="{{ url($masterclass['thumbnail-mobile']) }}" alt="{{ $masterclass['title'] }}" height="600px" width="100%">
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <hr>

            <div class="container" id="about-masterclass">
                <div class="row">
                    <div class="col-sm-12 col-md-6 mt-3">
                        <img src="{{ url('/images/logos/masterclasses.png') }}" alt="Masterclass Unico" width="80%">

                        <p class="content">
                            Se atualize sobre a Era Digital e compreenda as implicações atuais e futuras em nossas vidas, na carreira e na própria sociedade.
                        </p>

                        <div class="professores">
                            <p class="flex">
                                <i class="fa-solid fa-2x fa-people-group"></i>
                                Professores: Massimo Di Felice, Derrick de Kerckhove, Ysadora Córdova, Eliete Pereira, Sônia Guajajara
                            </p>

                            <p class="flex realizacao">
                                Realização:
                                <img src="{{ url('/images/logos/atopos.png') }}" alt="Atopos logo">
                                <img src="{{ url('/images/logos/cidig.png') }}" alt="Cidadania Digital logo">
                                <img src="{{ url('/images/logos/unico.png') }}" alt="Unico logo">
                                <img src="{{ url('/images/logos/usp.png') }}" alt="USP logo">
                            </p>
                        </div>

                        <a class="about-us-link" href="{{ route('v2.showSpecialPage', 'unico') }}">
                            Quero participar
                        </a>
                    </div>

                    <div class="col-xs-12 col-sm-6">
                        <img src="{{ url('/images/page-about-masterclass.png') }}">
                    </div>
                </div>
            </div>

            <div id="verbetes">
                <h2 class="home-title home-title-sections">Verbetes</h2>

                <div class="tags-list">
                    @foreach((array_slice($tagsToShow, 0, 10) ?? []) as $tag)
                        <a href="{{ route('v2.searchByTag', $tag['id']) }}" class="label">
                            {{ $tag['name'] }}
                        </a>
                    @endforeach
                </div>

                <div class="row flex slider slider-verbete">
                    @foreach($subjects as $key => $subject)
                        @if(!isset($subject['inactive']))
                            <div class="col-md-3 mb-3 card-container">
                                <div class="card card-body p-0">
                                    <hr>
                                    <a href="{{ route('v2.showSpecialPage', $key) }}">
                                        <img class="img-fluid" src="{{ $subject['image'] }}" alt="Video thumbnail">
                                        <p>{{ $subject['name'] }}</p>
                                    </a>
                                    <hr class="hr-bottom">
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{ view('video-target') }}
    </div>
@endsection
