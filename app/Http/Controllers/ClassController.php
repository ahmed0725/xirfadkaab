<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $classes = SchoolClass::withCount(['students', 'subjects'])->orderBy('class_name')->paginate(10);

        return view('classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('classes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string', 'max:255', 'unique:school_classes,class_name'],
            'classroom' => ['nullable', 'string', 'max:255'],
            'monthly_fee_amount' => ['required', 'numeric', 'min:0'],
            'shift' => ['required', 'string', 'in:morning,afternoon,evening'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        SchoolClass::create($validated);

        return redirect()->route('classes.index')->with('success', 'Class created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolClass $class): View
    {
        $class->load(['subjects', 'students']);

        return view('classes.show', ['schoolClass' => $class]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolClass $class): View
    {
        return view('classes.edit', ['schoolClass' => $class]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string', 'max:255', "unique:school_classes,class_name,{$class->id}"],
            'classroom' => ['nullable', 'string', 'max:255'],
            'monthly_fee_amount' => ['required', 'numeric', 'min:0'],
            'shift' => ['required', 'string', 'in:morning,afternoon,evening'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $class->update($validated);

        return redirect()->route('classes.index')->with('success', 'Class updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolClass $class): RedirectResponse
    {
        if ($class->students()->exists()) {
            return redirect()->route('classes.index')->withErrors([
                'class' => 'Cannot delete a class that still has students. Reassign or remove students first.',
            ]);
        }

        $class->delete();

        return redirect()->route('classes.index')->with('success', 'Class deleted.');
    }
}
