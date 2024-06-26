<?php

namespace App\Http\Controllers;

use App\Mail\JobPosted;
use App\Models\Employer;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class JobController extends Controller
{
    // public function index()
    // {
    //     $jobs = Job::with('employer')->latest()->paginate(5);

    //     return view('jobs.index', [
    //         'jobs' => $jobs
    //     ]);
    // }
    public function index()
    {
        // Obtiene el ID del usuario autenticado
        $userId = Auth::id();

        // Filtra los trabajos mostrando solo aquellos que pertenecen al employer del usuario autenticado
        $jobs = Job::whereHas('employer', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('employer')->latest()->paginate(5);

        return view('jobs.index', [
            'jobs' => $jobs
        ]);
    }

    public function create()
    {
        return view('jobs.create');
    }

    public function show(Job $job)
    {
        return view('jobs.show', ['job' => $job]);
    }

    // public function store()
    // {
    //     $userId = Auth::id();
    //     request()->validate([
    //         'title' => ['required', 'min:3'],
    //         'salary' => ['required']
    //     ]);

    //     $job = Job::create([
    //         'title' => request('title'),
    //         'salary' => request('salary'),
    //         'employer_id' => 1
    //     ]);

    //     Mail::to($job->employer->user)->queue(
    //         new JobPosted($job)
    //     );

    //     return redirect('/jobs');
    // }

    public function store()
    {
        request()->validate([
            'title' => ['required', 'min:3'],
            'salary' => ['required']
        ]);

        // Obtiene el employer asociado al usuario autenticado
        $employer = Employer::where('user_id', Auth::id())->first();

        $job = Job::create([
            'title' => request('title'),
            'salary' => request('salary'),
            'employer_id' => $employer->id  // Asigna el ID del employer del usuario autenticado
        ]);

        Mail::to($job->employer->user)->queue(
            new JobPosted($job)
        );

        return redirect('/jobs');
    }

    public function edit(Job $job)
    {
        return view('jobs.edit', ['job' => $job]);
    }

    public function update(Job $job)
    {
        Gate::authorize('edit', $job);

        request()->validate([
            'title' => ['required', 'min:3'],
            'salary' => ['required']
        ]);

        $job->update([
            'title' => request('title'),
            'salary' => request('salary'),
        ]);

        return redirect('/jobs/' . $job->id);
    }

    public function destroy(Job $job)
    {
        Gate::authorize('edit-job', $job);

        $job->delete();

        return redirect('/jobs');
    }
}
