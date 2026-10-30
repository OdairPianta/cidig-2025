<div>
    <p>Olá, <b>{{ $ticket->responsible->name }}</b>, recebemos este ticket:</p>

    <p><b>Tipo de ticket:</b> {{ $ticket->getTicketTypeDescription() }} </p>
    <p><b>Usuário: </b> {{ $user->name }}</p>
    <p><b>Email: </b> {{ $user->email }}</p>
    <p><b>Data: </b> {{ now()->format('Y-m-d H:i') }}</p>
    <p>
        <b>Título: </b>
        <a href="{{ route('tickets.show', $ticket->id) }}" class="text-reset">
            <b>{{ $ticket->title }}</b>
        </a>
    </p>
    @if($ticket->page_url)
    <p><b>Página: </b> <a href="{{ $ticket->page_url }}">{{ $ticket->page_url }}</a></p>
    @endif
    <p><b>Mensagem: </b></p>
    <p>{{ $ticket->description }}</p>
</div>
