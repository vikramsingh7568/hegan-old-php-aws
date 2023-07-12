<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\File;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\HelperClass\MessageCode;
use App\HelperClass\Helper;

use App\Models\Client\User;
use App\Models\Client\Plan;
use App\Models\Client\State;
use App\Models\Client\RegisterType;
use App\Models\Client\UserSmsOtp;

use App\Models\Admin\UserCategory;
use App\Models\ApiExceptions;
use Mail;
use DB;

class ClientAuthController extends Controller
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
    // DB::enableQueryLog();
    // print_r(DB::getQueryLog());
    $this->mCode = $mCode;
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
    $validator = Validator::make($request->all(), [
      'email' => ['required', 'email', 'max:255'],
      'password' => ['required', 'string', 'min:8'],
    ]);
    if ($validator->fails()) {
      return response()->json([
        'validation_error' => $validator->messages()
      ]);
    } else {
      $user = User::where('email', $request->email)->first();
      if (!$user) {
        return response()->json([
          'status' => 401,
          'validation_error' => [
            'email' => ['The provided credentials are incorrect.'],
            'password' => ['Password is incorrect.']
          ],
          'message' => 'The provided credentials are incorrect.'
        ]);
      } else if (!Hash::check($request->password, $user->password)) {
        return response()->json([
          'status' => 401,
          'validation_error' => ['password' => ['Password is incorrect.']],
          'message' => 'The provided credentials are incorrect.'
        ]);
      } else {
        $token = $user->createToken($user->email . '_Token')->plainTextToken;
        return response()->json([
          'status' => 200,
          'username' => $user->user_name,
          'token' => $token,
          'message' => 'Logged In Successfully.',
          'user' => $user,
        ]);
      }
    }
  }

  /**
   * This function is used to send OTP for client login!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function sendOTP(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'mobile_no' => ['required', 'numeric', 'digits:10'],
      ]);
      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('send_otp_validation_error');
      } else {
        $mobile_no = trim($request->mobile_no);
        $user = User::where('mobile_no', $mobile_no)->first();
        if (!$user) {
          $this->errors['mobile_no'] = [$this->mCode->getMessage('send_otp_no_user_error')];
          $message = $this->mCode->getMessage('send_otp_no_user_error');
        } else {
          if ($user->status == 'active') {
            $login_sms_otp = mt_rand(11111, 99999);
            $sms = "Hello " . $user->first_name . "
Your Hegan.in login OTP is " . $login_sms_otp . " Your OTP will expire within 10 minutes.
Please do not share it with anyone.
Regards 
Hegan";
            $this->response_code = 200;
            Helper::sentSms($mobile_no, $sms);
            $data['mobile_no'] = $mobile_no;
            $data['sms_otp'] = $login_sms_otp;
            $userD = UserSmsOtp::updateOrCreate(['mobile_no' => $mobile_no], $data);
            // User::where('mobile_no', $mobile_no)->update(['login_otp' => $login_otp]);
            $this->response_data = [
              // 'login_otp' => $login_sms_otp,
              'mobile_no' => $request->mobile_no
            ];

            $message = $this->mCode->getMessage('otp_send_success');
          } else {
            $this->errors['mobile_no'] = [$this->mCode->getMessage('send_otp_user_in_active')];
            $message = $this->mCode->getMessage('send_otp_user_in_active');
          }
        }
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ClientAuthController::sendOtp',
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

  public function html_email()
  {
    $data = array('name' => "Virat Gandhi");
    Mail::send('mail', $data, function ($message) {
      $message->to('abc@gmail.com', 'Tutorials Point')->subject('Laravel HTML Testing Mail');
      $message->from('xyz@gmail.com', 'Virat Gandhi');
    });
    echo "HTML Email Sent. Check your inbox.";
  }
  /**
   * This function is used to send OTP for client login!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function verifyOTP(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'mobile_no' => ['required', 'numeric', 'digits:10'],
        'login_otp' => ['required', 'numeric', 'digits:5'],
      ]);
      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('check_otp_validation_error');
      } else {

        $sms_otp_detail = UserSmsOtp::where(['mobile_no' => $request->mobile_no, 'sms_otp' => $request->login_otp])->first();
        if (@$sms_otp_detail) {
          $minut_diff = round(abs(time() -  strtotime($sms_otp_detail->updated_at)) / 60);
          //Otp valid for 10 mnt only
          if (@$minut_diff < 10) {
            $user = User::where(['mobile_no' => $request->mobile_no, 'status' => 'active'])->first();
            if ($user) {
              $token = $user->createToken($user->mobile_no . '_Token')->plainTextToken;
              $this->response_data = [
                'register_as' => $user->register_as,
                'mobile_no' => $request->mobile_no,
                'token' => $token,
                'user_name' => $user->user_name,
                'first_name' => $user->first_name,
                'email' => $user->email,
              ];
              $this->response_code = 200;
              $message = $this->mCode->getMessage('user_login_success');
            } else {
              $this->errors['login_otp'] = [$this->mCode->getMessage('send_otp_user_in_active')];
              $message = $this->mCode->getMessage('send_otp_user_in_active');
            }
          } else {
            $this->errors['login_otp'] = [$this->mCode->getMessage('otp_expire')];
            $this->response_data['minut_diff'] = $minut_diff;
            $this->response_data['time'] = date('Y-m-d h:i:s');
            $this->response_data['updated_at'] = $sms_otp_detail->updated_at;
            $message = $this->mCode->getMessage('otp_expire');
          }
        } else {
          $this->errors['login_otp'] = [$this->mCode->getMessage('invalid_otp')];
          $message = $this->mCode->getMessage('invalid_otp');
        }
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ClientAuthController::verifyOTP',
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
  public function getStates(Request $request)
  {
    try {

      $this->response_data = State::where('status', 'active')->orderBy('name', 'ASC')->get();
      $this->response_code = 200;
      $message = $this->mCode->getMessage('get_state_success');
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ClientAuthController::getStates',
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
  public function getPlans(Request $request)
  {
    try {

      $this->response_data = Plan::where('status', 'active')->get();
      $this->response_code = 200;
      $message = $this->mCode->getMessage('get_plan_success');
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ClientAuthController::getPlans',
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
  public function getRegisterType(Request $request)
  {
    try {

      $this->response_data = RegisterType::select([
        'id', 'user_type', 'code'
      ])->where('status', 'active')->get();
      $this->response_code = 200;
      $message = $this->mCode->getMessage('get_register_type_success');
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ClientAuthController::getRegisterType',
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

  public function uploadImage($file, $path)
  {
    ini_set('memory_limit', '3000M');
    ini_set('max_execution_time', '0');
    $fileName = $file->getClientOriginalName();
    $fileExt  = $file->getClientOriginalExtension();
    $path = $path . '/' . date('Y') . '/' . date('m');
    $destinationFolder = public_path('uploads/' . $path . '/');
    $num   = 1;
    $newName = time() . '-' . $fileName;
    // $appendNum = false;
    // while (file_exists($destinationFolder . $newName)) {
    //   $appendNum = true;
    //   $num++;
    // }
    // if ($appendNum) {
    //   $newName = $fileName . '_' . $num;
    // }
    $file->move($destinationFolder, $newName);
    return $path . '/' . $newName;
  }

  /**
   * This function is used to generate unique company Code!
   *  
   * @param Request $request
   * @return JSON response 
   */
  protected function generateCompanyCode($register_as = 'M', $state_id = 1, $user_id = 0)
  {
    $state_code = '';
    $state = State::select('state_code')->where('id', $state_id)->first();
    if ($state) {
      $state_code = $state->state_code;
    } else {
      $state_code = $state_id;
    }
    if ($user_id) {
      $lastRow = User::where('register_as', $register_as)->where('id', '!=', $user_id)->get()->count();
    } else {
      $lastRow = User::where('register_as', $register_as)->get()->count();
    }

    if ($lastRow) {
      $code_sequence = ($lastRow + 1);
    } else {
      $code_sequence = 1;
    }
    // returns 04
    $code_sequence_padded = sprintf("%04d", $code_sequence);
    return $state_code . '-' . $register_as . '-' . $code_sequence_padded;
  }

  /**
   * This function is used to Sinup/Register admin user!
   *  
   * @param Request $request
   * @return JSON response 
   */

  public function register(Request $request)
  {
    ini_set('memory_limit', '3000M');
    ini_set('max_execution_time', '0');
    // header("Access-Control-Allow-Origin: *");
    try {
      if ($request->register_as == 'M' || $request->register_as == 'D' || $request->register_as == 'W') {
        $validator = Validator::make($request->all(), [
          'first_name' => ['required', 'max:255'],
          'last_name' => ['required', 'max:255'],
          'firm_name' => ['required', 'max:255'], //, 'unique:users,firm_name'
          'email' => ['required', 'email', 'max:255', 'unique:users,email'],
          'mobile_no' => ['required', 'numeric', 'digits:10', 'unique:users,mobile_no'],
          'address' => ['required', 'max:255'],
          'state_id' => ['required', 'numeric'],
          'pin_code' => ['required', 'numeric', 'digits:6'],
          'user_categories' => ['required'],
          'register_as' => ['required'],
          'account_name' => ['required', 'max:255'],
          'account_no' => ['required', 'numeric', 'min:10'],
          'ifsc_code' => ['required', 'max:255'],
          'branch' => ['required', 'max:255'],
          'signature' => ['required', File::types(['jpg', 'png'])
            ->min(1)
            ->max(1024)],
        ]);
      } else {
        $validator = Validator::make($request->all(), [
          'first_name' => ['required', 'max:255'],
          'last_name' => ['required', 'max:255'],
          'firm_name' => ['required', 'max:255'], //, 'unique:users,firm_name'
          'email' => ['required', 'email', 'max:255', 'unique:users,email'],
          'mobile_no' => ['required', 'numeric', 'digits:10', 'unique:users,mobile_no'],
          'address' => ['required', 'max:255'],
          'state_id' => ['required', 'numeric'],
          'pin_code' => ['required', 'numeric', 'digits:6'],
          'user_categories' => ['required'],
          'register_as' => ['required'],
        ]);
      }



      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('register_validation_error');
      } else {
        $user_categories = explode(",", $request->user_categories);
        if (count($user_categories) == 1) {
          if (@$user_categories[0]  == 5) {
          } else if (@$user_categories[0]  == 4) {
            if (empty($request->fssai_no)) {
              $this->errors = [
                'fssai_no' => ['FSSAI No is required!']
              ];
              $message = $this->mCode->getMessage('register_validation_error');
            }
          } else {
            if (empty($request->dl_no_1) && empty($request->dl_no_2)) {
              $this->errors = [
                'dl_no_1' => ['dl_no_1 is required!'],
              ];
              $message = $this->mCode->getMessage('register_validation_error');
            } else {
              if (empty($request->dl_no_1) && empty($request->dl_no_2) && empty($request->fssai_no)) {
                $this->errors = [
                  'dl_no_1' => ['dl_no_1 is required!'],
                  'fssai_no' => ['FSSAI No is required!']
                ];
                $message = $this->mCode->getMessage('register_validation_error');
              } else if (empty($request->dl_no_1) && empty($request->dl_no_2)) {
                $this->errors = [
                  'dl_no_1' => ['dl_no_1 is required!'],
                ];
                $message = $this->mCode->getMessage('register_validation_error');
              } else if (empty($request->fssai_no)) {
                $this->errors = [
                  'fssai_no' => ['FSSAI No is required!']
                ];
                $message = $this->mCode->getMessage('register_validation_error');
              }
            }
          }
        } else if (count($user_categories) > 1) {
          if (empty($request->dl_no_1) && empty($request->dl_no_2) && empty($request->fssai_no)) {
            $this->errors = [
              'dl_no_1' => ['dl_no_1 is required!'],
              'fssai_no' => ['FSSAI No is required!']
            ];
            $message = $this->mCode->getMessage('register_validation_error');
          } else if (empty($request->dl_no_1) && empty($request->dl_no_2)) {
            $this->errors = [
              'dl_no_1' => ['dl_no_1 is required!'],
            ];
            $message = $this->mCode->getMessage('register_validation_error');
          } else if (empty($request->fssai_no)) {
            $this->errors = [
              'fssai_no' => ['FSSAI No is required!']
            ];
            $message = $this->mCode->getMessage('register_validation_error');
          }
        }
        if (!$this->errors) {
          if ($request->hasFile('signature')) {
            $signature_file = $this->uploadImage($request->signature, 'signatures');
          } else {
            $signature_file  = '';
          }
          $company_code = $this->generateCompanyCode($request->register_as, $request->state_id);
          $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'firm_name' => $request->firm_name,
            'user_name' => $company_code,
            'mobile_no' => $request->mobile_no,
            'register_as' => $request->register_as,
            'plan_id' => $request->plan_id,
            'address' => $request->address,
            'pin_code' => $request->pin_code,
            'dl_no_1' => $request->dl_no_1,
            'dl_no_2' => $request->dl_no_2,
            'gst_no' => $request->gst_no,
            'fssai_no' => $request->fssai_no,
            'account_name' => $request->account_name,
            'account_no' => $request->account_no,
            'ifsc_code' => $request->ifsc_code,
            'branch' => $request->branch,
            'state_id' => $request->state_id,
            'password' => Hash::make($request->mobile_no),
            'signature' => $signature_file,
            'status' => 'pending'
          ]);

          if (@$user) {
            $user_categories = explode(",", $request->user_categories);
            if (is_array($user_categories) && count($user_categories)) {
              foreach ($user_categories as $cat_id) {
                if ($cat_id) {
                  $catData = [
                    'user_id' => $user->id,
                    'medicine_category_id' => $cat_id,
                    'commission' => '0'
                  ];
                  UserCategory::create($catData);
                }
              }
            }
            // Email Notification  
            if (config('app.env') != 'local') {
              $sub = "Welcome Email";
              $emailData = [
                'name' => $user->first_name
              ];
              $mailto = $user->email;
              $email_date =  Mail::send(['html' => 'emails/client_register_email'], $emailData, function ($message) use ($mailto, $sub) {
                $message->to($mailto, 'Hegan B2B Pharma Marketplace')->subject($sub);
                $message->from('no-reply@hegan.in', 'Hegan B2B Pharma Marketplace');
              });
            }
          }
          $this->response_code = 200;
          $message = $this->mCode->getMessage('register_success');
          $this->response_data = $user;
        }
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ClientAuthController::register',
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
      $user_id = auth()->user()->id;
      if ($user_id) {
        $this->response_data = User::select([
          'users.*',
          'plans.plan_name',
          'states.name as state_name'
        ])
          ->leftJoin('plans', 'plans.id', '=', 'users.plan_id')
          ->leftJoin('states', 'states.id', '=', 'users.state_id')
          ->where('users.id', $user_id)->first();
        if ($this->response_data) {
          $this->response_code = 200;
          $userCategories =  UserCategory::select([
            'user_categories.commission',
            'mc.*'
          ])
            ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'user_categories.medicine_category_id')
            ->where('user_categories.user_id', $user_id)->get();
          $this->response_data['userCategories'] = $userCategories;

          // $userCategories =  UserCategory::select([
          //   'user_categories.medicine_category_id'
          // ])
          //   ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'user_categories.medicine_category_id')
          //   ->where('user_categories.user_id', $user_id)->get();

          // $user_categories = [];
          // foreach ($userCategories as $cat) {
          //   $user_categories[] = $cat->medicine_category_id;
          // }

          // {/* {values.user_categories} */
          // }
          // $this->response_data['user_categories'] = [implode(',', $user_categories)];
          // $this->response_data['user_categories'] = [3,2];

          $message = $this->mCode->getMessage('profile_fetched');
        } else {
          $message = $this->mCode->getMessage('profile_not_fount');
          $this->response_code = 401;
          $this->response_code = [];
        }
      } else {
        $message = $this->mCode->getMessage('profile_not_fount');
        $this->response_code = 401;
        $this->response_code = [];
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ClientAuthController::profile',
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
   * This function is used to get Unapprove (Pending Users) list!
   *  
   * @param Request $request
   * @return JSON response 
   */

  public function getClientProfileInfo(Request $request)
  {
    try {
      $user_id = auth()->user()->id;
      if ($user_id) {
        $this->response_data = User::select([
          'users.*',
          'plans.plan_name',
          'states.name as state_name'
        ])
          ->leftJoin('plans', 'plans.id', '=', 'users.plan_id')
          ->leftJoin('states', 'states.id', '=', 'users.state_id')
          ->where('users.id', $user_id)->first();
        if ($this->response_data) {
          $this->response_code = 200;
          $userCategories =  UserCategory::select([
            'user_categories.*', 'mc.name as medicine_category',
            'mc.m_commission as m_default_commission',
            'mc.d_commission as d_default_commission',
            'mc.w_commission as w_default_commission'
          ])
            ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'user_categories.medicine_category_id')
            ->where('user_categories.user_id', $user_id)->get();
          $this->response_data['userCategories'] = $userCategories;

          $select_cat_ids = [];
          foreach ($userCategories as $uCat) {
            $select_cat_ids[] = $uCat->medicine_category_id;
          }
          $this->response_data['user_categories'] =  json_decode(json_encode($select_cat_ids, JSON_NUMERIC_CHECK));
          $message = $this->mCode->getMessage('user_fetch_success');
        } else {
          $message = $this->mCode->getMessage('un_authorized_access');
          $this->response_code = 401;
          $this->response_code = [];
        }
      } else {
        $message = $this->mCode->getMessage('un_authorized_access');
        $this->response_code = 401;
        $this->response_code = [];
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'ClientAuthController::getUserInfo',
        'errors' => $e->getMessage(),
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
  public function updateProfile_old(Request $request)
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

  /**
   * This function is used to get Unapprove (Pending Users) list!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function updateProfile(Request $request)
  {

    try {
      $user_id = auth()->user()->id;;
      if ($request->register_as == 'M' || $request->register_as == 'D' || $request->register_as == 'W') {
        $validator = Validator::make($request->all(), [
          'first_name' => ['required', 'max:255'],
          'last_name' => ['required', 'max:255'],
          'firm_name' => ['required', 'max:255'], //, 'unique:users,firm_name'
          'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user_id],
          'mobile_no' => ['required', 'numeric', 'digits:10', 'unique:users,mobile_no,' . $user_id],
          'address' => ['required', 'max:255'],
          'state_id' => ['required', 'numeric'],
          'pin_code' => ['required', 'numeric', 'digits:6'],
          'user_categories' => ['required'],
          'register_as' => ['required'],
          'account_name' => ['required', 'max:255'],
          'account_no' => ['required', 'numeric', 'min:10'],
          'ifsc_code' => ['required', 'max:255'],
          'branch' => ['required', 'max:255'],
          'update_signature' => ['nullable', File::types(['jpg', 'png'])
            ->min(1)
            ->max(1024)],
        ]);
      } else {
        $validator = Validator::make($request->all(), [
          'first_name' => ['required', 'max:255'],
          'last_name' => ['required', 'max:255'],
          'firm_name' => ['required', 'max:255'], //, 'unique:users,firm_name'
          'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user_id],
          'mobile_no' => ['required', 'numeric', 'digits:10', 'unique:users,mobile_no,' . $user_id],
          'address' => ['required', 'max:255'],
          'state_id' => ['required', 'numeric'],
          'pin_code' => ['required', 'numeric', 'digits:6'],
          'user_categories' => ['required'],
          'register_as' => ['required'],
        ]);
      }

      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('register_validation_error');
      } else {
        if (@$request->id) {
          $user_categories = explode(",", $request->user_categories);
          if (count($user_categories) == 1) {
            if (@$user_categories[0]  == 5) {
            } else if (@$user_categories[0]  == 4) {
              if (empty($request->fssai_no)) {
                $this->errors = [
                  'fssai_no' => ['FSSAI No is required!']
                ];
                $message = $this->mCode->getMessage('register_validation_error');
              }
            } else {
              if (empty($request->dl_no_1) && empty($request->dl_no_2)) {
                $this->errors = [
                  'dl_no_1' => ['dl_no_1 is required!'],
                ];
                $message = $this->mCode->getMessage('register_validation_error');
              } else {
                if (empty($request->dl_no_1) && empty($request->dl_no_2) && empty($request->fssai_no)) {
                  $this->errors = [
                    'dl_no_1' => ['dl_no_1 is required!'],
                    'fssai_no' => ['FSSAI No is required!']
                  ];
                  $message = $this->mCode->getMessage('register_validation_error');
                } else if (empty($request->dl_no_1) && empty($request->dl_no_2)) {
                  $this->errors = [
                    'dl_no_1' => ['dl_no_1 is required!'],
                  ];
                  $message = $this->mCode->getMessage('register_validation_error');
                } else if (empty($request->fssai_no)) {
                  $this->errors = [
                    'fssai_no' => ['FSSAI No is required!']
                  ];
                  $message = $this->mCode->getMessage('register_validation_error');
                }
              }
            }
          } else if (count($user_categories) > 1) {
            if (empty($request->dl_no_1) && empty($request->dl_no_2) && empty($request->fssai_no)) {
              $this->errors = [
                'dl_no_1' => ['dl_no_1 is required!'],
                'fssai_no' => ['FSSAI No is required!']
              ];
              $message = $this->mCode->getMessage('register_validation_error');
            } else if (empty($request->dl_no_1) && empty($request->dl_no_2)) {
              $this->errors = [
                'dl_no_1' => ['dl_no_1 is required!'],
              ];
              $message = $this->mCode->getMessage('register_validation_error');
            } else if (empty($request->fssai_no)) {
              $this->errors = [
                'fssai_no' => ['FSSAI No is required!']
              ];
              $message = $this->mCode->getMessage('register_validation_error');
            }
          }
          if (!$this->errors) {
            $company_code = $this->generateCompanyCode($request->register_as, $request->state_id, $user_id);
            $updatedData = [
              'first_name' => $request->first_name,
              'last_name' => $request->last_name,
              // 'email' => $request->email,
              'firm_name' => $request->firm_name,
              'user_name' => $company_code,
              // 'mobile_no' => $request->mobile_no,
              'register_as' => $request->register_as,
              'plan_id' => $request->plan_id,
              'address' => $request->address,
              'pin_code' => $request->pin_code,
              'dl_no_1' => $request->dl_no_1,
              'dl_no_2' => $request->dl_no_2,
              'gst_no' => $request->gst_no,
              'fssai_no' => $request->fssai_no,
              'account_name' => $request->account_name,
              'account_no' => $request->account_no,
              'ifsc_code' => $request->ifsc_code,
              'branch' => $request->branch,
              'state_id' => $request->state_id,
              'status' => ($request->status) ? $request->status : 'pending'
            ];
            if ($request->hasFile('update_signature')) {
              $signature_file = $this->uploadImage($request->update_signature, 'signatures');
              $updatedData['signature'] = $signature_file;
            }
            $user = User::where('id', $user_id)->update($updatedData);
            if (@$user) {
              $user_categories = explode(",", $request->user_categories);
              if (is_array($user_categories) && count($user_categories)) {
                UserCategory::where('user_id', $user_id)->delete();
                foreach ($user_categories as $cat_id) {
                  if ($cat_id) {
                    $matchThese = ['user_id' => $user_id, 'medicine_category_id' => $cat_id];
                    UserCategory::updateOrCreate($matchThese, [
                      'user_id' => $user_id,
                      'medicine_category_id' => $cat_id,
                      'commission' => '0'
                    ]);
                  }
                }
              }
              // Email Notification  
              // if (config('app.env') != 'local') {
              //   $sub = "Welcome Email";
              //   $emailData = [
              //     'name' => $user->first_name
              //   ];
              //   $mailto = $user->email;
              //   $email_date =  Mail::send(['html' => 'emails/client_register_email'], $emailData, function ($message) use ($mailto, $sub) {
              //     $message->to($mailto, 'Hegan B2B Pharma Marketplace')->subject($sub);
              //     $message->from('no-reply@hegan.in', 'Hegan B2B Pharma Marketplace');
              //   });
              // }
            }
            $user = User::where('id', $user_id)->first();
            $this->response_code = 200;
            $message = $this->mCode->getMessage('user_update_success');
            $this->response_data = $user;
          }
        }
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ClientAuthController::updateProfile',
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
   * This function is used to get Unapprove (Pending Users) list!
   *  
   * @param Request $request
   * @return JSON response 
   */

  public function getPendingUsersList(Request $request)
  {
    try {
      $pendingUsers = User::select([
        'users.*',
        'plans.plan_name',
        'states.name as state_name'
      ])
        ->leftJoin('plans', 'plans.id', '=', 'users.plan_id')
        ->leftJoin('states', 'states.id', '=', 'users.state_id')
        ->where('users.status', 'pending')
        ->orderBy('users.id', 'DESC')
        ->get();
      $this->response_data = $pendingUsers;
      if ($this->response_data) {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('pending_fetch_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('pending_fetch_success_no_data');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'ClientAuthController::getPendingUsersList',
        'errors' => $e->getMessage(),
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
   * This function is used to get Unapprove (Pending Users) list!
   *  
   * @param Request $request
   * @return JSON response 
   */

  public function getRejectedUsersList(Request $request)
  {
    try {
      $rejectedUsers = User::select([
        'users.*',
        'plans.plan_name',
        'states.name as state_name'
      ])
        ->leftJoin('plans', 'plans.id', '=', 'users.plan_id')
        ->leftJoin('states', 'states.id', '=', 'users.state_id')
        ->where('users.status', 'rejected')
        ->orderBy('users.id', 'DESC')
        ->get();
      $this->response_data = $rejectedUsers;
      if ($this->response_data) {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('rejected_fetch_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('rejected_fetch_success_no_data');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'ClientAuthController::getRejectedUsersList',
        'errors' => $e->getMessage(),
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
   * This function is used to get Unapprove (Pending Users) list!
   *  
   * @param Request $request
   * @return JSON response 
   */

  public function getApproveUsersList(Request $request)
  {
    try {
      $approvedUsers = User::select([
        'users.*',
        'plans.plan_name',
        'states.name as state_name'
      ])
        ->leftJoin('plans', 'plans.id', '=', 'users.plan_id')
        ->leftJoin('states', 'states.id', '=', 'users.state_id')
        ->where('users.status', 'active')
        ->orderBy('users.id', 'DESC')
        ->get();
      $this->response_data = $approvedUsers;
      if ($this->response_data) {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('all_users_fetch_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('all_users_fetch_success_no_data');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'ClientAuthController::getAllUsersList',
        'errors' => $e->getMessage(),
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
   * This function is used to get Unapprove (Pending Users) list!
   *  
   * @param Request $request
   * @return JSON response 
   */

  public function getUserInfo(Request $request)
  {
    try {
      $user_id = ($request->user_id) ? $request->user_id : auth()->user()->id;
      if ($user_id) {
        $this->response_data = User::select([
          'users.*',
          'plans.plan_name',
          'states.name as state_name'
        ])
          ->leftJoin('plans', 'plans.id', '=', 'users.plan_id')
          ->leftJoin('states', 'states.id', '=', 'users.state_id')
          ->where('users.id', $user_id)->first();
        if ($this->response_data) {
          $this->response_code = 200;
          $userCategories =  UserCategory::select([
            'user_categories.*', 'mc.name as medicine_category',
            'mc.m_commission as m_default_commission',
            'mc.d_commission as d_default_commission',
            'mc.w_commission as w_default_commission'
          ])
            ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'user_categories.medicine_category_id')
            ->where('user_categories.user_id', $user_id)->get();
          $this->response_data['userCategories'] = $userCategories;

          $select_cat_ids = [];
          foreach ($userCategories as $uCat) {
            $select_cat_ids[] = $uCat->medicine_category_id;
          }
          $this->response_data['user_categories'] =  $select_cat_ids;
          $message = $this->mCode->getMessage('user_fetch_success');
        } else {
          $message = $this->mCode->getMessage('un_authorized_access');
          $this->response_code = 401;
          $this->response_code = [];
        }
      } else {
        $message = $this->mCode->getMessage('un_authorized_access');
        $this->response_code = 401;
        $this->response_code = [];
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'ClientAuthController::getUserInfo',
        'errors' => $e->getMessage(),
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
   * This function is used to get Unapprove (Pending Users) list!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function saveUpdatedInfo(Request $request)
  {

    try {
      $user_id = $request->id;
      if ($request->register_as == 'M' || $request->register_as == 'D' || $request->register_as == 'W') {
        $validator = Validator::make($request->all(), [
          'first_name' => ['required', 'max:255'],
          'last_name' => ['required', 'max:255'],
          'firm_name' => ['required', 'max:255'], //, 'unique:users,firm_name'
          'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user_id],
          'mobile_no' => ['required', 'numeric', 'digits:10', 'unique:users,mobile_no,' . $user_id],
          'address' => ['required', 'max:255'],
          'state_id' => ['required', 'numeric'],
          'pin_code' => ['required', 'numeric', 'digits:6'],
          'user_categories' => ['required'],
          'register_as' => ['required'],
          'account_name' => ['required', 'max:255'],
          'account_no' => ['required', 'numeric', 'min:10'],
          'ifsc_code' => ['required', 'max:255'],
          'branch' => ['required', 'max:255'],
          'update_signature' => ['nullable', File::types(['jpg', 'png'])
            ->min(1)
            ->max(1024)],
        ]);
      } else {
        $validator = Validator::make($request->all(), [
          'first_name' => ['required', 'max:255'],
          'last_name' => ['required', 'max:255'],
          'firm_name' => ['required', 'max:255'], //, 'unique:users,firm_name'
          'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user_id],
          'mobile_no' => ['required', 'numeric', 'digits:10', 'unique:users,mobile_no,' . $user_id],
          'address' => ['required', 'max:255'],
          'state_id' => ['required', 'numeric'],
          'pin_code' => ['required', 'numeric', 'digits:6'],
          'user_categories' => ['required'],
          'register_as' => ['required'],
        ]);
      }



      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('register_validation_error');
      } else {
        if (@$request->id) {
          $user_categories = explode(",", $request->user_categories);
          if (count($user_categories) == 1) {
            if (@$user_categories[0]  == 5) {
            } else if (@$user_categories[0]  == 4) {
              if (empty($request->fssai_no)) {
                $this->errors = [
                  'fssai_no' => ['FSSAI No is required!']
                ];
                $message = $this->mCode->getMessage('register_validation_error');
              }
            } else {
              if (empty($request->dl_no_1) && empty($request->dl_no_2)) {
                $this->errors = [
                  'dl_no_1' => ['dl_no_1 is required!'],
                ];
                $message = $this->mCode->getMessage('register_validation_error');
              } else {
                if (empty($request->dl_no_1) && empty($request->dl_no_2) && empty($request->fssai_no)) {
                  $this->errors = [
                    'dl_no_1' => ['dl_no_1 is required!'],
                    'fssai_no' => ['FSSAI No is required!']
                  ];
                  $message = $this->mCode->getMessage('register_validation_error');
                } else if (empty($request->dl_no_1) && empty($request->dl_no_2)) {
                  $this->errors = [
                    'dl_no_1' => ['dl_no_1 is required!'],
                  ];
                  $message = $this->mCode->getMessage('register_validation_error');
                } else if (empty($request->fssai_no)) {
                  $this->errors = [
                    'fssai_no' => ['FSSAI No is required!']
                  ];
                  $message = $this->mCode->getMessage('register_validation_error');
                }
              }
            }
          } else if (count($user_categories) > 1) {
            if (empty($request->dl_no_1) && empty($request->dl_no_2) && empty($request->fssai_no)) {
              $this->errors = [
                'dl_no_1' => ['dl_no_1 is required!'],
                'fssai_no' => ['FSSAI No is required!']
              ];
              $message = $this->mCode->getMessage('register_validation_error');
            } else if (empty($request->dl_no_1) && empty($request->dl_no_2)) {
              $this->errors = [
                'dl_no_1' => ['dl_no_1 is required!'],
              ];
              $message = $this->mCode->getMessage('register_validation_error');
            } else if (empty($request->fssai_no)) {
              $this->errors = [
                'fssai_no' => ['FSSAI No is required!']
              ];
              $message = $this->mCode->getMessage('register_validation_error');
            }
          }
          if (!$this->errors) {
            $company_code = $this->generateCompanyCode($request->register_as, $request->state_id, $user_id);
            $updatedData = [
              'first_name' => $request->first_name,
              'last_name' => $request->last_name,
              'email' => $request->email,
              'mobile_no' => $request->mobile_no,
              'firm_name' => $request->firm_name,
              'user_name' => $company_code,
              'register_as' => $request->register_as,
              'plan_id' => $request->plan_id,
              'address' => $request->address,
              'pin_code' => $request->pin_code,
              'dl_no_1' => $request->dl_no_1,
              'dl_no_2' => $request->dl_no_2,
              'gst_no' => $request->gst_no,
              'fssai_no' => $request->fssai_no,
              'account_name' => $request->account_name,
              'account_no' => $request->account_no,
              'ifsc_code' => $request->ifsc_code,
              'branch' => $request->branch,
              'state_id' => $request->state_id,
              'status' => ($request->status) ? $request->status : 'pending'
            ];
            if ($request->hasFile('update_signature')) {
              $signature_file = $this->uploadImage($request->update_signature, 'signatures');
              $updatedData['signature'] = $signature_file;
            }

            $user = User::where('id', $user_id)->update($updatedData);

            if (@$user) {
              $user_categories = explode(",", $request->user_categories);
              if (is_array($user_categories) && count($user_categories)) {
                UserCategory::where('user_id', $user_id)->delete();
                foreach ($user_categories as $cat_id) {
                  if ($cat_id) {
                    $matchThese = ['user_id' => $user_id, 'medicine_category_id' => $cat_id];
                    UserCategory::updateOrCreate($matchThese, [
                      'user_id' => $user_id,
                      'medicine_category_id' => $cat_id,
                      'commission' => '0'
                    ]);
                  }
                }
              }
              // Email Notification  
              // if (config('app.env') != 'local') {
              //   $sub = "Welcome Email";
              //   $emailData = [
              //     'name' => $user->first_name
              //   ];
              //   $mailto = $user->email;
              //   $email_date =  Mail::send(['html' => 'emails/client_register_email'], $emailData, function ($message) use ($mailto, $sub) {
              //     $message->to($mailto, 'Hegan B2B Pharma Marketplace')->subject($sub);
              //     $message->from('no-reply@hegan.in', 'Hegan B2B Pharma Marketplace');
              //   });
              // }
            }
            $user = User::where('id', $user_id)->first();
            $this->response_code = 200;
            $message = $this->mCode->getMessage('user_update_success');
            $this->response_data = $user;
          }
        }
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ClientAuthController::saveUpdatedInfo',
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
   * This function is used to get Unapprove (Pending Users) list!
   *  
   * @param Request $request
   * @return JSON response 
   */

  public function userUpdateStatusCommission(Request $request)
  {
    try {
      $user_id = ($request->user_id) ? $request->user_id : 0;
      if ($user_id) {
        $user_pre = User::where('id', $user_id)->first();
        User::where('id', $user_id)->update([
          'status' => $request->status,
        ]);
        // Status change Emmail Notifucation
        if ($user_pre->state != $request->status) {
          // Email Notification  
          if (config('app.env') != 'local') {
            $emailData = [
              'name' => $user_pre->first_name
            ];
            $mailto = $user_pre->email;
            if ($request->status == 'active') {
              $sub = "Activation Completed";
              $email_template = 'emails/client_approved_email';
            } else {
              $sub = "Profile Rejected";
              $email_template = 'emails/client_rejected_email';
            }
            Mail::send(['html' => $email_template], $emailData, function ($message) use ($mailto, $sub) {
              $message->to($mailto, 'Hegan B2B Pharma Marketplace')->subject($sub);
              $message->from('no-reply@hegan.in', 'Hegan B2B Pharma Marketplace');
            });
          }
        }
        foreach ($request->userCategories as $row) {
          UserCategory::where('id', $row['id'])->update([
            'commission' => $row['commission'],
          ]);
        }
        $this->response_data = User::where('id', $user_id)->first();
        if ($this->response_data) {
          $this->response_code = 200;
          $userCategories =  UserCategory::select([
            'user_categories.*', 'mc.name as medicine_category',
            'mc.m_commission as m_default_commission',
            'mc.d_commission as d_default_commission',
            'mc.w_commission as w_default_commission',
          ])
            ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'user_categories.medicine_category_id')
            ->where('user_categories.user_id', $user_id)->get();
          $this->response_data['userCategories'] = $userCategories;
          $message = $this->mCode->getMessage('user_update_success');
        } else {
          $message = $this->mCode->getMessage('un_authorized_access');
          $this->response_code = 401;
          $this->response_code = [];
        }
      } else {
        $message = $this->mCode->getMessage('un_authorized_access');
        $this->response_code = 401;
        $this->response_code = [];
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'ClientAuthController::userUpdateStatusCommission',
        'errors' => $e->getMessage(),
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
