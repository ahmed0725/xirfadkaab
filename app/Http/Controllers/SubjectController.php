<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $subjects = Subject::with('schoolClass')->latest()->paginate(10);
        $classes = SchoolClass::orderBy('class_name')->get();

        return view('subjects.index', compact('subjects', 'classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $classes = SchoolClass::orderBy('class_name')->get();

        return view('subjects.create', compact('classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects')->where(fn ($query) => $query->where('school_class_id', $request->school_class_id)),
            ],
        ]);

        Subject::create($validated);

        return redirect()->route('subjects.index')->with('success', 'Subject assigned to class.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subject $subject): View
    {
        $subject->load('schoolClass');

        return view('subjects.show', compact('subject'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject): View
    {
        $classes = SchoolClass::orderBy('class_name')->get();

        return view('subjects.edit', compact('subject', 'classes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects')
                    ->ignore($subject->id)
                    ->where(fn ($query) => $query->where('school_class_id', $request->school_class_id)),
            ],
        ]);

        $subject->update($validated);

        return redirect()->route('subjects.index')->with('success', 'Subject updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted.');
    }
}
