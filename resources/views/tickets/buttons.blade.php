@if ($ticket->status == "prepared")
    {{--approved buttons--}}
    <a href="/tickets/approve/{{$ticket->id}}" class="btn btn-sm btn-success">APPROVE</a>
    <a href="/tickets/disapprove/{{$ticket->id}}" class="btn btn-sm btn-danger">DISAPPROVE</a>
@elseif ($ticket->status == "approved")
    <a href="/tickets/bet/{{$ticket->id}}" class="btn btn-sm btn-warning">BET</a>
    <a href="/tickets/disapprove/{{$ticket->id}}" class="btn btn-sm btn-danger">DISAPPROVE</a>
@elseif ($ticket->status == "bet")
    <a href="/tickets/checkresult/{{$ticket->id}}" class="btn btn-sm btn-warning">CHECK RESULT</a>
@endif