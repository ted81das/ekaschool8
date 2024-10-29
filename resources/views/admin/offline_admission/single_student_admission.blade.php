<div class="eForm-layouts">
    <form method="POST" enctype="multipart/form-data" class="d-block ajaxForm" action="{{ route('admin.offline_admission.create') }}">
        @csrf 
        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="name" class="col-sm-2 col-eForm-label">{{ get_phrase('Name') }}</label>
            <div class="col-sm-10 col-md-9 col-lg-10">
                <input type="text" class="form-control eForm-control" id="name" name="name" required>
            </div>
        </div>
        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="email"
                class="col-sm-2 col-eForm-label">{{ get_phrase('Email') }}</label>
            <div class="col-sm-10 col-md-9 col-lg-10">
                <input type="email" class="form-control eForm-control" id="email" name="email" required>
            </div>
        </div>
        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="password"
                class="col-sm-2 col-eForm-label">{{ get_phrase('Password') }}</label>
            <div class="col-sm-10 col-md-9 col-lg-10">
                <input type="password" class="form-control eForm-control" id="password" name="password" required>
            </div>
        </div>

        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="class_id" class="col-sm-2 col-eForm-label">{{ get_phrase('Class') }}</label>
            <div class="col-md-10">
                <select name="class_id" id="class_id" class="form-select eForm-select eChoice-multiple-with-remove" required onchange="classWiseSection(this.value)">
                    <option value="">{{ get_phrase('Select a class') }}</option>
                    @foreach($data['classes'] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="section_id" class="col-sm-2 col-eForm-label">{{ get_phrase('Section') }}</label>
            <div class="col-md-10" id = "section_content">
                <select name="section_id" id="section_id" class="form-select eForm-select eChoice-multiple-with-remove" required >
                    <option value="">{{ get_phrase('Select section') }}</option>
                </select>
            </div>
        </div>

        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="birthdatepicker" class="col-sm-2 col-eForm-label">{{ get_phrase('Birthday') }}<span class="required"></span></label>
            <div class="col-md-10">
                <input type="text" class="form-control eForm-control" id="eInputDate" name="eDefaultDateRange" value="{{ date('m/d/Y') }}" />
            </div>
        </div>

        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="gender" class="col-sm-2 col-eForm-label">{{ get_phrase('Gender') }}</label>
            <div class="col-md-10">
                <select name="gender" id="gender" class="form-select eForm-select eChoice-multiple-with-remove"  required>
                    <option value="">{{ get_phrase('Select gender') }}</option>
                    <option value="Male">{{ get_phrase('Male') }}</option>
                    <option value="Female">{{ get_phrase('Female') }}</option>
                    <option value="Others">{{ get_phrase('Others') }}</option>
                </select>
            </div>
        </div>

        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="blood_group" class="col-sm-2 col-eForm-label">{{ get_phrase('Blood group') }}</label>
            <div class="col-md-10">
                <select name="blood_group" id="blood_group" class="form-select eForm-select eChoice-multiple-with-remove"  required>
                    <option value="">{{ get_phrase('Select a blood group') }}</option>
                    <option value="a+">{{ get_phrase('A+') }}</option>
                    <option value="a-">{{ get_phrase('A-') }}</option>
                    <option value="b+">{{ get_phrase('B+') }}</option>
                    <option value="b-">{{ get_phrase('B-') }}</option>
                    <option value="ab+">{{ get_phrase('AB+') }}</option>
                    <option value="ab-">{{ get_phrase('AB-') }}</option>
                    <option value="o+">{{ get_phrase('O+') }}</option>
                    <option value="o-">{{ get_phrase('O-') }}</option>
                </select>
            </div>
        </div>

        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="address" class="col-sm-2 col-eForm-label">{{ get_phrase('Address') }}</label>
            <div class="col-md-10">
                <textarea class="form-control eForm-control" id="address" rows="4" name = "address" required></textarea>
            </div>
        </div>

        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="phone" class="col-sm-2 col-eForm-label">{{ get_phrase('Phone') }}</label>
            <div class="col-md-10">
                <input type="text" id="phone" name="phone" class="form-control eForm-control" required>
            </div>
        </div>

        <div class="row fmb-14 justify-content-between align-items-center">
            <label for="photo" class="col-sm-2 col-eForm-label">{{ get_phrase('Student profile image') }}</label>
            <div class="col-md-10">
                <input class="form-control eForm-control-file" type="file" id="photo" name="photo" accept="image/*">
            </div>
        </div>

        <div class="row">
            <div class="col-sm-10 offset-sm-2">
                <button type="submit" class="btn-form">{{ get_phrase('Add Student') }}</button>
            </div>
        </div>
    </form>
</div>