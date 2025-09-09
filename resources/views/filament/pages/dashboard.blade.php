<x-filament::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- content section -->
        <x-filament::section>
            <x-slot name="heading">Seus conteúdos recentes</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y table-auto">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="p-2 text-gray-900 dark:text-gray-100">Título</th>
                            <th class="p-2 text-gray-900 dark:text-gray-100">Tipo</th>
                            <th class="p-2 text-gray-900 dark:text-gray-100">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach(\App\Models\Resource::where('created_by', auth()->id())->latest()->limit(5)->get() as $resource)
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="p-2 text-gray-900 dark:text-gray-100">{{ $resource->title }}</td>
                            <td class="p-2">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($resource->format_id == 1) bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @elseif($resource->format_id == 2) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($resource->format_id == 3) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                    {{ $resource->getFormatDescription() }}
                                </span>
                            </td>
                            <td class="p-2 text-gray-900 dark:text-gray-100">{{ $resource->created_at->format('d/m/y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <!-- Create content section -->
        <x-filament::section>
            <x-slot name="heading">Criar conteúdo</x-slot>
            <div>
                <p class="mab-4 text-gray-700 dark:text-gray-300">Qual conteúdo você deseja criar hoje?</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('filament.admin.resources.resources.create') }}" class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center space-x-3">
                        <span class="text-primary-500">
                            <x-heroicon-o-document class="w-6 h-6" />
                        </span>
                        <span class="text-gray-900 dark:text-gray-100">Novo verbete</span>
                    </a>
                    
                    <a href="{{ route('filament.admin.resources.series.create') }}?format_id=2" class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center space-x-3">
                        <span class="text-primary-500">
                            <x-heroicon-o-film class="w-6 h-6" />
                        </span>
                        <span class="text-gray-900 dark:text-gray-100">Nova websérie</span>
                    </a>
                </div>
            </div>
        </x-filament::section>
    </div>

    
    <x-filament::section>
        <x-slot name="heading">Tickets</x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y table-auto">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="p-2 text-gray-900 dark:text-gray-100">Título</th>
                        <th class="p-2 text-gray-900 dark:text-gray-100">Criador</th>
                        <th class="p-2 text-gray-900 dark:text-gray-100">Responsável</th>
                        <th class="p-2 text-gray-900 dark:text-gray-100">Data</th>
                        <th class="p-2 text-gray-900 dark:text-gray-100">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach(\App\Models\Ticket::where('created_by', auth()->id())->orWhere('responsible_id', auth()->id())->latest()->limit(7)->get() as $ticket)
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="p-2">
                            <a href="{{ route('filament.admin.resources.tickets.show', $ticket) }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300">
                                {{ $ticket->title }}
                            </a>
                        </td>
                        <td class="p-2 text-gray-900 dark:text-gray-100">{{ $ticket->createdBy->name }}</td>
                        <td class="p-2 text-gray-900 dark:text-gray-100">{{ $ticket->responsible ? $ticket->responsible->name : '-' }}</td>
                        <td class="p-2 text-gray-900 dark:text-gray-100">{{ $ticket->created_at->format('d/m/y') }}</td>
                        <td class="p-2">
                            <span class="px-2 py-1 text-xs rounded-full
                                @switch($ticket->status)
                                    @case(0) bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @break
                                    @case(1) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @break
                                    @case(2) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @break
                                    @case(3) bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @break
                                    @default bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                @endswitch">
                                @switch($ticket->status)
                                    @case(0) Aberto @break
                                    @case(1) Em progresso @break
                                    @case(2) Realizado @break
                                    @case(3) Fechado - Não realizado @break
                                    @default Desconhecido
                                @endswitch
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament::page>