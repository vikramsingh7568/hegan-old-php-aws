<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use PhpOffice\PhpSpreadsheet\Reader\Exception;
use App\HelperClass\MessageCode;
use App\HelperClass\Helper;

use App\Models\Admin\Brand;
use App\Models\Client\Medicine;
use App\Models\Admin\MedicineBrand;
use App\Models\Admin\MedicineCategory;
use App\Models\Client\TaxCategory;
use App\Models\Client\UserStock;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderProduct;


use App\Models\ApiExceptions;
use DB;

class AdminOrderHistoryController extends Controller
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
        'api_name' => 'OrderHistoryController::addApiExceptionInDb',
        'errors' => $ex->getMessage(),
      ];
      ApiExceptions::updateOrCreate(['api_name' => $data['api_name']], $data);
    }
  }

  /**
   * This function is used to get Order paurchase list!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function geOrderPurchaseList_group_orders(Request $request)
  {
    try {
      $this->response_data = Order::orderBy('orders.id', 'DESC')->get();
      if ($this->response_data) {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('order_purchase_fetch_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('no_order_purchase_list_fount');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'OrderHistoryController::geOrderPurchaseList',
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
   * This function is used to get Order paurchase list!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function geOrderPurchaseList(Request $request)
  {
    try {
      $data_rows = OrderProduct::select([
        'order_products.*',
        'orders.order_id as hash_order_id',
        'orders.transaction_id',
        'orders.payment_status',
      ])
        ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
        // ->where(['order_products.user_id' => $user_id])
        ->orderBy('order_products.id', 'DESC')->get();
      if ($data_rows) {
        foreach ($data_rows as $row) {
          $row->return_valid = 0;
          $minut_diff = round(abs(time() -  strtotime($row->updated_at)) / (60 * 60));
          if ($row->status == 'delivered' && $minut_diff <= 12) {
            $row->return_valid = 1;
          }
        }
        $this->response_data = $data_rows;
        $this->response_code = 200;
        $message = $this->mCode->getMessage('order_purchase_fetch_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('no_order_purchase_list_fount');
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'OrderHistoryController::geOrderPurchaseList',
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
   * This function is used to get Order paurchase list!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function updateOrderTransId(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required'],
        'order_id' => ['required'],
        'transaction_id' => ['required'],
      ]);
      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('order_trans_id_update_validation_failed');
      } else {
        $order_id =  $request->order_id;
        $order = Order::where(['orders.id' => $order_id])->first();
        $data_row = [];
        if ($order) {
          Order::where(['orders.id' => $order_id])->update([
            'transaction_id' => $request->transaction_id,
            'payment_status' => 'success',
          ]);
          OrderProduct::where(['order_id' => $order_id])->update([
            'status' => 'completed',
          ]);

          $data_row = OrderProduct::select([
            'order_products.*',
            'orders.order_id as hash_order_id',
            'orders.transaction_id',
            'orders.payment_status',
          ])
            ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
            ->where(['order_products.id' => $request->row_id])
            ->orderBy('order_products.id', 'DESC')->first();
        }
        $this->response_data = $data_row;
        if ($this->response_data) {
          $this->response_code = 200;
          $message = $this->mCode->getMessage('order_txn_id_update_success');
        } else {
          $this->response_code = 200;
          $message = $this->mCode->getMessage('no_order_fount');
        }
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'OrderHistoryController::updateOrderTransId',
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
   * This function is used to get Order paurchase list!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function updatePurchaseOrderStatus(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required'],
        'order_status' => ['required'],
        'invoice_number' => 'required_if:order_status,ready_to_ship',
        'number_of_box' => 'required_if:order_status,ready_to_ship',
        'total_weight' => 'required_if:order_status,ready_to_ship',
      ]);
      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('order_status_update_validation_failed');
      } else {
        $updateData = [
          'status' => $request->order_status,
        ];
        if ($request->invoice_number) {
          $updateData['invoice_number'] = $request->invoice_number;
        }
        if ($request->number_of_box) {
          $updateData['number_of_box'] = $request->number_of_box;
        }
        if ($request->total_weight) {
          $updateData['total_weight'] = $request->total_weight;
        }
        if ($request->tracking_url) {
          $updateData['tracking_url'] = $request->tracking_url;
        }
        OrderProduct::where(['id' => $request->row_id])->update($updateData);
        $order = OrderProduct::select([
          'order_products.*',
          'orders.order_id as hash_order_id',
          'orders.transaction_id',
          'orders.payment_status',
        ])
          ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
          ->where(['order_products.id' => $request->row_id])
          ->orderBy('order_products.id', 'DESC')->first();
        $order->return_valid = 0;
        $minut_diff = round(abs(time() -  strtotime($order->updated_at)) / (60 * 60));
        if ($order->status == 'delivered' && $minut_diff <= 12) {
          $order->return_valid = 1;
        }
        $this->response_data = $order;
        if ($this->response_data) {
          $this->response_code = 200;
          $message = $this->mCode->getMessage('order_status_update_success');
        } else {
          $this->response_code = 200;
          $message = $this->mCode->getMessage('no_order_fount');
        }
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'OrderHistoryController::updateOrderTransId',
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
   * This function is used to get urder details.
   *  
   * @param Request $request // row_id as order table auto incremented id
   * @return JSON response 
   */
  public function getOrderDetails(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required'],
      ]);
      if ($validator->fails()) {
        $message = $this->mCode->getMessage('un_authorized_access');
        $this->errors = $validator->messages();
      } else {
        $orderDetail = [];
        $user_id = auth()->user()->id;
        $register_as = auth()->user()->register_as;
        if ($register_as == 'R' || $register_as == 'H') {
          $order = Order::where('id',  $request->row_id)->where('user_id',  $user_id)->first();
        } else {
          $order = Order::where('id',  $request->row_id)->first();
        }
        if ($order) {
          $orderProducts = OrderProduct::where('order_id',  $order->id)->get();
          foreach ($orderProducts as $idx => $row) {
            $order_items[$idx] = $row;
            $proDetail = json_decode($row->product);
            // ,"product_name":"Sdaf","product_image":"http:\/\/localhost\/hegan.in\/app-
            $order_items[$idx]['product_name'] = $proDetail->product_name;
            $order_items[$idx]['product_image'] = $proDetail->product_image;
            $order_items[$idx]['unit_packing'] = $proDetail->unit_packing;
            $order_items[$idx]['category_name'] = $proDetail->category_name;
            $order_items[$idx]['seller_name'] = $proDetail->seller_name;
            $order_items[$idx]['expiry_date'] = $proDetail->expiry_date;
            $order_items[$idx]['brand_name'] = $proDetail->brand_name;
            $order_items[$idx]['mrp'] = $proDetail->mrp;
            $order_items[$idx]['min_qty'] = $proDetail->min_qty;
            $order_items[$idx]['min_qty_net_rate'] = $proDetail->min_qty_net_rate;
          }
          $orderDetail = $order;
          $orderDetail['order_items'] = $order_items;
        }

        $message = $this->mCode->getMessage('order_success');
        $this->response_data = $orderDetail;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'OrderHistoryController::getOrderDetails',
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
   * This function is used to get urder details.
   *  
   * @param Request $request // row_id as order table auto incremented id
   * @return JSON response 
   */
  public function getOrderDetail(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required'],
      ]);
      if ($validator->fails()) {
        $message = $this->mCode->getMessage('un_authorized_access');
        $this->errors = $validator->messages();
      } else {
        $orderDetail = [];
        $user_id = auth()->user()->id;
        $register_as = auth()->user()->register_as;
        $order = OrderProduct::select([
          'order_products.*',
          'orders.order_id as hash_order_id',
          'orders.transaction_id',
          'orders.payment_status',
        ])
          ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
          ->where(['order_products.id' => $request->row_id])
          ->orderBy('order_products.id', 'DESC')->first();
        if ($order) {
          $proDetail[] = json_decode($order->product);
          foreach ($proDetail as $idx => $row) {
            $order_items[$idx]['product_name'] = $row->product_name;
            $order_items[$idx]['product_image'] = $row->product_image;
            $order_items[$idx]['unit_packing'] = $row->unit_packing;
            $order_items[$idx]['category_name'] = $row->category_name;
            $order_items[$idx]['seller_name'] = $row->seller_name;
            $order_items[$idx]['expiry_date'] = $row->expiry_date;
            $order_items[$idx]['brand_name'] = $row->brand_name;
            $order_items[$idx]['mrp'] = $row->mrp;
            $order_items[$idx]['min_qty'] = $row->min_qty;
            $order_items[$idx]['min_qty_net_rate'] = $row->min_qty_net_rate;

            $order_items[$idx]['quantity'] = $order->quantity;
            $order_items[$idx]['total_mrp'] = $order->total_mrp;
            $order_items[$idx]['total_savings'] = $order->total_savings;
            $order_items[$idx]['total_payable'] = $order->total_payable;
            $order_items[$idx]['status'] = $order->status;
          }
          $orderDetail = $order;
          $orderDetail['order_items'] = $order_items;
        }

        $message = $this->mCode->getMessage('order_success');
        $this->response_data = $orderDetail;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'OrderHistoryController::getOrderDetails',
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
   * This Function is used to send order confirmation to Seller in mobile!
   *  
   * @param Request $request
   * @return Boolen TRUE/FALSE
   */
  protected function sentCancledOrderSmsToSeller($seller_id, $order_id)
  {
    $seller = User::where('id', $seller_id)->first();
    $sms = "Hello " . $seller->first_name . "  " . $seller->last_name . "
The customer with the order " . $order_id . " has canceled the respective order.
Sorry for the inconvenience caused.
Regards
Hegan";
    $mobile_no = $seller->mobile_no;
    $mobile_no = '8802355218';
    return Helper::sentSms($mobile_no, $sms);
  }

  /**
   * This Function is used to send order confirmation to Buyer in mobile!
   *  
   * @param Request $request
   * @return Boolen TRUE/FALSE
   */
  protected function sentCancledOrderSmsToBuyer($buyer_id, $order_id)
  {
    $buyer = User::where('id', $buyer_id)->first();
    $sms = "Hello " . $buyer->first_name . "  " . $buyer->last_name . "
Your Order " . $order_id . " has been successfully canceled.
Hope to serve you soon.
Regards
Hegan";

    $mobile_no = $buyer->mobile_no;
    // $mobile_no = '8802355218';
    return Helper::sentSms($mobile_no, $sms);
  }

  /**
   * This function is use to check ordered quantity is available in Your Inventory & 
   * if  ordered quantity is available then update remaining quantity
   * @param product_id $product_id
   * @param ordered_quantity $ordered_quantity
   * @return Boolen
   */

  protected function updateAvailableQuantity($product_id, $ordered_quantity)
  {
    try {
      $product = Medicine::where('id', $product_id)->first();
      if (@$product) {
        $available_quantity = (int) $product->available_qty + (int) $ordered_quantity;
        Medicine::where('id', $product_id)->update(['available_qty' => $available_quantity]);
        return true;
      } else {
        return false;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ProductController::updateAvailableQuantity',
        'errors' => $ex->getMessage(),
      ];
      $this->addApiExceptionInDb($data);
      $message = $this->mCode->getMessage('exception_error');
    }
  }

  /**
   * This function is used to get urder details.
   *  
   * @param Request $request // row_id as order table auto incremented id
   * @return JSON response 
   */
  public function cancelOrder(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required'],
      ]);
      if ($validator->fails()) {
        $message = $this->mCode->getMessage('un_authorized_access');
        $this->errors = $validator->messages();
      } else {
        $orderProduct = OrderProduct::where('id',   $request->row_id)->first();
        if ($orderProduct) {
          $order = Order::where('id',  $orderProduct->order_id)->first();
          $order_id = $order->order_id;
          OrderProduct::where(['id' => $request->row_id])->update([
            'status' => 'cancelled',
          ]);
          $this->updateAvailableQuantity($orderProduct->product_id, $orderProduct->quantity);
          //Send notification to selller
          $this->sentCancledOrderSmsToSeller($orderProduct->seller_id, $order_id);

          //Send notification to Buyer
          $this->sentCancledOrderSmsToBuyer($orderProduct->user_id, $order_id);
        }
        // $order = OrderProduct::where('id',  $request->row_id)->first();
        $order = OrderProduct::select([
          'order_products.*',
          'orders.order_id as hash_order_id',
          'orders.transaction_id',
          'orders.payment_status',
        ])
          ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
          ->where(['order_products.id' => $request->row_id])
          ->orderBy('order_products.id', 'DESC')->first();
        $message = $this->mCode->getMessage('order_canceled_success');
        $this->response_data = $order;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'OrderHistoryController::cancelOrder',
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
   * This function is used to get urder details.
   *  
   * @param Request $request // row_id as order table auto incremented id
   * @return JSON response 
   */
  public function orderCancelRefund(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required'],
      ]);
      if ($validator->fails()) {
        $message = $this->mCode->getMessage('un_authorized_access');
        $this->errors = $validator->messages();
      } else {
        $orderProduct = OrderProduct::where('id',   $request->row_id)->first();
        if ($orderProduct) {
          // $order = Order::where('id',  $orderProduct->order_id)->first();
          // $order_id= $order->order_id;
          OrderProduct::where(['id' => $request->row_id])->update([
            'status' => 'refund',
          ]);
          // $this->updateAvailableQuantity($orderProduct->product_id, $orderProduct->quantity);
          // //Send notification to selller
          // $this->sentCancledOrderSmsToSeller($orderProduct->seller_id, $order_id);

          // //Send notification to Buyer
          // $this->sentCancledOrderSmsToBuyer($orderProduct->user_id, $order_id);
        }
        // $order = OrderProduct::where('id',  $request->row_id)->first();
        $order = OrderProduct::select([
          'order_products.*',
          'orders.order_id as hash_order_id',
          'orders.transaction_id',
          'orders.payment_status',
        ])
          ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
          ->where(['order_products.id' => $request->row_id])
          ->orderBy('order_products.id', 'DESC')->first();
        $message = $this->mCode->getMessage('order_canceled_success');
        $this->response_data = $order;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'OrderHistoryController::cancelOrder',
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
   * This function is used to get urder details.
   *  
   * @param Request $request // row_id as order table auto incremented id
   * @return JSON response 
   */
  public function returnOrder(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required'],
        'product_expire' => 'required_without_all:product_damaged',
        'product_damaged' => 'required_without_all:product_expire',
      ]);
      if ($validator->fails()) {
        $message = $this->mCode->getMessage('un_authorized_access');
        $this->errors = $validator->messages();
      } else {
        $orderProduct = OrderProduct::where('id',  $request->row_id)->first();
        if ($orderProduct) {
          $return_reason = 'both';
          if (!empty($request->product_expire) && !empty($request->product_expire)) {
            $return_reason = 'both';
          } elseif (!empty($request->product_expire)) {
            $return_reason = $request->product_expire;
          } else {
            $return_reason = $request->product_damaged;
          }
          OrderProduct::where(['id' => $request->row_id])->update([
            'status' => 'return',
            'return_reason' => $return_reason,
          ]);
          $this->updateAvailableQuantity($orderProduct->product_id, $orderProduct->quantity);
          //Send notification to selller
          // $this->sentCancledOrderSmsToSeller($orderProduct->seller_id, $order_id);
          // Send notification to Buyer
          // $this->sentCancledOrderSmsToBuyer($orderProduct->user_id, $order_id);
        }
        $order = OrderProduct::select([
          'order_products.*',
          'orders.order_id as hash_order_id',
          'orders.transaction_id',
          'orders.payment_status',
        ])
          ->leftJoin('orders', 'orders.id', '=', 'order_products.order_id')
          ->where(['order_products.id' => $request->row_id])
          ->orderBy('order_products.id', 'DESC')->first();

        $message = $this->mCode->getMessage('order_return_success');
        $this->response_data = $order;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'OrderHistoryController::returnOrder',
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
   * This function is used to get urder details.
   *  
   * @param Request $request // row_id as order table auto incremented id
   * @return JSON response 
   */
  public function cancelOrder_group(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required'],
      ]);
      if ($validator->fails()) {
        $message = $this->mCode->getMessage('un_authorized_access');
        $this->errors = $validator->messages();
      } else {
        $order = Order::where('id',  $request->row_id)->first();
        if ($order) {
          Order::where(['orders.id' => $request->row_id])->update([
            'status' => 'cancelled',
          ]);
          $orderProducts = OrderProduct::where('order_id',  $order->id)->get();
          $order_id = $order->order_id;
          foreach ($orderProducts as $idx => $row) {
            OrderProduct::where(['id' => $row->id])->update([
              'status' => 'cancelled',
            ]);
            $this->updateAvailableQuantity($row->product_id, $row->quantity);
            //Send notification to selller
            $this->sentCancledOrderSmsToSeller($row->seller_id, $order_id);
          }
          //Send notification to Buyer
          $this->sentCancledOrderSmsToBuyer(auth()->user()->id, $order_id);
        }
        $order = Order::where('id',  $request->row_id)->first();
        $message = $this->mCode->getMessage('order_canceled_success');
        $this->response_data = $order;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'OrderHistoryController::cancelOrder',
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
