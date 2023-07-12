<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\HelperClass\MessageCode;

use App\Models\Client\Medicine;
use App\Models\Client\TaxCategory;
use App\Models\Client\UserStock;

use App\Models\ApiExceptions;
use DB;

class DashboardController extends Controller
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
  public function getSellerStockCards(Request $request)
  {
    try {
      $user_id = auth()->user()->id;
      $total_medi = Medicine::select([
        'products.*',
      ])
        ->where(['products.user_id' => $user_id])
        ->count();
      $total_expire_medi = Medicine::select([
        'products.*',
      ])
        ->where(['products.user_id' => $user_id])
        ->whereDate('expiry_date', '<', now())
        ->count();

      $available_stock = Medicine::select([
        'products.*',
      ])
        ->where(['products.user_id' => $user_id])
        ->sum('available_qty');

      $expire_available_stock = Medicine::select([
        'products.*',
      ])
        ->where(['products.user_id' => $user_id])
        ->whereDate('expiry_date', '<', now())
        ->sum('available_qty');

      $this->response_code = 200;
      $message = $this->mCode->getMessage('medi_fetch_success');
      $this->response_data = [
        [
          'name' => 'Total Medicine',
          'amount' => (@$total_medi) ? @$total_medi : 0,
          'icon' => 'assessment'
        ],
        [
          'name' => 'Out of Stock',
          'amount' => (@$total_expire_medi) ? @$total_expire_medi : 0,
          'icon' => 'group'
        ],
        [
          'name' => 'Available Stock',
          'amount' => (@$available_stock) ? @$available_stock : 0,
          'icon' => 'inventory'
        ],
        [
          'name' => 'Expire Medicine',
          'amount' => (@$expire_available_stock) ? @$expire_available_stock : 0,
          'icon' => 'shopping_cart'
        ],
      ];
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'DashboardController::getSellerStockCards',
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
