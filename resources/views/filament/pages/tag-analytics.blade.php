<x-filament-panels::page>
    @php
        $usage = collect($this->getUsage());
        $max = max(1, (int) $usage->max('total'));
        $min = (int) $usage->where('total', '>', 0)->min('total');
    @endphp

    <x-filament::section heading="Nuvem de tags" description="O tamanho de cada tag indica a frequência de uso: os polos conceituais da plataforma.">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            @forelse ($usage->where('total', '>', 0) as $tag)
                @php $size = 0.85 + (($tag->total - $min) / max(1, $max - $min)) * 1.9; @endphp
                <span title="{{ $tag->total }} uso(s)" class="font-semibold text-primary-600" style="font-size: {{ round($size, 2) }}rem; opacity: {{ round(0.55 + 0.45 * ($tag->total / $max), 2) }}">
                    {{ $tag->name }}
                </span>
            @empty
                <p class="text-sm text-gray-500">Ainda não há tags em uso.</p>
            @endforelse
        </div>
    </x-filament::section>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section heading="Tags mais usadas">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1">Tag</th><th>Verbetes</th><th>Conteúdos</th><th>Séries</th><th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usage->take(30) as $tag)
                        <tr class="border-t border-gray-200 dark:border-white/10">
                            <td class="py-1">{{ $tag->name }}</td>
                            <td>{{ $tag->subjects_count }}</td>
                            <td>{{ $tag->resources_count }}</td>
                            <td>{{ $tag->series_count }}</td>
                            <td class="font-semibold">{{ $tag->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament::section>

        <x-filament::section heading="Tags usadas juntas" description="Quais conceitos costumam aparecer associados.">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500"><th class="py-1">Conceito A</th><th>Conceito B</th><th>Vezes juntos</th></tr>
                </thead>
                <tbody>
                    @forelse ($this->getCooccurrences() as $pair)
                        <tr class="border-t border-gray-200 dark:border-white/10">
                            <td class="py-1">{{ $pair['a'] }}</td>
                            <td>{{ $pair['b'] }}</td>
                            <td class="font-semibold">{{ $pair['count'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-2 text-gray-500">Nenhuma associação encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-filament::section>
    </div>
</x-filament-panels::page>
