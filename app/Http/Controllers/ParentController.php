<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\Session;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Enrollment;
use Illuminate\Support\Str;
use App\Models\Gradebook;
use App\Models\Subject;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Exam;
use App\Models\ExamCategory;
use App\Models\Grade;
use App\Models\ClassList;
use App\Models\DailyAttendances;
use App\Models\Routine;
use App\Models\Syllabus;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Noticeboard;
use App\Models\FrontendEvent;

use App\Models\Admin;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\StudentFeeManager;
use App\Models\Payments;
use App\Models\Feedback;
use App\Models\PaymentMethods;

use Illuminate\Foundation\Auth\User as AuthUser;
use PhpParser\Builder\Class_;

class ParentController extends Controller
{
    public function parentDashboard()
    {
        return view('parent.dashboard');
    }

    public function teacherList(Request $request)
    {
        $search = $request['search'] ?? "";

        if($search != "") {

            $teachers = User::where(function ($query) use($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                        ->where('school_id', auth()->user()->school_id)
                        ->where('role_id', 3);
                })->paginate(10);

        } else {
            $teachers = User::where('role_id', 3)->where('school_id', auth()->user()->school_id)->paginate(10);
        }

        return view('parent.user.teacher_list', compact('teachers', 'search'));
    }

    public function childList(Request $request)
    {
        $search = $request['search'] ?? "";

        if($search != "") {

            $students = User::where(function ($query) use($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->where('parent_id', auth()->user()->id)
                    ->where('school_id', auth()->user()->school_id)
                    ->where('role_id', 7);
            })->paginate(10);

        } else {
            $students = User::where('role_id', 7)->where('parent_id', auth()->user()->id)->where('school_id', auth()->user()->school_id)->paginate(10);
        }

        return view('parent.user.child_list', compact('students', 'search'));
    }

    public function studentIdCardGenerate($id)
    {
        $student_details = (new CommonController)->get_student_details_by_id($id);
        return view('parent.user.id_card', ['student_details' => $student_details]);
    }

    public function FeeManagerList(Request $request)
    { //parent

        $active_session = get_school_settings(auth()->user()->school_id)->value('running_session');
        $details_of_chilren = User::where('parent_id', auth()->user()->id)->get()->toArray();
        $invoices = "i";


        if (count($request->all()) > 0) {

            $data = $request->all();
            $date = explode('-', $data['eDateRange']);
            $date_from = strtotime($date[0] . ' 00:00:00');
            $date_to  = strtotime($date[1] . ' 23:59:59');
            $selected_status = $data['status'];

            if ($selected_status != "all") {
                $invoices = StudentFeeManager::where('timestamp', '>=', $date_from)->where('timestamp', '<=', $date_to)->where('status', $selected_status)->where('parent_id', auth()->user()->id)->where('session_id', $active_session)->get();
            } else if ($selected_status == "all") {
                $invoices = StudentFeeManager::where('timestamp', '>=', $date_from)->where('timestamp', '<=', $date_to)->where('school_id', auth()->user()->school_id)->where('parent_id', auth()->user()->id)->where('session_id', $active_session)->get();
            }


            return view('parent.fee_manager.student_fee_manager', ['invoices' => $invoices, 'date_from' => $date_from, 'date_to' => $date_to,  'selected_status' => $selected_status]);
        } else {

            $date_from = strtotime(date('d-M-Y', strtotime(' -30 day')) . ' 00:00:00');
            $date_to = strtotime(date('d-M-Y') . ' 23:59:59');
            $selected_status = "";

            $invoices = StudentFeeManager::where('timestamp', '>=', $date_from)->where('timestamp', '<=', $date_to)->where('parent_id', auth()->user()->id)->where('school_id', auth()->user()->school_id)->where('session_id', $active_session)->get();


            return view('parent.fee_manager.student_fee_manager', ['invoices' => $invoices, 'date_from' => $date_from, 'date_to' => $date_to,  'selected_status' => $selected_status]);
        }
    }

    public function FeePayment(Request $request, $id)
    {

        $fee_details = StudentFeeManager::where('id', $id)->first()->toArray();
        $user_info = User::where('id', auth()->user()->id)->first()->toArray();
        return view('parent.payment.payment_gateway', ['fee_details' => $fee_details, 'user_info' => $user_info]);
    }

    public function feeManagerExport($date_from = "", $date_to = "", $selected_status = "")
    {

        $active_session = get_school_settings(auth()->user()->school_id)->value('running_session');


        if ($selected_status != "all") {
            $invoices = StudentFeeManager::where('timestamp', '>=', $date_from)->where('timestamp', '<=', $date_to)->where('status', $selected_status)->where('parent_id', auth()->user()->id)->where('session_id', $active_session)->get();
        } else if ($selected_status == "all") {
            $invoices = StudentFeeManager::where('timestamp', '>=', $date_from)->where('timestamp', '<=', $date_to)->where('school_id', auth()->user()->school_id)->where('parent_id', auth()->user()->id)->where('session_id', $active_session)->get();
        }

        $classes = Classes::where('school_id', auth()->user()->school_id)->get();



        $file = "student_fee-" . date('d-m-Y', $date_from) . '-' . date('d-m-Y', $date_to) . '-' . $selected_status . ".csv";

        $csv_content = get_phrase('Invoice No') . ', ' . get_phrase('Student') . ', ' . get_phrase('Class') . ', ' . get_phrase('Invoice Title') . ', ' . get_phrase('Total Amount') . ', ' . get_phrase('Created At') . ', ' . get_phrase('Paid Amount') . ', ' . get_phrase('Status');

        foreach ($invoices as $invoice) {
            $csv_content .= "\n";

            $student_details = (new CommonController)->get_student_details_by_id($invoice['student_id']);
            $invoice_no = sprintf('%08d', $invoice['id']);

            $csv_content .= $invoice_no . ', ' . $student_details['name'] . ', ' . $student_details['class_name'] . ', ' . $invoice['title'] . ', ' . currency($invoice['total_amount']) . ', ' . date('d-M-Y', $invoice['timestamp']) . ', ' . currency($invoice['paid_amount']) . ', ' . $invoice['status'];
        }
        $txt = fopen($file, "w") or die("Unable to open file!");
        fwrite($txt, $csv_content);
        fclose($txt);

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $file);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        header("Content-type: text/csv");
        readfile($file);
    }

    public function studentFeeinvoice(Request $request, $id)
    {

        $invoice_details = StudentFeeManager::find($id)->toArray();
        $student_details = (new CommonController)->get_student_details_by_id($invoice_details['student_id'])->toArray();

        return view('parent.fee_manager.invoice', ['invoice_details' => $invoice_details, 'student_details' => $student_details]);
    }

    public function gradeList()
    {
        $grades = Grade::where('school_id', auth()->user()->school_id)->paginate(10);
        return view('parent.grade.grade_list', compact('grades'));
    }

    public function subjectList()
    {


        $active_session = get_school_settings(auth()->user()->school_id)->value('running_session');
        $student_data = User::where('parent_id', auth()->user()->id)->where('school_id', auth()->user()->school_id)->get();
        if (!empty($student_data)) {
            $student_data = $student_data->toArray();
        }

        return view('parent.subject.subject_list', compact('student_data'));
    }

    public function subjectList_by_student_name(Request $request)
    {
        $data = $request->all();
        $class_id = Enrollment::where('user_id', $data['user_id'])->first()->toArray();
        $class_name = Classes::where('id', $class_id['class_id'])->get()->toArray();
        $subjects = Subject::where('class_id', $class_id['class_id'])->get()->toArray();
        return view('parent.subject.table', ['class_name' => $class_name, 'subjects' => $subjects]);
    }

   

    public function offlinePayment(Request $request, $id = "")
    {
        $data = $request->all();

        if ($data['amount'] > 0) {

            $file = $data['document_image'];

            if ($file) {
                $filename = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension(); //Get extension of uploaded file

                
                $file->move(public_path('assets/uploads/offline_payment'), $filename);
                $data['document_image'] = $filename;

            } else {
                $data['document_image'] = '';
            }

            StudentFeeManager::where('id',  $id)->update([
                'status' => 'pending',
                'document_image'=> $data['document_image'],
                'payment_method' => 'offline'
            ]);





            return redirect()->route('parent.fee_manager.list')->with('message', 'offline payment requested successfully');
        }else{
            return redirect()->route('parent.fee_manager.list')->with('message', 'offline payment requested fail');
        }


    }

    public function syllabusList()
    {


        $active_session = get_school_settings(auth()->user()->school_id)->value('running_session');
        $student_data = User::where('parent_id', auth()->user()->id)->where('school_id', auth()->user()->school_id)->get();
        if (!empty($student_data)) {
            $student_data = $student_data->toArray();
        }

        return view('parent.syllabus.syllabus_list', compact('student_data'));
    }

    public function syllabusList_by_student_name(Request $request)
    {
        $data = $request->all();
        $class_id = Enrollment::where('user_id', $data['user_id'])->first()->toArray();
        $class_name = Classes::where('id', $class_id['class_id'])->get()->toArray();
        $syllabus = Syllabus::where('class_id', $class_id['class_id'])->get()->toArray();

        return view('parent.syllabus.table', ['class_name' => $class_name, 'syllabus' => $syllabus]);
    }

    public function routine()
    {
        $child = User::where('parent_id', auth()->user()->id)->where('school_id', auth()->user()->school_id)->get();
        $student_data = array();

        if (!empty($child)) {
            $child = $child->toArray();
            foreach ($child as $info) {
                $each_child = (new CommonController)->get_student_academic_info($info['id']);

                $each_child = $each_child->toArray();
                array_push($student_data, $each_child);
            }
        }




        return view('parent.routine.routine', ['student_data' => $student_data]);
    }

    public function routineList(Request $request)
    {
        $data = $request->all();

        $student_id = $data['student_id'];

        $academic_info = Enrollment::where('user_id', $student_id)->first()->toArray();

        $classes = Classes::where('school_id', auth()->user()->school_id)->get();

        return view('parent.routine.routine_list', ['student_id' => $student_id, 'class_id' => $academic_info['class_id'], 'section_id' => $academic_info['section_id'], 'classes' => $classes]);
    }


    public function list_of_attendence(Request $request)
    {
        
        $child = User::where('parent_id', auth()->user()->id)->where('school_id', auth()->user()->school_id)->get();
        $child_data = array();

        if (!empty($child)) {
            $child = $child->toArray();
            foreach ($child as $info) {
                $each_child = (new CommonController)->get_student_academic_info($info['id']);

                $each_child = $each_child->toArray();
                array_push($child_data, $each_child);
            }
        }

        

        if(!empty($request->all())){
            $data = $request->all();
            $date = '01 '.$data['month'].' '.$data['year'];
            $page_data['attendance_date'] = strtotime($date);
            $page_data['month'] = $data['month'];
            $page_data['year'] = $data['year'];
            $student_data = (new CommonController)->get_student_academic_info($data['student_id']);

            $first_date = strtotime($date);

            $last_date = date("Y-m-t", strtotime($date));
            $last_date = strtotime($last_date);

            $attendance_of_students = DailyAttendances::whereBetween('timestamp', [$first_date, $last_date])->where(['class_id' => $student_data['class_id'], 'section_id' => $student_data['class_id'], 'student_id' => $student_data['user_id']])->get();

            $no_of_users = DailyAttendances::where(['class_id' => $student_data['class_id'], 'section_id' => $student_data['section_id'], 'student_id' => $student_data['user_id'], 'school_id' => auth()->user()->school_id])->distinct()->count('student_id');

        } else {

            $date = '01 '.date('M').' '.date('Y');
            $page_data['attendance_date'] = strtotime($date);
            $page_data['month'] = date('M');
            $page_data['year'] = date('Y');
            $attendance_of_students = array();
            $student_data = array();
            $no_of_users = array();
        }



        return view('parent.attendence.list_of_attendence', ['child_data' => $child_data, 'page_data' => $page_data, 'attendance_of_students' => $attendance_of_students, 'student_data' => $student_data, 'no_of_users' => $no_of_users]);
    }

    public function dailyAttendanceFilter_csv(Request $request)
    {

        $data = $request->all();

        $store_get_data=array_keys($data);


        $data['month']= substr($store_get_data[0],0,3);
        $data['year']= substr($store_get_data[0],4,4);
        $data['role_id']=substr($store_get_data[0],9,5);

        $active_session = get_school_settings(auth()->user()->school_id)->value('running_session');

      
        $date = '01 ' . $data['month'] . ' ' . $data['year'];


        $first_date = strtotime($date);

        $last_date = date("Y-m-t", strtotime($date));
        $last_date = strtotime($last_date);

        $page_data['month'] = $data['month'];
        $page_data['year'] = $data['year'];
        $page_data['attendance_date'] = $first_date;
        $no_of_users = 0;


        $no_of_users = DailyAttendances::whereBetween('timestamp', [$first_date, $last_date])->where(['school_id' => auth()->user()->school_id,  'session_id' => $active_session])->distinct()->count('student_id');
        $attendance_of_students = DailyAttendances::whereBetween('timestamp', [$first_date, $last_date])->where(['school_id' => auth()->user()->school_id, 'student_id' => auth()->user()->id, 'session_id' => $active_session])->get()->toArray();
       

        $csv_content ="Student"."/".get_phrase('Date');
        $number_of_days = date('m', $page_data['attendance_date']) == 2 ? (date('Y', $page_data['attendance_date']) % 4 ? 28 : (date('m', $page_data['attendance_date']) % 100 ? 29 : (date('m', $page_data['attendance_date']) % 400 ? 28 : 29))) : ((date('m', $page_data['attendance_date']) - 1) % 7 % 2 ? 30 : 31);
        for ($i = 1; $i <= $number_of_days; $i++)
        {
            $csv_content .=','.get_phrase($i);

        }


        $file = "Attendence_report.csv";


        $student_id_count = 0;


        foreach(array_slice($attendance_of_students, 0, $no_of_users) as $attendance_of_student ){
            $csv_content .= "\n";

            $user_details = (new CommonController)->get_user_by_id_from_user_table($attendance_of_student['student_id']);
            if(date('m', $page_data['attendance_date']) == date('m', $attendance_of_student['timestamp'])) {
                
                if($student_id_count != $attendance_of_student['student_id']) {
                    
                    $csv_content .= $user_details['name'] . ',';

                    for ($i = 1; $i <= $number_of_days; $i++) {

                        $page_data['date'] = $i.' '.$page_data['month'].' '.$page_data['year'];
                        $timestamp = strtotime($page_data['date']);

                        $attendance_by_id = DailyAttendances::where([ 'student_id' => $attendance_of_student['student_id'], 'school_id' => auth()->user()->school_id, 'timestamp' => $timestamp])->first();

                        if(isset($attendance_by_id->status) && $attendance_by_id->status == 1){
                            $csv_content .= "P,";
                        }elseif(isset($attendance_by_id->status) && $attendance_by_id->status == 0){
                            $csv_content .= "A,";
                        }
                        else
                        {
                            $csv_content .= ",";

                        }


                        if($i==$number_of_days)
                        {
                            $csv_content= substr_replace($csv_content,"", -1);
                        }
                    }
                }

                $student_id_count = $attendance_of_student['student_id'];
            }
        }

        $txt = fopen($file, "w") or die("Unable to open file!");
        fwrite($txt, $csv_content);
        fclose($txt);

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $file);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        header("Content-type: text/csv");
        readfile($file);
    }

    public function marks()
    {

        $child = User::where('parent_id', auth()->user()->id)->where('school_id', auth()->user()->school_id)->get();
        $student_data = array();

        if (!empty($child)) {
            $child = $child->toArray();
            foreach ($child as $info) {
                $each_child = (new CommonController)->get_student_academic_info($info['id']);

                $each_child = $each_child->toArray();
                array_push($student_data, $each_child);
            }
        }

        return view('parent.marks.index', ['student_data' => $student_data]);
    }

    public function marks_list(Request $request, $value = '')
    {
        $data = $request->all();
        $exam_categories = ExamCategory::where('school_id', auth()->user()->school_id)->get();
        $user_id = $data['student_id'];
        $student_details = (new CommonController)->get_student_details_by_id($user_id);

        $subjects = Subject::where(['class_id' => $student_details['class_id'], 'school_id' => auth()->user()->school_id])->get();


        return view('parent.marks.table', ['exam_categories' => $exam_categories, 'student_details' => $student_details, 'subjects' => $subjects]);
    }

    public function noticeboardList()
    {

        $notices = Noticeboard::get()->where('school_id', auth()->user()->school_id);

        $events = array();

        foreach ($notices as $notice) {
            if ($notice['end_date'] != "") {
                if ($notice['start_date'] != $notice['end_date']) {
                    $end_date = strtotime($notice['end_date']) + 24 * 60 * 60;
                    $end_date = date('Y-m-d', $end_date);
                } else {
                    $end_date = date('Y-m-d', strtotime($notice['end_date']));
                }
            }

            if ($notice['end_date'] == "" && $notice['start_time'] == "" && $notice['end_time'] == "") {
                $info = array(
                    'id' => $notice['id'],
                    'title' => $notice['notice_title'],
                    'start' => date('Y-m-d', strtotime($notice['start_date']))
                );
            } else if ($notice['start_time'] != "" && ($notice['end_date'] == "" && $notice['end_time'] == "")) {
                $info = array(
                    'id' => $notice['id'],
                    'title' => $notice['notice_title'],
                    'start' => date('Y-m-d', strtotime($notice['start_date'])) . 'T' . $notice['start_time']
                );
            } else if ($notice['end_date'] != "" && ($notice['start_time'] == "" && $notice['end_time'] == "")) {
                $info = array(
                    'id' => $notice['id'],
                    'title' => $notice['notice_title'],
                    'start' => date('Y-m-d', strtotime($notice['start_date'])),
                    'end' => $end_date
                );
            } else if ($notice['end_date'] != "" && $notice['start_time'] != "" && $notice['end_time'] != "") {
                $info = array(
                    'id' => $notice['id'],
                    'title' => $notice['notice_title'],
                    'start' => date('Y-m-d', strtotime($notice['start_date'])) . 'T' . $notice['start_time'],
                    'end' => date('Y-m-d', strtotime($notice['end_date'])) . 'T' . $notice['end_time']
                );
            } else {
                $info = array(
                    'id' => $notice['id'],
                    'title' => $notice['notice_title'],
                    'start' => date('Y-m-d', strtotime($notice['start_date']))
                );
            }
            array_push($events, $info);
        }

        $events = json_encode($events);

        return view('parent.noticeboard.noticeboard', ['events' => $events]);
    }

    public function editNoticeboard($id = "")
    {
        $notice = Noticeboard::find($id);
        return view('parent.noticeboard.edit', ['notice' => $notice]);
    }

    function profile(){
        return view('parent.profile.view');
    }

    function profile_update(Request $request){
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        
        $user_info['birthday'] = strtotime($request->eDefaultDateRange);
        $user_info['gender'] = $request->gender;
        $user_info['phone'] = $request->phone;
        $user_info['address'] = $request->address;


        if(empty($request->photo)){
            $user_info['photo'] = $request->old_photo;
        }else{
            $file_name = random(10).'.png';
            $user_info['photo'] = $file_name;

            $request->photo->move(public_path('assets/uploads/user-images/'), $file_name);
        }

        $data['user_information'] = json_encode($user_info);

        User::where('id', auth()->user()->id)->update($data);
        
        return redirect(route('parent.profile'))->with('message', get_phrase('Profile info updated successfully'));
    }

    function user_language(Request $request){
        $data['language'] = $request->language;
        User::where('id', auth()->user()->id)->update($data);
        
        return redirect()->back()->with('message', 'You have successfully transleted language.');
    }

    function password($action_type = null, Request $request){



        if($action_type == 'update'){

            

            if($request->new_password != $request->confirm_password){
                return back()->with("error", "Confirm Password Doesn't match!");
            }


            if(!Hash::check($request->old_password, auth()->user()->password)){
                return back()->with("error", "Current Password Doesn't match!");
            }

            $data['password'] = Hash::make($request->new_password);
            User::where('id', auth()->user()->id)->update($data);

            return redirect(route('parent.password', 'edit'))->with('message', get_phrase('Password changed successfully'));
        }

        return view('parent.profile.password');
    }

    /**
     * Show the event list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function eventList(Request $request)
    {
        $search = $request['search'] ?? "";

        if($search != "") {

            $events = FrontendEvent::where(function ($query) use($search) {
                    $query->where('title', 'LIKE', "%{$search}%");
                })->paginate(10);

        } else {
            $events = FrontendEvent::where('school_id', auth()->user()->school_id)->paginate(10);
        }

        return view('parent.events.events', compact('events', 'search'));
    }

    public function filter()
    {
        $child = User::where('parent_id', auth()->user()->id)->where('school_id', auth()->user()->school_id)->get();
        $student_data = array();

        if (!empty($child)) {
            $child = $child->toArray();
            foreach ($child as $info) {
                $each_child = (new CommonController)->get_student_academic_info($info['id']);

                $each_child = $each_child->toArray();
                array_push($student_data, $each_child);
            }
        }

        return view('parent.feedback.filter', ['student_data' => $student_data]);
    }

    public function marks_listc(Request $request, $value = '')
    {
        $data = $request->all();
        $exam_categories = ExamCategory::where('school_id', auth()->user()->school_id)->get();
        $user_id = $data['student_id'];
        $student_details = (new CommonController)->get_student_details_by_id($user_id);

        $subjects = Subject::where(['class_id' => $student_details['class_id'], 'school_id' => auth()->user()->school_id])->get();


        return view('parent.marks.table', ['exam_categories' => $exam_categories, 'student_details' => $student_details, 'subjects' => $subjects]);
    }

    public function feedback_list(Request $request, $value = '')
    {
        $data = $request->all();
        $user_id = $data['student_id'];
        //$student_details = (new CommonController)->get_student_details_by_id($user_id);
        $feedbacks = Feedback::where(['student_id' => $user_id,'school_id'=> auth()->user()->school_id])->orderBy('created_at', 'DESC')->paginate(20);

        return view('parent.feedback.feedback_list', ['feedbacks' => $feedbacks]);
    }
}
