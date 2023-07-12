<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\File;

use App\HelperClass\MessageCode;

use App\Models\ApiExceptions;
use App\Models\Admin\MedicineBrand;

use App\Models\Client\Medicine;
use App\Models\Client\TaxCategory;

class MedicineInventoryController extends Controller
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


  public function getVenderMedicineInventory(Request $request)
  {
    try {
      $vender_id = ($request->vender_id) ? $request->vender_id : auth()->user()->id;
      if ($vender_id) {
        $this->response_data = Medicine::select(['products.*', 'mc.name as category_name', 'mb.name as brand_name'])
          ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'products.category_id')
          ->leftJoin('medicine_brands as mb', 'mb.id', '=', 'products.brand_id')
          ->where(['products.user_id' => $vender_id])
          ->orderBy('products.id', 'DESC')->get();
      } else {
        $this->response_data = [];
      }

      if ($this->response_data) {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('medi_fetch_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('no_medi_list_fount');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'MedicineInventoryController::getVenderMedicineInventory',
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

  public function getMedicineInventoryList(Request $request)
  {
    try {
      // $user_id = auth()->user()->id;
      $this->response_data = Medicine::select(['products.*', 'mc.name as category_name', 'mb.name as brand_name'])
        ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'products.category_id')
        ->leftJoin('medicine_brands as mb', 'mb.id', '=', 'products.brand_id')
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
        'api_name' => 'MedicineInventoryController::getMedicineInventoryList',
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
  public function getMedicineInventory(Request $request)
  {
    try {
      $row_id = $request->row_id;
      if ($row_id) {
        $medicine_data = Medicine::select([
          'products.*', 'mc.name as category_name', 'mb.name as brand_name',
          'tc.cgst', 'tc.sgst', 'tc.igst', 'tc.cess'
        ])
          ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'products.category_id')
          ->leftJoin('medicine_brands as mb', 'mb.id', '=', 'products.brand_id')
          ->leftJoin('tax_categories as tc', 'tc.id', '=', 'products.tax_category_id')
          ->where(['products.id' => $row_id])->first();

        if ($medicine_data->product_iamge) {
          $medicine_data->product_iamge_url = $medicine_data->product_iamge;
        }

        $this->response_data  = $medicine_data;
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

  /**
   * This function is used to Sinup/Register admin user!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function uploadImage($file, $path)
  {
    $fileName = $file->getClientOriginalName();
    $fileExt  = $file->getClientOriginalExtension();
    $path = $path . '/' . date('Y') . '/' . date('m');
    $destinationFolder = public_path('uploads/' . $path);
    $num   = 1;
    $newName = $fileName;
    $appendNum = false;
    while (file_exists($destinationFolder . $newName)) {
      $appendNum = true;
      $num++;
    }
    if ($appendNum) {
      $newName = $fileName . '_' . $num;
    }
    $file->move($destinationFolder, $newName);
    return $path . '/' . $newName;
  }


  protected function calculateNetRate($min_qty, $min_qty_discount, $min_qty_bonus_deal, $min_qty_trade_rate, $igst, $cess = 0)
  {
    $min_qty = (int)$min_qty;
    $min_qty_discount = (int)$min_qty_discount;
    $min_qty_trade_rate = (int)$min_qty_trade_rate;
    $igst = (int)$igst;
    $cess = (int)$cess;

    $qty_trade_rate = ($min_qty * $min_qty_trade_rate);
    $amount_without_tax = 0;
    if ($min_qty_bonus_deal == 0 || is_int($min_qty_bonus_deal)) {
      $qty_trade_rate_dis_pre = ($qty_trade_rate * $min_qty_discount) / 100;
      $amount_without_tax = ($qty_trade_rate - $qty_trade_rate_dis_pre);
    } else {

      $dealType = explode('+', $min_qty_bonus_deal);
      if (is_array($dealType)) {
        $deal_x = (int) (@$dealType[0]);
        $deal_y = (int) (@$dealType[1]);
        $deal_pre = ((($deal_y) / ($deal_x + $deal_y)) * 100);
        $deal_pre_amount = ($qty_trade_rate * $deal_pre) / 100;
        $amount_after_deal_pre = ($qty_trade_rate - $deal_pre_amount);
        $discount_amount = ($amount_after_deal_pre * $min_qty_discount) / 100;
        $amount_without_tax = ($amount_after_deal_pre - $discount_amount);
      } else {
        $qty_trade_rate_dis_pre = ($qty_trade_rate * $min_qty_discount) / 100;
        $amount_without_tax = ($qty_trade_rate - $qty_trade_rate_dis_pre);
      }
    }

    $total_tax_pre = 0;
    if ($igst && $cess) {
      $total_tax_pre = $igst + $cess;
    } else {
      $total_tax_pre = $igst;
    }

    $tax_amount = ($amount_without_tax * $total_tax_pre) / 100;

    $net_rate = $amount_without_tax + $tax_amount;
    return number_format($net_rate, 2, '.', '');
  }

  protected function calculateMargin($mrp, $net_rate, $qty)
  {
    $mrp = (int)$mrp;
    $qty = (int) $qty;
    $net_rate = (float) $net_rate;
    $margin_pre = ((($mrp - ($net_rate / $qty)) / $mrp) * 100);
    // var min_qty_margin_percentage = (((mrp - (min_qty_net_rate / qty)) / mrp) * 100);
    // $margin_pre = ((($mrp - $net_rate) * $mrp) / 100);
    return number_format($margin_pre, 2, '.', '');
  }

  /**
   * This function is used to Sinup/Register admin user!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function updateMedicineStock(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required', 'numeric'],
        'product_name' => ['required', 'max:255'],
        'category_id' => ['required'],
        'brand_name' => ['required'],
        // 'expiry_date' => ['required', 'date'],
        'available_qty' => ['required', 'numeric', 'min:1'],
        'hsn_sac_code' => ['required', 'max:255'],
        'mrp' => ['required', 'numeric'],
        'min_qty' => ['required', 'numeric'],
        'min_qty_discount' => ['nullable', 'numeric'],
        'min_qty_bonus_deal' => ['required'],
        'min_qty_trade_rate' => ['required', 'numeric'],
        'product_image' => ['nullable', File::types(['jpg', 'png'])
          ->min(1)
          ->max(2048)],
        'tax_category_id' => ['required', 'numeric', 'min:1'],
      ]);

      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('medi_validation_failed');
      } else {
        

        $min_qty = (int) $request->min_qty;
        $min_qty_discount = (int) $request->min_qty_discount;
        $min_qty_bonus_deal = $request->min_qty_bonus_deal;
        $min_qty_trade_rate = (int) $request->min_qty_trade_rate;

        $igst = 0;
        $cess = 0;
        if ($request->tax_category_id) {
          $taxData = TaxCategory::Where('id', $request->tax_category_id)->first();
          if (@$taxData) {
            $igst = (int) @$taxData['igst'];
            $cess = (int) @$taxData['cess'];
          }
        }
        $min_qty_net_rate = $this->calculateNetRate($min_qty, $min_qty_discount, $min_qty_bonus_deal, $min_qty_trade_rate, $igst, $cess);
        $min_qty_margin_percentage = $this->calculateMargin($request->mrp, $min_qty_net_rate, $request->min_qty);
        $user_id = $request->user_id;
        if ($request->brand_name) {
          $brand_name = $request->brand_name;
          $brand_detail = MedicineBrand::Where('name', $brand_name)->first();
          if ($brand_detail) {
            $brand_id =  $brand_detail['id'];
          } else {
            $brand_detail = MedicineBrand::create([
              'name' => $brand_name,
              'status' => 1,
            ]);
            $brand_id =  $brand_detail->id;
          }
        }

        $exp_data = '';
        if ($request->expiry_date) {
          $exp_data = $request->expiry_date;
        }
        $updateData = [
          'product_name' => $request->product_name,
          'description' => $request->description,
          'category_id' => $request->category_id,
          'brand_id' => ($brand_id) ? $brand_id : $request->brand_id,
          'unit_packing' => $request->unit_packing,
          'expiry_date' => ($exp_data) ? date('Y-m-d', strtotime($exp_data)) : null,
          'available_qty' => $request->available_qty,
          'hsn_sac_code' => $request->hsn_sac_code,
          'batch_no' => $request->batch_no,
          'mrp' => $request->mrp,
          'tax_category_id' => $request->tax_category_id,
          'min_qty' => $request->min_qty,
          'min_qty_discount' => ($request->min_qty_discount) ? $request->min_qty_discount : 0,
          'min_qty_bonus_deal' => $request->min_qty_bonus_deal,
          'min_qty_trade_rate' => $request->min_qty_trade_rate,
          'min_qty_net_rate' => $min_qty_net_rate,
          'min_qty_margin_percentage' => $min_qty_margin_percentage,
          'status' => ($request->status) ? $request->status : 'pending',
        ];
        if ($request->hasFile('product_image')) {
          $product_image_file = $this->uploadImage($request->product_image, 'products');
          if ($product_image_file) {
            $updateData['product_image'] = $product_image_file;
          }
        }
        $medicine = Medicine::where('id', $request->row_id)->update($updateData);

        $message = $this->mCode->getMessage('medi_update_stock_success');
        $this->response_data = $medicine;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'MedicineInventoryController::updateMedicineStock',
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
