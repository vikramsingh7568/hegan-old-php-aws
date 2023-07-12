<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\HelperClass\MessageCode;

use App\Models\Admin\MedicineCategory;
use App\Models\ApiExceptions;

class MedicineCategoryController extends Controller
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

    public function getMedicineCategories(Request $request)
    {
        try {
            $this->response_data = MedicineCategory::get();
            if ($this->response_data) {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mc_fetch_success');
            } else {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mc_fetch_success_no_data');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineCategoryController::getMedicineCategories',
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

    public function getMedicineCategory(Request $request)
    {
        try {
            $this->response_data = MedicineCategory::where('id', $request->row_id)->first();
            if ($this->response_data) {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mc_fetch_success');
            } else {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mc_fetch_success_no_data');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineCategoryController::getMedicineCategory',
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

    public function addMedicineCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'max:256', 'unique:medicine_categories'],
                'm_commission' =>  ['required', 'numeric', 'digits_between:1,2'],
                'd_commission' =>  ['required', 'numeric', 'digits_between:1,2'],
                'w_commission' =>  ['required', 'numeric', 'digits_between:1,2'],
                'status' => ['required'],
            ]);

            if ($validator->fails()) {
                $this->errors = $validator->messages();
                $message = $this->mCode->getMessage('medicien_category_validation_failed');
            } else {
                $this->response_data = MedicineCategory::create([
                    'name' => $request->name,
                    'm_commission' => $request->m_commission,
                    'd_commission' => $request->d_commission,
                    'w_commission' => $request->w_commission,
                    'status' => $request->status,
                ]);
                $this->response_code = 200;
                $message = $this->mCode->getMessage('medicien_category_added_seccess');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineCategoryController::addMedicineCategory',
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

    public function deleteMedicineCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'row_id' => ['required'],
            ]);
            if ($validator->fails()) {
                $this->errors = $validator->messages();
                $message = $this->mCode->getMessage('medicien_category_validation_failed');
            } else {
                MedicineCategory::where('id', $request->row_id)->delete();
                $this->response_data = [];
                $this->response_code = 200;
                $message = $this->mCode->getMessage('medicien_category_remove_seccess');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineCategoryController::deleteMedicineCategory',
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
                $message = $this->mCode->getMessage('medicien_category_validation_failed');
            } else {
                MedicineCategory::where('id', $request->row_id)->update([
                    'status' => $request->status,
                ]);
                $this->response_code = 200;
                if ($request->status == 'active') {
                    $message = $this->mCode->getMessage('medicien_category_active_success');
                } else {
                    $message = $this->mCode->getMessage('medicien_category_inactive_success');
                }
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineCategoryController::changeStatus',
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

    public function updateMedicineCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'max:256', 'unique:medicine_categories,name,' . $request->row_id],
                'm_commission' =>  ['required', 'numeric', 'digits_between:1,2'],
                'd_commission' =>  ['required', 'numeric', 'digits_between:1,2'],
                'w_commission' =>  ['required', 'numeric', 'digits_between:1,2'],
                'status' => ['required'],
                'row_id' => ['required'],
            ]);
            if ($validator->fails()) {
                $this->errors = $validator->messages();
                $message = $this->mCode->getMessage('medicien_category_validation_failed');
            } else {

                MedicineCategory::where('id', $request->row_id)->update([
                    'name' => $request->name,
                    'm_commission' => $request->m_commission,
                    'd_commission' => $request->d_commission,
                    'w_commission' => $request->w_commission,
                    'status' => $request->status,
                ]);
                $this->response_code = 200;
                $message = $this->mCode->getMessage('mc_update_success');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'MedicineCategoryController::changeStatus',
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
