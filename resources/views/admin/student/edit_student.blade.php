<?php use App\Models\Section; ?>
<div class="eoff-form">
    <form method="POST" enctype="multipart/form-data" class="d-block ajaxForm" action="{{ route('admin.student.update', ['id' => $user->id]) }}">
         @csrf 
        <div class="form-row">
            <div class="fpb-7">
                <label for="name" class="eForm-label">{{ get_phrase('Name') }}</label>
                <input type="text" class="form-control eForm-control" value="{{ $user->name }}" id="name" name = "name" required>
            </div>

            <div class="fpb-7">
                <label for="email" class="eForm-label">{{ get_phrase('Email') }}</label>
                <input type="email" class="form-control eForm-control" value="{{ $user->email }}" id="email" name = "email" required>
            </div>

            <div class="fpb-7">
                <label for="class_id" class="eForm-label">{{ get_phrase("Class") }}</label>
                <select name="class_id" id="class_id" class="form-select eForm-select eChoice-multiple-with-remove" required onchange="classWiseSection(this.value)">
                    <option value="">{{ get_phrase('Select a class') }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $student_details['class_id'] == $class->id ?  'selected':'' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fpb-7">
                <label for="section_id" class="eForm-label">{{ get_phrase('Section') }}</label>
                <select name="section_id" id="section_id" class="form-select eForm-select eChoice-multiple-with-remove" required >
                    <?php if($student_details['section_id'] !=""){
                        $sections = Section::get()->where('class_id', $student_details['class_id']); ?>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" {{ $student_details['section_id'] == $section->id ?  'selected':'' }}>{{ $section->name }}</option>
                        @endforeach
                    <?php } else { ?>
                        <option value="">{{ get_phrase('First select a class') }}</option>
                    <?php } ?>
                </select>
            </div>

            <?php 
            $info = json_decode($user->user_information);
            ?>

            <div class="fpb-7">
                <label for="birthday" class="eForm-label">{{ get_phrase('Birthday') }}<span class="required"></span></label>
                <input type="text" class="form-control eForm-control inputDate" id="birthday" name="birthday" value="{{ date('m/d/Y', $info->birthday) }}" />
                </div>
            </div>

            <div class="fpb-7">
                <label for="gender" class="eForm-label">{{ get_phrase('Gender') }}</label>
                <select name="gender" id="gender" class="form-select eForm-select eChoice-multiple-with-remove"  required>
                    <option value="">{{ get_phrase('Select gender') }}</option>
                    <option value="Male" {{ $info->gender == 'Male' ?  'selected':'' }} >{{ get_phrase('Male') }}</option>
                    <option value="Female" {{ $info->gender == 'Female' ?  'selected':'' }}>{{ get_phrase('Female') }}</option>
                    <option value="Others" {{ $info->gender == 'Others' ?  'selected':'' }}>{{ get_phrase('Others') }}</option>
                </select>
            </div>

            <div class="fpb-7">
                <label for="phone" class="eForm-label">{{ get_phrase('Phone number') }}</label>
                <input type="text" class="form-control eForm-control" value="{{ $info->phone }}" id="phone" name = "phone" placeholder="Provide student number" required>
            </div>
            <div class="fpb-7">
                <label for="blood_group" class="eForm-label">{{ get_phrase('Blood group') }}</label>
                <select name="blood_group" id="blood_group" class="form-select eForm-select eChoice-multiple-with-remove">
                    <option value="">{{ get_phrase('Select a blood group') }}</option>
                    <option value="a+" {{ $info->blood_group == 'a+' ?  'selected':'' }} >{{ get_phrase('A+') }}</option>
                    <option value="a-" {{ $info->blood_group == 'a-' ?  'selected':'' }} >{{ get_phrase('A-') }}</option>
                    <option value="b+" {{ $info->blood_group == 'b+' ?  'selected':'' }} >{{ get_phrase('B+') }}</option>
                    <option value="b-" {{ $info->blood_group == 'b-' ?  'selected':'' }} >{{ get_phrase('B-') }}</option>
                    <option value="ab+" {{ $info->blood_group == 'ab+' ?  'selected':'' }} >{{ get_phrase('AB+') }}</option>
                    <option value="ab-" {{ $info->blood_group == 'ab-' ?  'selected':'' }} >{{ get_phrase('AB-') }}</option>
                    <option value="o+" {{ $info->blood_group == 'o+' ?  'selected':'' }} >{{ get_phrase('O+') }}</option>
                    <option value="o-" {{ $info->blood_group == 'o-' ?  'selected':'' }} >{{ get_phrase('O-') }}</option>
                </select>
            </div>

            <div class="fpb-7">
                <label for="phone" class="eForm-label">{{ get_phrase('Address') }}</label>
                <textarea class="form-control eForm-control" id="address" name = "address" rows="5" placeholder="Provide student address" required>{{ $info->address }}</textarea>
            </div>

            <div class="fpb-7">
              <label for="formFile" class="eForm-label"
                >{{ get_phrase('Photo') }}</label
              >
              <input
                class="form-control eForm-control-file"
                id="photo" name="photo" accept="image/*"
                type="file"
              />
            </div>

            <div class="fpb-7 pt-2">
                <button class="btn-form" type="submit">{{ get_phrase('Update') }}</button>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    "use strict";
    $(document).ready(function () {
      $(".eChoice-multiple-with-remove").select2();
    });

    function classWiseSection(classId) {
        let url = "{{ route('admin.class_wise_sections', ['id' => ":classId"]) }}";
        url = url.replace(":classId", classId);
        $.ajax({
            url: url,
            success: function(response){
                $('#section_id').html(response);
            }
        });
    }

    $(function () {
      $('.inputDate').daterangepicker(
        {
          singleDatePicker: true,
          showDropdowns: true,
          minYear: 1901,
          maxYear: parseInt(moment().format("YYYY"), 10),
        },
        function (start, end, label) {
          var years = moment().diff(start, "years");
        }
      );
    });
</script>