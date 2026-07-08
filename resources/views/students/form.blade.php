<input name="name" value="{{ old('name', $student?->name) }}" class="form-control" placeholder="Name" required>
<input name="mother_name" value="{{ old('mother_name', $student?->mother_name) }}" class="form-control" placeholder="Mother Name" required>
<input name="phone" value="{{ old('phone', $student?->phone) }}" class="form-control" placeholder="Phone" required>
<input type="number" name="age" value="{{ old('age', $student?->age) }}" class="form-control" placeholder="Age" required>
<select name="gender" class="form-control" required><option value="">Gender</option><option value="male" @selected(old('gender',$student?->gender)==='male')>Male</option><option value="female" @selected(old('gender',$student?->gender)==='female')>Female</option></select>
<select name="school_class_id" class="form-control" required><option value="">Class</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(old('school_class_id',$student?->school_class_id)==$class->id)>{{ $class->display_name }}</option>@endforeach</select>
<select name="status" class="form-control" required><option value="active" @selected(old('status',$student?->status)==='active')>Active</option><option value="inactive" @selected(old('status',$student?->status)==='inactive')>Inactive</option></select>
<select name="fee_type" class="form-control" required>
    @foreach(\App\Models\Student::FEE_TYPES as $value => $label)
        <option value="{{ $value }}" @selected(old('fee_type', $student?->fee_type ?? \App\Models\Student::FEE_TYPE_REGULAR) === $value)>{{ $label }}</option>
    @endforeach
</select>
<input type="date" name="registration_date" value="{{ old('registration_date', optional($student?->registration_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="form-control md:col-span-2" required>
<div class="md:col-span-2 flex gap-2"><button class="btn-primary">Save</button><a href="{{ route('students.index') }}" class="btn-secondary">Cancel</a></div>
