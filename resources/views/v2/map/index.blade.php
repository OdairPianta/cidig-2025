@extends('layouts.v2.app')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.min.css">

    {{-- Dark theme colours (#090E1F page, #161C32 cards, light text) set here because the compiled CSS is dark-on-light for plain Bootstrap. --}}
    <style>
        body#app-2023 #map-page h1 { color: #FFFFFF; text-align: left; }
        body#app-2023 #map-page .lead, body#app-2023 #map-page label { color: #F1F5F9; }
        body#app-2023 #map-page .form-control { background-color: #161C32; border-color: rgba(255, 255, 255, .25); color: #F1F5F9; }
        body#app-2023 #map-page .form-control::placeholder { color: #94A3B8; }
        body#app-2023 #map-page .map-count { color: #CBD5E1; }
        #map-wrap { position: relative; z-index: 0; isolation: isolate; }
        #projects-map { height: 70vh; min-height: 420px; border-radius: .5rem; border: 1px solid rgba(255, 255, 255, .15); background: #0b1226; }
        /* OpenStreetMap only has a light style (no API key needed); inverting the tiles matches the dark site. */
        #projects-map .leaflet-tile-pane { filter: invert(1) hue-rotate(180deg) brightness(.85) contrast(.9) saturate(.7); }
        #projects-map .leaflet-bar a, #projects-map .leaflet-bar a:hover { background-color: #161C32; color: #F1F5F9; border-bottom-color: rgba(255, 255, 255, .2); text-decoration: none; }
        #projects-map .leaflet-bar a:hover { background-color: #1f2747; }
        #projects-map .leaflet-popup-content-wrapper, #projects-map .leaflet-popup-tip { background: #161C32; color: #F1F5F9; }
        #projects-map .leaflet-popup-content { margin: 14px 16px; max-height: 280px; overflow-y: auto; font-size: .9rem; line-height: 1.45; }
        #projects-map .leaflet-popup-content h3 { color: #FFFFFF; font-size: 1.05rem; margin: 0 0 .25rem; text-align: left; }
        #projects-map .leaflet-popup-content p { color: #F1F5F9; margin: 0 0 .5rem; }
        #projects-map .leaflet-popup-content .meta { color: #CBD5E1; }
        #projects-map .leaflet-popup-content a { color: #FF8A5C; text-decoration: underline; margin-right: .6rem; }
        #projects-map .leaflet-popup-close-button { color: #CBD5E1; }
        #projects-map .leaflet-popup-close-button:hover { color: #FFFFFF; }
        #projects-map .leaflet-control-attribution { background: rgba(9, 14, 31, .8); color: #CBD5E1; }
        #projects-map .leaflet-control-attribution a { color: #FF8A5C; }
    </style>

    <div id="map-page" class="container content-home pt-5 pb-5">
        <h1 class="mb-3">Mapa de projetos</h1>
        <p class="lead mb-4">Projetos de cidadania digital mapeados pelo Observatório Latino-Americano de Cidadania Digital. Clique em um ponto para conhecer o projeto.</p>

        @if($projects->isEmpty())
            <p class="map-count">Ainda não há projetos publicados no mapa.</p>
        @else
            <div class="form-row mb-3">
                <div class="col-12 col-md-4 mb-2">
                    <label for="map-search" class="small mb-1">Buscar</label>
                    <input id="map-search" type="search" class="form-control" placeholder="Nome, cidade ou proponente">
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <label for="map-country" class="small mb-1">País</label>
                    <select id="map-country" class="form-control"><option value="">Todos</option></select>
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <label for="map-theme" class="small mb-1">Linha temática</label>
                    <select id="map-theme" class="form-control"><option value="">Todas</option></select>
                </div>
            </div>
            <p id="map-count" class="map-count small" aria-live="polite"></p>
            <div id="map-wrap"><div id="projects-map" role="region" aria-label="Mapa com os projetos mapeados"></div></div>
        @endif
    </div>
@endsection

@section('inline_scripts')
    @if($projects->isNotEmpty())
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.min.js"></script>
        <script>
            (function () {
                var projects = @json($projects);
                var map = L.map('projects-map', { worldCopyJump: true }).setView([-15, -60], 3);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                var cluster = L.markerClusterGroup();
                map.addLayer(cluster);

                // Popups are built with textContent: the answers come from a spreadsheet and are never trusted as HTML.
                function el(tag, text, className) {
                    var node = document.createElement(tag);
                    if (text) node.textContent = text;
                    if (className) node.className = className;
                    return node;
                }
                function link(label, url) {
                    var a = el('a', label);
                    a.href = url; a.target = '_blank'; a.rel = 'noopener';
                    return a;
                }
                function popup(p) {
                    var box = el('div');
                    box.appendChild(el('h3', p.name));
                    var place = [p.city, p.country].filter(Boolean).join(', ');
                    if (place) box.appendChild(el('p', place, 'meta'));
                    if (p.proposers) box.appendChild(el('p', 'Proponente: ' + p.proposers + (p.nature ? ' (' + p.nature + ')' : ''), 'meta'));
                    if (p.purpose) box.appendChild(el('p', p.purpose));
                    if (p.description) box.appendChild(el('p', p.description));
                    if (p.themes.length) box.appendChild(el('p', 'Linhas temáticas: ' + p.themes.join(', '), 'meta'));
                    if (p.tics.length) box.appendChild(el('p', 'TICs: ' + p.tics.join(', '), 'meta'));
                    if (p.researcher) box.appendChild(el('p', 'Pesquisador responsável: ' + p.researcher, 'meta'));
                    var links = el('p');
                    [['Site', p.website], ['App', p.app], ['Instagram', p.instagram], ['Facebook', p.facebook], ['TikTok', p.tiktok]].forEach(function (l) {
                        if (l[1]) links.appendChild(link(l[0], l[1]));
                    });
                    if (links.childNodes.length) box.appendChild(links);
                    return box;
                }

                var markers = projects.map(function (p) {
                    return { project: p, marker: L.marker([p.lat, p.lng], { title: p.name }).bindPopup(popup(p), { maxWidth: 340 }) };
                });

                function fill(select, values) {
                    values.filter(function (v, i, a) { return v && a.indexOf(v) === i; }).sort().forEach(function (v) {
                        var o = document.createElement('option'); o.value = v; o.textContent = v; select.appendChild(o);
                    });
                }
                var country = document.getElementById('map-country'), theme = document.getElementById('map-theme'), search = document.getElementById('map-search');
                fill(country, projects.map(function (p) { return p.country; }));
                fill(theme, [].concat.apply([], projects.map(function (p) { return p.themes; })));

                function norm(s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); }
                function apply() {
                    var q = norm(search.value), shown = [];
                    cluster.clearLayers();
                    markers.forEach(function (m) {
                        var p = m.project;
                        if (country.value && p.country !== country.value) return;
                        if (theme.value && p.themes.indexOf(theme.value) === -1) return;
                        if (q && norm([p.name, p.city, p.country, p.proposers].join(' ')).indexOf(q) === -1) return;
                        shown.push(m.marker);
                    });
                    cluster.addLayers(shown);
                    document.getElementById('map-count').textContent = shown.length + (shown.length === 1 ? ' projeto' : ' projetos') + ' no mapa';
                    if (shown.length) map.fitBounds(cluster.getBounds().pad(0.2), { maxZoom: 6 });
                }
                [country, theme].forEach(function (s) { s.addEventListener('change', apply); });
                search.addEventListener('input', apply);
                apply();
            })();
        </script>
    @endif
@endsection
