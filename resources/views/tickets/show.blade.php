@extends('layouts.app')

@section('content')
<h1 class="col-md-12">All tickets</h1>

{!! $grid->show('grid-table') !!}
@endsection
