<div class="eoff-form">
    <form method="POST" enctype="multipart/form-data" class="d-block ajaxForm" action="{{ route('admin.grade.update', ['id' => $grade->id]) }}">
        @csrf 
        <div class="form-row">
            <div class="fpb-7">
                <label for="grade" class="eForm-label">{{ get_phrase('Grade') }}</label>
                <input type="text" class="form-control eForm-control" value="{{ $grade->name }}" id="grade" name = "grade" required>
            </div>

            <div class="fpb-7">
                <label for="grade_point" class="eForm-label">{{ get_phrase('Grade point') }}</label>
                <input type="number" class="form-control eForm-control" id="grade_point" name = "grade_point" step=".01" min="0" value="{{ $grade->grade_point }}" placeholder="Provide grade point" required>
            </div>

            <div class="fpb-7">
                <label for="mark_from" class="eForm-label">{{ get_phrase('Mark From') }}</label>
                <input type="number" class="form-control eForm-control" id="mark_from" name = "mark_from" min="0" value="{{ $grade->mark_from }}" placeholder="Mark from" required>
            </div>

            <div class="fpb-7">
                <label for="mark_upto" class="eForm-label">{{ get_phrase('Mark upto') }}</label>
                <input type="number" class="form-control eForm-control" id="mark_upto" name = "mark_upto" min="0" value="{{ $grade->mark_upto }}" placeholder="Mark upto" required>
            </div>

            <div class="fpb-7 pt-2">
                <button class="btn-form" type="submit">{{ get_phrase('Update') }}</button>
            </div>
        </div>
    </form>
</div>