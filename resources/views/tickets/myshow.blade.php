@extends('layouts.app')

@section('content')
    <div class="container my-container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <div class="basic-content">
                    <h1>My Tickets</h1>

                    <div>
                        {!! $grid->render() !!}
                    </div>
                </div>


            </div>
        </div>
    </div>
@endsection
