<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\File;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\HelperClass\MessageCode;
use App\Models\Admin\Brand;
use App\Models\Client\Medicine;
use App\Models\Admin\MedicineBrand;
use App\Models\Admin\MedicineCategory;
use App\Models\Client\TaxCategory;
use App\Models\Client\UserStock;

use App\Models\ApiExceptions;
use DB;

class StockController extends Controller
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
  public function getMedicinesList(Request $request)
  {
    try {
      $user_id = auth()->user()->id;
      $this->response_data = Medicine::select([
        'products.*',
        'mc.name as category_name', 'mb.name as brand_name'
      ])
        ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'products.category_id')
        ->leftJoin('medicine_brands as mb', 'mb.id', '=', 'products.brand_id')
        ->where(['products.user_id' => $user_id])
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
        'api_name' => 'MedicineController::getMedicineCategories',
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