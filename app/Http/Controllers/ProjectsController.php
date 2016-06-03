<?php

namespace App\Http\Controllers;

use Auth;
use App\Project;
use App\Organization;
use App\Http\Requests\ProjectRequest;

class ProjectsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $projects = Project::where(['user_id' => Auth::user()->id])->get();
    	return view('projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Project $project, ProjectRequest $request)
    {
        $project->update($request->all());

        return redirect()->action('ProjectsController@show', $project->id);
    }

    public function create()
    {
        $organization_list = Organization::lists('name', 'id');
    	return view('projects.create', compact('organization_list'));
    }

    public function store(ProjectRequest $request)
    {
        Auth::user()->projects()->create($request->all());

        return redirect()->action('ProjectsController@index');
    }
}
