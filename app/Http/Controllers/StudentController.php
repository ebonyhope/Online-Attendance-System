<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rules\PasswordChange;
use App\Course;
use App\CitsStudent;
use App\Student;
use Hash;


class StudentController extends Controller
{
    public function getLogin(){
        return view('index');
    }


    public function postLogin(){
        //validate the matricNo
        $this->validate(request(), [
            'matricNo' => 'required|exists:cits-students,matricNo',     
        ]);
        
        //retrieve student with that matric No from the cits databse
        $student = CitsStudent::where('matricNo', '=', Request('matricNo'))->first();

        if(($student != null)){
            $courseIds = Course::getCourseIds(explode(',', $student->courses));
            session([
                'matricNo' => request()->input('matricNo'),
                'courses' => $courseIds,
            ]);

            $courses = Course::getCourses($courseIds);

            return view('register', compact('courses', 'student'));
        }
        else{
            session()->flash('incorrectDetails', 'Incorrect Password or Matric No');
            return redirect('student/login');
        }
    }

    public function postRegister(){

        $student = Student::where([
                ['matricNo', '=', session('matricNo')],
            ])->first();

        if ((session('matricNo')) && (session('courses')) && ($student == null)) {
            
            $courses = [];
            $sessionCourses = session('courses');

            //search for converting associative arrays to string
            foreach ($sessionCourses as $course) {
               $courses[$course] = 0;
            }

            $student = Student::create([
                        'matricNo' => session('matricNo'),
                        'courses' => json_encode($courses),
                    ]); 
            dd($student->courses);
            session()->flash('RegistrationSuccessful', 'You have successfully registered on this platform');   

            return redirect('/');
        }
        else{
            session()->flash('RegistrationError', 'This student has registered before');

            return redirect('student/register');
        }
    }

    //val1 = incoming request
    //val2 = database data
    public function validatePassword($val1, $val2)
    {

        if (Hash::check($val1, $val2)) {
            return true;
        }
        else{
            return false;
        }
    }

}
