<x-filament-panels::page>
    @php $result = $this->getResult(); @endphp

    <x-filament::section
        :heading="$result['ok'] ? 'Tudo certo para enviar imagens' : 'Há problemas que impedem o envio ou a exibição de imagens'"
        description="Estes valores são os que o navegador encontra no servidor (não os do terminal)."
    >
        <table class="w-full text-sm">
            <tbody>
                @foreach ($result['rows'] as $row)
                    <tr class="border-t border-gray-200 dark:border-white/10 align-top">
                        <td class="py-2 pr-4 font-medium whitespace-nowrap">{{ $row['label'] }}</td>
                        <td @class([
                            'py-2 break-all',
                            'text-success-600' => $row['status'] === 'ok',
                            'text-warning-600' => $row['status'] === 'warn',
                            'text-danger-600 font-semibold' => $row['status'] === 'error',
                        ])>{{ $row['value'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::section>
</x-filament-panels::page>
