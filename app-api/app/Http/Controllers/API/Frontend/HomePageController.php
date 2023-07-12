<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use App\Models\Client\Medicine;
use App\Models\Admin\MedicineCategory;
use App\Models\Admin\MedicineBrand;

use App\HelperClass\MessageCode;
use App\Models\User;
use App\Models\ApiExceptions;

class HomePageController extends Controller
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
    public function getHotSellerList(Request $request)
    {
        try {
            $query = Medicine::select([
                'products.*',
                'mc.name as category_name', 'mb.name as brand_name',
                'u.user_name as seller_name'
            ])
                ->join('users as u', 'u.id', '=', 'products.user_id')
                ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'products.category_id')
                ->leftJoin('medicine_brands as mb', 'mb.id', '=', 'products.brand_id')
                ->where(['products.status' => 'approved']);
            $query->orderBy('products.id', 'DESC')->get();

            $this->response_data  = $query->get();
            if ($this->response_data) {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('medi_fetch_success');
            } else {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('no_medi_list_fount');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'HomePageController::getMedicinesList',
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
    public function getAuthHotSellerList(Request $request)
    {
        try {
            $query = Medicine::select([
                'products.*',
                'mc.name as category_name', 'mb.name as brand_name',
                'u.user_name as seller_name'
            ])
                ->join('users as u', 'u.id', '=', 'products.user_id')
                ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'products.category_id')
                ->leftJoin('medicine_brands as mb', 'mb.id', '=', 'products.brand_id')
                ->where(['products.status' => 'approved']);
            if (auth()->user()) {
                $query->where('products.user_id', '!=', auth()->user()->id);
                // $user_id = auth()->user()->id;
                // switch (auth()->user()->register_as) {
                //     case 'M':
                //         break;
                //     case 'D':
                //     case 'W':
                //         $query->where('u.register_as', 'M');
                //         break;
                //     case 'R':
                //     case 'H':
                //         $query->where('u.register_as', 'D')->orWhere('u.register_as', 'W');
                //         break;
                // }
            }
            $query->orderBy('products.id', 'DESC')->get();

            $this->response_data  = $query->get();
            if ($this->response_data) {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('medi_fetch_success');
            } else {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('no_medi_list_fount');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'HomePageController::getMedicinesList',
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
    public function getMedicinesList(Request $request)
    {
        try {
            // $user_id = auth()->user()->id;
            $this->response_data = Medicine::select([
                'products.*',
                'mc.name as category_name', 'mb.name as brand_name',
                'u.user_name as seller_name'
            ])
                ->leftJoin('users as u', 'u.id', '=', 'products.user_id')
                ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'products.category_id')
                ->leftJoin('medicine_brands as mb', 'mb.id', '=', 'products.brand_id')
                ->where(['products.status' => 'approved'])
                ->orderBy('products.id', 'DESC')->get();
            if ($this->response_data) {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('medi_fetch_success');
            } else {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('no_medi_list_fount');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'HomePageController::getMedicinesList',
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
    public function getCategoriesList(Request $request)
    {
        try {
            $this->response_data = MedicineCategory::select([
                'id',
                'name',
                'status'
            ])
                ->where(['status' => 'active'])
                ->orderBy('id', 'DESC')->get();
            if ($this->response_data) {
                foreach ($this->response_data as $idx => $row) {
                    $row['cat_image'] = url('public/uploads/cat_thumbs/cat_img_' . $row['id'] . '.jpg');
                    $this->response_data[$idx] = $row;
                }
                $this->response_code = 200;
                $message = $this->mCode->getMessage('medi_cat_list_success');
            } else {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('no_medi_cat_list_fount');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'HomePageController::getCategoriesList',
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
    public function getBrandsList(Request $request)
    {
        try {
            $this->response_data = MedicineBrand::select([
                'id',
                'name',
                'status'
            ])
                ->where(['status' => 'active'])
                ->orderBy('id', 'DESC')->get();
            if ($this->response_data) {
                // foreach ($this->response_data as $idx => $row) {
                //     $row['cat_image'] = url('public/uploads/cat_thumbs/cat_img_' . $row['id'] . '.jpg');
                //     $this->response_data[$idx] = $row;
                // }
                $this->response_code = 200;
                $message = $this->mCode->getMessage('medi_brand_list_success');
            } else {
                $this->response_code = 200;
                $message = $this->mCode->getMessage('no_medi_brand_list_fount');
            }
        } catch (\Exception $e) {
            $data = [
                'api_name' => 'HomePageController::getBrandsList',
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
