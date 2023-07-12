<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\HelperClass\MessageCode;

use App\Models\Admin\Admin;
use App\Models\ApiExceptions;


class AdminAuthController extends Controller
{
    protected $response_code = '401';
    protected $response_data = [];
    protected $errors = [];



    /**
     * @var mCode|null
     */
    protected $mCode = null;

    public function __construct(MessageCode $mCode)
    {
        $this->mCode = $mCode;
        // $this->middleware('auth:api', ['except' => ['login', 'register','checkOtp','resetPassword']]);
    }

    /**
     * @param Request $request
     * @return boolen
     */
    protected function addApiExceptionInDb($data)
    {
        try {
            ApiExceptions::updateOrCreate(['api_name' => $data['api_name']], $data);
        } catch (Exception $ex) {
            $data = [
                'api_name' => 'App::addApiExceptionInDb',
                'errors' => $ex->getMessage(),
            ];
            ApiExceptions::updateOrCreate(['api_name' => $data['api_name']], $data);
        }
    }

    /**
     * This function is used to login admin user!
     *  
     * @param Request $request
     * @return JSON response 
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);
            if ($validator->fails()) {
                $this->errors = $validator->messages();
                $message = $this->mCode->getMessage('validation_error');
            } else {
                $admin = Admin::where('email', $request->email)->first();
                if (!$admin) {
                    $this->errors =[
                        'email' => [$this->mCode->getMessage('invalid_email')],
                    ];
                    $message = $this->mCode->getMessage('invalid_credentials');
                } else if (!Hash::check($request->password, $admin->password)) {
                    $message = $this->mCode->getMessage('invalid_credentials');
                    $this->errors =[
                        'password' => [$this->mCode->getMessage('invalid_password')],
                    ];
                } else {
                    $token = $admin->createToken($admin->email . '_Token')->plainTextToken;
                    $this->response_data = [
                        'token' => $token,
                        'user_name' => $admin->name,
                        'user' => $admin,
                    ];
                    $this->response_code = 200;
                    $message = $this->mCode->getMessage('login_success');
                }
            }
        } catch (\Exception $ex) {
            $data = [
                'api_name' => 'AdminAuthController::login',
                'errors' => $ex->getMessage(),
            ];
            $this->addApiExceptionInDb($data);
            $message = $this->mCode->getMessage('exception_error');
        }
        return response()->json([
            'response_code' => $this->response_code,
            'message' => $message,
            'errors' => $this->errors,
            'data' => $this->response_data
        ]);
    }

    /**
     * This function is used to Sinup/Register admin user!
     *  
     * @param Request $request
     * @return JSON response 
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'validation_error' => $validator->messages()
            ]);
        } else {
            $user = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $token = $user->createToken($user->email . '_Token')->plainTextToken;
            return response()->json([
                'status' => 200,
                'username' => $user->name,
                'user' => $user,
                'token' => $token,
                'message' => 'Registered Successfully.'
            ]);
        }
    }




    /**
     * This function is used to logout admin user!
     *  
     * @param Request $request
     * @return JSON response 
     */
    public function logout(Request $request)
    {
        try {
            if (auth()->user()) {
                auth()->user()->tokens()->delete();
                $this->response_data = [];
                $this->response_code = 200;
                $message = $this->mCode->getMessage('logout_success');
            } else {
                $this->response_data = [];
                $message = $this->mCode->getMessage('logout_failed');
            }
        } catch (\Exception $ex) {
            $data = [
                'api_name' => 'AdminAuthController::logout',
                'errors' => $ex->getMessage(),
            ];
            $this->addApiExceptionInDb($data);
            $message = $this->mCode->getMessage('exception_error');
        }

        return response()->json([
            'response_code' => $this->response_code,
            'message' => $message,
            'errors' => $this->errors,
            'data' => $this->response_data
        ]);
    }

    /**
     * This function is used to get Admin profile!
     *  
     * @param Request $request
     * @return JSON response 
     */
    public function profile()
    {
        try {
            if (auth()->user()) {
                $this->response_data = auth()->user();
                $this->response_code = 200;
                $message = $this->mCode->getMessage('profile_fetched');
            } else {
                $this->response_data = [];
                $message = $this->mCode->getMessage('profile_not_fount');
            }
        } catch (\Exception $ex) {
            $data = [
                'api_name' => 'AdminAuthController::profile',
                'errors' => $ex->getMessage(),
            ];
            $this->addApiExceptionInDb($data);
            $message = $this->mCode->getMessage('exception_error');
        }
        return response()->json([
            'response_code' => $this->response_code,
            'message' => $message,
            'errors' => $this->errors,
            'data' => $this->response_data
        ]);
    }


    /**
     * This function is used to Update Admin Profile!
     *  
     * @param Request $request
     * @return JSON response 
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'min:3', 'max:255'],
        ]);
        try {
            if ($validator->fails()) {
                $message = $this->mCode->getMessage('register_validation_failed');
                $this->errors = $validator->messages();
            } else {
                $user_id = ($request->id) ? $request->id : auth()->user()->id;
                if ($user_id) {
                    $updateData = [];
                    if ($request->name) {
                        $updateData['name'] = $request->name;
                    }
                    if ($request->email) {
                        $updateData['email'] = $request->email;
                    }
                    Admin::where('id', $user_id)->update($updateData);
                    /**
                     * Update profile details for teacher
                     */
                    // $matchThese = ['user_id' => $user_id];
                    // $user = UserInfo::updateOrCreate($matchThese, [
                    //     'user_id' => $user_id,
                    //     'qualification' => $request->qualification,
                    //     'organization_type' => $request->organization_type,
                    //     'experience_as_teacher' => $request->experience_as_teacher,
                    // ]);
                    $message = $this->mCode->getMessage('update_profile_info');
                    $token = str_replace('Bearer ', '', $request->header('Authorization'));
                    $admin = Admin::where(['id' => $user_id])->first();
                    $this->response_code = 200;
                    $this->response_data = $admin;
                } else {
                    $message = $this->mCode->getMessage('api_key_invalid');
                    $this->response_data = [];
                }
            }
        } catch (\Exception $ex) {
            // Log Api Exception in to DB
            $data = [
                'api_name' => 'AdminAuthController::updateProfile',
                'errors' => $ex->getMessage(),
            ];
            $this->addApiExceptionInDb($data);
            $message = $this->mCode->getMessage('exception_error');
        }
        return response()->json([
            'response_code' => $this->response_code,
            'message' => $message,
            'errors' => $this->errors,
            'data' => $this->response_data
        ]);
    }
}
