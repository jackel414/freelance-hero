@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">All Organizations</div>

                <div class="panel-body">
                    @if ( count($organizations) > 0 )
                        @foreach ( $organizations as $organization )
                            <p>Name: {{ $organization->name }}</p>
                        @endforeach
                    @else
                        <p>No active organizations.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection