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
use Carbon\Carbon;

use App\Models\ApiExceptions;
use DB;

class MedicineController extends Controller
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


  /**
   * This function is used to get Unapprove (Pending Users) list!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function getMedicine(Request $request)
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
   * This function is used to get Unapprove (Pending Users) list!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function getCategories(Request $request)
  {
    try {
      $this->response_data = MedicineCategory::where('status', 'active')->get();
      if ($this->response_data) {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('medi_cat_fetch_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('no_medi_cat_list_fount');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'MedicineController::getCategories',
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
  public function getBrands(Request $request)
  {
    try {
      $this->response_data = MedicineBrand::where('status', 'active')->get();
      if ($this->response_data) {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('medi_brand_fetch_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('no_medi_brand_list_fount');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'MedicineController::getBrands',
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
  public function getTaxCategories(Request $request)
  {
    try {
      $this->response_data = TaxCategory::where('status', 'active')->get();
      if ($this->response_data) {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('tax_cat_fetch_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('no_tax_cat_list_fount');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'MedicineController::TaxCategory',
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

  public function uploadImage($file, $path)
  {
    $fileName = $file->getClientOriginalName();
    $fileExt  = $file->getClientOriginalExtension();
    $path = $path . '/' . date('Y') . '/' . date('m');
    $destinationFolder = public_path('uploads/' . $path);
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
    // $margin_pre = ((($mrp - $net_rate) * $mrp) / 100);
    return number_format($margin_pre, 2, '.', '');
  }
  /**
   * This function is used to Sinup/Register admin user!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function addMedicineStock(Request $request)
  {
    try {
      //   $free_deal = '4(2+3)';
      //   if($free_deal){
      //     for($i = 0; $i< strlen($free_deal); $i++){
      //         if($free_deal[$i] === "+"){
      //          $x = '';
      //          $y = '';
      //          for($j = 0; $j <strlen($free_deal); $j++){
      //             if($free_deal[$j] != "+"){
      //                $x+=$free_deal[$j];
      //             }else{
      //               break;
      //             }
      //          }

      //          for($j = 0; $j< strlen($free_deal); $j++){
      //             if($free_deal[$j] != "+"){
      //                $y+=$free_deal[$j];
      //             }else{
      //              $y=0;
      //             }
      //          }
      //          $x = (int)($x);
      //          $y = (int)($y);

      //           $freeT = ($y*100)/($x+$y);
      //           $freeT = $freeT.toFixed(2);
      //           $data = $Quantity*$tradeRate;
      //           $data2  = $data*$freeT/100;
      //           $data3 = $data-$data2;
      //           $dis = ($discount/100)*$data3;
      //           $AMt = $data3 -$dis;
      //           $vik = $IGST+$CESS;
      //           $v = $AMt*$vik/100;
      //           echo $final = ($v+$AMt).toFixed(2);

      //           return $final; 
      //         }
      //       }
      //    }

      //  $data = $Quantity*$tradeRate;
      //  $dis = ($discount/100)*$data;
      //  $AMt = $data -$dis;
      //  if($IGST && $CESS){
      //   $vik = (int)($IGST) + (int)($CESS);
      //  }else if($IGST){
      //   $vik = (int)($IGST);
      //  } else if($CESS){
      //   $vik = (int)($CESS);
      //  }else{
      //   $final = ($AMt).toFixed(2);
      //  }
      //    if($IGST && $CESS){
      //       $vik = (int)($IGST) + (int)($CESS);
      //       $v = ($AMt*$vik)/100;
      //       echo $final = ($v+$AMt).toFixed(2);
      //       return $final;
      //    }
      //    else if($IGST){
      //       $vik = (int)($IGST);
      //       $v = AMt*vik/100
      //       let final = (v+AMt).toFixed(2);
      //       setAmount(final)
      //       return
      //    }
      //    else if(CESS){
      //       let vik = parseInt(CESS);
      //       let v = AMt*vik/100
      //       let final = (v+AMt).toFixed(2);
      //       setAmount(final)
      //       return
      //    }
      //    else if(!CESS || !IGST){
      //    let final = (AMt).toFixed(2);
      //    setAmount(final)
      //    return
      //    }

      $validator = Validator::make($request->all(), [
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
        if ($request->hasFile('product_image')) {
          $product_image_file = $this->uploadImage($request->product_image, 'products');
        } else {
          $product_image_file  = '';
        }
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
        $brand_id = 1;
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
        $user_id = auth()->user()->id;
        $medicine = Medicine::create([
          'user_id' => auth()->user()->id,
          'product_name' => $request->product_name,
          'description' => $request->description,
          'category_id' => $request->category_id,
          'brand_id' => $brand_id,
          'unit_packing' => $request->unit_packing,
          'expiry_date' => ($exp_data) ? date('Y-m-d', strtotime($exp_data)) : null,
          'available_qty' => $request->available_qty,
          'hsn_sac_code' => $request->hsn_sac_code,
          'batch_no' => $request->batch_no,
          'mrp' => $request->mrp,
          'product_image' => $product_image_file,
          'tax_category_id' => $request->tax_category_id,
          'min_qty' => $request->min_qty,
          'min_qty_discount' => ($request->min_qty_discount) ? $request->min_qty_discount : 0,
          'min_qty_bonus_deal' => $request->min_qty_bonus_deal,
          'min_qty_trade_rate' => $request->min_qty_trade_rate,
          'min_qty_net_rate' => $min_qty_net_rate,
          'min_qty_margin_percentage' => $min_qty_margin_percentage,
          'status' => ($request->status) ? $request->status : 'pending',
        ]);

        if (@$medicine) {
          $matchThese = ['user_id' => $user_id, 'product_id' => $medicine->id];
          UserStock::updateOrCreate($matchThese, [
            'user_id' => $user_id,
            'product_id' => $medicine->id,
            'available_stock' => (DB::raw('available_stock+' . $request->available_qty)) ? DB::raw('available_stock+' . $request->available_qty) : 0,
          ]);
        }


        $message = $this->mCode->getMessage('medi_add_stock_success');
        $this->response_data = $medicine;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'MedicineController::addMedicineStock',
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

        if (@$medicine) {
          $medicine = Medicine::where('id', $request->row_id)->first();
          // $matchThese = ['user_id' => $user_id, 'product_id' => $medicine->id];
          // UserStock::updateOrCreate($matchThese, [
          //   'user_id' => $user_id,
          //   'product_id' => $medicine->id,
          //   'available_stock' => (DB::raw('available_stock+' . $request->available_qty)) ? DB::raw('available_stock+' . $request->available_qty) : 0,
          // ]);
        }
        $message = $this->mCode->getMessage('medi_update_stock_success');
        $this->response_data = $medicine;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'MedicineController::updateMedicineStock',
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
  public function deleteMedicine(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required'],
      ]);
      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('medi_validation_failed');
      } else {
        Medicine::where('id', $request->row_id)->delete();
        $this->response_data = [];
        $this->response_code = 200;
        $message = $this->mCode->getMessage('medi_remove_seccess');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'MedicineController::deleteMedicine',
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

  public function getExcelFileData(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'import_products' => ['required', 'file', 'mimes:xls,xlsx', 'min:1', 'max:2048'],
      ]);
      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('inventry_upload_falid');
      } else {
        $import_products_file = $request->file('import_products');
        $spreadsheet = IOFactory::load($import_products_file->getRealPath());
        $sheet        = $spreadsheet->getActiveSheet();
        $row_limit    = $sheet->getHighestDataRow();
        $column_limit = $sheet->getHighestDataColumn();
        $row_range    = range(2, $row_limit);
        $column_range = range('F', $column_limit);
        $startcount = 2;
        $data = array();
        foreach ($row_range as $row) {
          $data[] = [
            'product_name' => $sheet->getCell('A' . $row)->getValue(),
            'category' => $sheet->getCell('B' . $row)->getValue(),
            'brand' => $sheet->getCell('C' . $row)->getValue(),
            'description' => $sheet->getCell('D' . $row)->getValue(),
            'unit_packing' => $sheet->getCell('E' . $row)->getValue(),
            'expiry_date' => $sheet->getCell('F' . $row)->getFormattedValue(),
            'available_qty' => $sheet->getCell('G' . $row)->getValue(),
            'hsn_sac_code' => $sheet->getCell('H' . $row)->getValue(),
            'batch_no' => $sheet->getCell('I' . $row)->getValue(),
            'mrp' => $sheet->getCell('J' . $row)->getValue(),
            'min_qty_trade_rate' => $sheet->getCell('K' . $row)->getValue(),
            'tax' => $sheet->getCell('L' . $row)->getValue(),
            'min_qty' => $sheet->getCell('M' . $row)->getValue(),
            'min_qty_discount' => $sheet->getCell('N' . $row)->getValue(),
            'min_qty_bonus_deal' => $sheet->getCell('O' . $row)->getValue(),
          ];
          $startcount++;
        }
        $this->response_data = $data;
        $this->response_code = 200;
        $message = $this->mCode->getMessage('inventry_upload_success');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'MedicineController::getExcelFileData',
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
  public function bulkImportExcelFileData(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'import_file_data' => ['required', 'array', 'min:1', 'max:2048'],
      ]);
      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('inventry_import_falid');
      } else {
        $fileData = [];

        if (is_array($request->import_file_data)) {
          foreach ($request->import_file_data as $row) {
            // print_r($row);
            // exit;
            $medicineCategory = trim($row['category']);
            $category_id = 0;
            if ($medicineCategory) {
              $catData = MedicineCategory::Where('name', trim($row['category']))->first();
              if (@$catData) {
                $category_id = @$catData['id'];
              }
            }

            $medicineBrand = trim($row['brand']);
            $brand_id = 0;
            if ($medicineBrand) {
              $brandData = MedicineBrand::Where('name', trim($row['brand']))->first();
              if (@$brandData) {
                $brand_id = @$brandData['id'];
              } else {
                $newbrand = MedicineBrand::create([
                  'name' => $row['brand'],
                  'status' => 'active'
                ]);
                $brand_id = $newbrand->id;
              }
            }

            $tax_igst = trim($row['tax']);
            $tax_category_id = 0;
            $igst = $tax_igst;
            $cess = 0;
            if ($tax_igst != '') {
              $taxData = TaxCategory::Where('igst', trim($row['tax']))->first();
              if (@$taxData) {
                $tax_category_id = @$taxData['id'];
                $igst = (int) @$taxData['igst'];
                $cess = (int) @$taxData['cess'];
              }
            }

            $min_qty = (int) $row['min_qty'];
            $mrp = (int) $row['mrp'];
            $min_qty_discount = (int) $row['min_qty_discount'];
            $min_qty_bonus_deal = $row['min_qty_bonus_deal'];
            $min_qty_trade_rate = (int) $row['min_qty_trade_rate'];

            $min_qty_net_rate = $this->calculateNetRate($min_qty, $min_qty_discount, $min_qty_bonus_deal, $min_qty_trade_rate, $igst, $cess);
            $min_qty_margin_percentage = $this->calculateMargin($mrp, $min_qty_net_rate, $min_qty);
            if ($row['expiry_date']) {
              $expiry_date = str_replace('/', '-', $row['expiry_date']);
              $expiry_date= @Carbon::createFromFormat('m/d/Y', $row['expiry_date'])->format('Y-m-d');
            } else {
              $expiry_date = '';
            }

            Medicine::create([
              'user_id' => auth()->user()->id,
              'product_name' => trim($row['product_name']),
              'description' => trim($row['description']),
              'category_id' => $category_id,
              'brand_id' => $brand_id,
              'unit_packing' => $row['unit_packing'],
              'expiry_date' => ($expiry_date) ? date('Y-m-d', strtotime($expiry_date)) : null,
              'available_qty' => ($row['available_qty']) ? $row['available_qty'] : 0,
              'hsn_sac_code' => trim($row['hsn_sac_code']),
              'batch_no' => trim($row['batch_no']),
              'mrp' => ($row['mrp']) ? $row['mrp'] : 0,
              'tax_category_id' => $tax_category_id,
              'min_qty' => ($row['min_qty']) ? $row['min_qty'] : 0,
              'min_qty_discount' => ($row['min_qty_discount']) ? $row['min_qty_discount'] : 0,
              'min_qty_bonus_deal' => ($row['min_qty_bonus_deal']) ? $row['min_qty_bonus_deal'] : 0,
              'min_qty_trade_rate' => trim($row['min_qty_trade_rate']),
              'min_qty_net_rate' => ($min_qty_net_rate) ? $min_qty_net_rate : 0,
              'min_qty_margin_percentage' => ($min_qty_margin_percentage) ? $min_qty_margin_percentage : 0,
              'status' => 'pending',
            ]);
          }
        }
        $this->response_data = $fileData;
        $this->response_code = 200;
        $message = $this->mCode->getMessage('inventry_import_success');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'MedicineController::getExcelFileData',
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
