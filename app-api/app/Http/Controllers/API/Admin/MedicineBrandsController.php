<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\HelperClass\MessageCode;

use App\Models\Admin\MedicineBrand;
use App\Models\ApiExceptions;

class MedicineBrandsController extends Controller
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
     * This function is used to get Unapprove (Pending Users) list!
     *  
     * @param Request $request
     * @return JSON response 
     */

    public function getMedicineBrands(Request $request)
    {
        try {
            $this->response_data = MedicineBrand::get();
            if ($this->response_data) {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mb_fetch_success');
            } else {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mb_fetch_success_no_data');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineBrandsController::getMedicineCategories',
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

    public function getMedicineBrand(Request $request)
    {
        try {
            $this->response_data = MedicineBrand::where('id', $request->row_id)->first();
            if ($this->response_data) {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mb_fetch_success');
            } else {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mb_fetch_success_no_data');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineBrandsController::getMedicineCategories',
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

    public function addMedicineBrand(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'max:256', 'unique:medicine_brands'],
                'status' => ['required'],
            ]);

            if ($validator->fails()) {
                $this->errors = $validator->messages();
                $message = $this->mCode->getMessage('mb_validation_failed');
            } else {
                $this->response_data = MedicineBrand::create([
                    'name' => $request->name,
                    'status' => $request->status,
                ]);
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mb_added_seccess');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineBrandsController::addMedicineBrand',
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

    public function deleteMedicineBrand(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'row_id' => ['required'],
            ]);
            if ($validator->fails()) {
                $this->errors = $validator->messages();
                $message = $this->mCode->getMessage('mb_validation_failed');
            } else {
                MedicineBrand::where('id', $request->row_id)->delete();
                $this->response_data = [];
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mb_remove_seccess');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineBrandsController::deleteMedicineBrand',
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
    public function changeStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => ['required'],
                'row_id' => ['required'],
            ]);
            if ($validator->fails()) {
                $this->errors = $validator->messages();
                $message = $this->mCode->getMessage('mb_validation_failed');
            } else {
                MedicineBrand::where('id', $request->row_id)->update([
                    'status' => $request->status,
                ]);
                $this->response_code = 200;
                if ($request->status == 'active') {
                    $message = $this->mCode->getMessage('mb_active_success');
                } else {
                    $message = $this->mCode->getMessage('mb_inactive_success');
                }
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineBrandsController::changeStatus',
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


    public function updateMedicineBrand(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => ['required'],
                'name' => ['required'],
                'row_id' => ['required'],
            ]);
            if ($validator->fails()) {
                $this->errors = $validator->messages();
                $message = $this->mCode->getMessage('mb_validation_failed');
            } else {
                MedicineBrand::where('id', $request->row_id)->update([
                    'name' => $request->name,
                    'status' => $request->status,
                ]);
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mb_update_success');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineBrandsController::changeStatus',
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
