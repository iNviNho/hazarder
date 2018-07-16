<table class="table matchbettable">
    <tr>
        @foreach ($matchBets->get() as $matchBet)
            <td>{{$matchBet->name}}</td>
        @endforeach
    </tr>
    <tr>
        @foreach ($matchBets->get() as $matchBet)
            <td><strong>{{$matchBet->value}}</strong></td>
        @endforeach
    </tr>
</table>
