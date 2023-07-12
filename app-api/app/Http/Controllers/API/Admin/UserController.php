<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use App\Models\User;

class UserController extends Controller
{
    protected $status_code = 200;
    protected $response_data = [];

    public function getAdminUsersList(Request $request)
    {
        $users = User::where(['user_type' => '1'])->get();
        $this->response_data = $users;
        return response()->json([
            'status' => $this->status_code,
            'data' => $this->response_data,
            'message' => 'Admin Users feached successfully.'
        ]);
    }

    public function getTeachersList(Request $request)
    {
        $users = User::where(['user_type' => '2'])->get();
        $this->response_data = $users;
        return response()->json([
            'status' => $this->status_code,
            'data' => $this->response_data,
            'message' => 'Teachers feached successfully.'
        ]);
    }
    public function getStudentsList(Request $request)
    {
        $users = User::where(['user_type' => '3'])->get();
        $this->response_data = $users;
        return response()->json([
            'status' => $this->status_code,
            'data' => $this->response_data,
            'message' => 'Students feached successfully.'
        ]);
    }


    public function getUsers(Request $request)
    {
        $users = User::all();
        $this->response_data = $users;
        return response()->json([
            'status' => $this->status_code,
            'data' => $this->response_data,
            'message' => 'User feached successfully.'
        ]);
    }

    public function addUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_name' => ['required', 'max:256'],
            'status' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'validation_error' => $validator->messages()
            ]);
        } else {
            try {
                $userType = User::create([
                    'course_name' => $request->course_name,
                    'status' => $request->status,
                ]);
                return response()->json([
                    'status' => $this->status_code,
                    'data' => $this->response_data,
                    'message' => 'User Created Successfully.'
                ]);
            } catch (\Exception $e) {
                $this->status_code = 412;
                return response()->json([
                    'status' => $this->status_code,
                    'data' => $this->response_data,
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    public function deleteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'row_id' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'validation_error' => $validator->messages()
            ]);
        } else {
            try {
                User::where('id', $request->row_id)->delete();
                return response()->json([
                    'status' => $this->status_code,
                    'data' => $this->response_data,
                    'message' => 'User Successfully Deleted!'
                ]);
            } catch (\Exception $e) {
                $this->status_code = 412;
                return response()->json([
                    'status' => $this->status_code,
                    'data' => $this->response_data,
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
    public function getUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'row_id' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'validation_error' => $validator->messages()
            ]);
        } else {
            try {
                $this->response_data = User::where('id', $request->row_id)->first();
                if ($this->response_data) {
                    return response()->json([
                        'status' => $this->status_code,
                        'data' => $this->response_data,
                        'message' => 'User detail fetched successfully!'
                    ]);
                } else {
                    return response()->json([
                        'status' => 401,
                        'data' => $this->response_data,
                        'message' => 'You are trying to fetching undefined data!'
                    ]);
                }
            } catch (\Exception $e) {
                $this->status_code = 412;
                return response()->json([
                    'status' => $this->status_code,
                    'data' => $this->response_data,
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'course_name' => ['required', 'max:256'],
            'status' => ['required'],
            'row_id' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'validation_error' => $validator->messages()
            ]);
        } else {
            try {
                User::where('id', $request->row_id)->update([
                    'course_name' => $request->course_name,
                    'status' => $request->status,
                ]);

                return response()->json([
                    'status' => $this->status_code,
                    'data' => $this->response_data,
                    'message' => 'User updated successfully!'
                ]);
            } catch (\Exception $e) {
                $this->status_code = 412;
                return response()->json([
                    'status' => $this->status_code,
                    'data' => $this->response_data,
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}
