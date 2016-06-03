@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">{{ $project->name }}</div>

                <div class="panel-body">
                    <p>Status: {{ $project->status }}</p>
                    <p>Start Date: {{ $project->start_date }}</p>
                    <p>Target End Date: {{ $project->target_end_date }}</p>
                    @if ( $project->end_date )
                    <p>Date Completed: {{ $project->end_date }}</p>
                    @endif
                    <p><a href="{{ action( 'ProjectsController@edit', $project->id ) }}">Edit Project</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection