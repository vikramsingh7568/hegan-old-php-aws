<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;
use App\HelperClass\Helper;
use App\HelperClass\MessageCode;

use App\Models\Client\Medicine;
use App\Models\Admin\MedicineCategory;
use App\Models\User;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\ApiExceptions;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
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
  public function getSearchProducts(Request $request)
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
      if ($request->selected_categories) {
        $query->where('products.category_id', $request->selected_categories);
      }
      if ($request->selected_brands) {
        $query->where('products.brand_id', $request->selected_brands);
      }
      if ($request->q) {
        $search = $request->q;
        $query->where(function ($query) use ($search) {
          $query->where('products.product_name', 'LIKE', '%' . $search . '%')
            ->orWhere('mc.name', 'LIKE', '%' . $search . '%')
            ->orWhere('mb.name', 'LIKE', '%' . $search . '%');
        });
      }
      $query->orderBy('products.id', 'DESC');
      $this->response_data  = $query->paginate(9);
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
  public function getAuthSearchProducts(Request $request)
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
      if ($request->selected_categories) {
        $query->where('products.category_id', $request->selected_categories);
      }
      if ($request->selected_brands) {
        $query->where('products.brand_id', $request->selected_brands);
      }
      if ($request->q) {
        $search = $request->q;
        $query->where(function ($query) use ($search) {
          $query->where('products.product_name', 'LIKE', '%' . $search . '%')
            ->orWhere('mc.name', 'LIKE', '%' . $search . '%')
            ->orWhere('mb.name', 'LIKE', '%' . $search . '%');
        });
      }
      if (auth()) {
        $query->where('products.user_id', '!=', auth()->user()->id);
        // switch (auth()->user()->register_as) {
        //   case 'M':
        //     break;
        //   case 'D':
        //   case 'W':
        //     $query->where('u.register_as', 'M');
        //     break;
        //   case 'R':
        //   case 'H':
        //     $query->where(function ($query) {
        //       $query->where('u.register_as', 'D')
        //         ->orWhere('u.register_as', 'W');
        //     });
        //     break;
        // }
      }

      $query->orderBy('products.id', 'DESC');

      $this->response_data  = $query->paginate(9);
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
  public function getProduct(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'row_id' => ['required', 'numeric'],
      ]);
      if ($validator->fails()) {
        $this->errors = $validator->messages();
        $message = $this->mCode->getMessage('no_medi_list_fount');
      } else {
        $row_id = $request->row_id;
        $this->response_data = Medicine::select([
          'products.*',
          'mc.name as category_name', 'mb.name as brand_name',
          'u.user_name as seller_name'
        ])
          ->leftJoin('users as u', 'u.id', '=', 'products.user_id')
          ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'products.category_id')
          ->leftJoin('medicine_brands as mb', 'mb.id', '=', 'products.brand_id')
          ->where(['products.status' => 'approved'])
          ->where(['products.id' => $row_id])->first();
        if ($this->response_data) {
          $this->response_code = 200;
          $message = $this->mCode->getMessage('medi_fetch_success');
        } else {
          $this->response_code = 200;
          $message = $this->mCode->getMessage('no_medi_list_fount');
        }
      }
    } catch (\Exception $e) {
      $data = [
        'api_name' => 'ProductController::getProduct',
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
  public function getRelatedProducts(Request $request)
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
      $this->response_data = $query->orderBy('products.id', 'DESC')->get();
      if ($this->response_data) {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('related_pro_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('no_related_pro_found');
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
  public function getAuthRelatedProducts(Request $request)
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
      if (auth()->user()->id) {
        $query->where('products.user_id', '!=', auth()->user()->id);
        // switch (auth()->user()->register_as) {
        //   case 'M':
        //     break;
        //   case 'D':
        //   case 'W':
        //     $query->where('u.register_as', 'M');
        //     break;
        //   case 'R':
        //   case 'H':
        //     $query->where('u.register_as', 'D')->orWhere('u.register_as', 'W');
        //     break;
        // }
      }
      $this->response_data = $query->orderBy('products.id', 'DESC')->get();
      if ($this->response_data) {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('related_pro_success');
      } else {
        $this->response_code = 200;
        $message = $this->mCode->getMessage('no_related_pro_found');
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
   * This function is used to Sinup/Register admin user!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function updateUserCart(Request $request)
  {
    try {
      if (auth()) {
        $validator = Validator::make($request->all(), [
          'row_id' => ['required'],
          'quantity' => ['required'],
        ]);
        if ($validator->fails()) {
          $this->errors = $validator->messages();
          $message = $this->mCode->getMessage('update_cart_validation_failed');
        } else {

          $user_id = auth()->user()->id;
          $userCart = Cart::where('user_id', $user_id)->first();
          if (@$userCart) {
            $cortItems[] = [
              'id' => $request->row_id,
              'quantity' => $request->quantity
            ];
            $oldCortItems = json_decode($userCart['cart_items']);
            if (is_array($oldCortItems)) {
              foreach ($oldCortItems as $idx => $row) {
                if (@$row->id == $request->row_id) {
                  $cortItems[0] = [
                    'id' => $request->row_id,
                    'quantity' => ($cortItems[0]['quantity'] + $row->quantity)
                  ];
                } else {
                  $cortItems[] = $row;
                }
              }
            }
          } else {
            $cortItems[] = [
              'id' => $request->row_id,
              'quantity' => $request->quantity
            ];
          }
          $matchThese = ['user_id' => $user_id];
          Cart::updateOrCreate($matchThese, [
            'user_id' => $user_id,
            'cart_items' => json_encode($cortItems),
          ]);
          $message = $this->mCode->getMessage('update_cart_success');
          $this->response_data = $cortItems;
          $this->response_code = 200;
        }
      } else {
        $this->response_code = 504;
        $message = $this->mCode->getMessage('un_authorized_access');
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ProductController::updateUserCart',
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
  public function getUserCart(Request $request)
  {
    try {
      if (auth()) {
        $cortItems = [];
        $user_id = auth()->user()->id;
        $userCart = Cart::where('user_id', $user_id)->first();
        if (@$userCart) {
          $cortItems = json_decode($userCart['cart_items']);
        }
        $message = $this->mCode->getMessage('get_cart_success');
        $this->response_data = $cortItems;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ProductController::getUserCart',
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


  public function getProductForCart($row_id, $quantity)
  {
    $product = Medicine::select([
      'products.*',
      'mc.name as category_name', 'mb.name as brand_name',
      'u.user_name as seller_name'
    ])
      ->leftJoin('users as u', 'u.id', '=', 'products.user_id')
      ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'products.category_id')
      ->leftJoin('medicine_brands as mb', 'mb.id', '=', 'products.brand_id')
      ->where(['products.status' => 'approved'])
      ->where(['products.id' => $row_id])->first();
    if ($product) {
      $item_additional_discount_total = 0;
      $item_delivery_charges = 0;

      $item_mrp_total  = ($quantity * $product->mrp);
      $item_total  = (($quantity * $product->min_qty_net_rate) / $product->min_qty);
      $item_total_savings = ($item_mrp_total - $item_total);

      $product_data = [
        'id' => $row_id,
        'quantity' => $quantity,
        'user_id' => $product->user_id,
        'product_name' => $product->product_name,
        'product_image' => $product->product_image,
        'unit_packing' => $product->unit_packing,
        'brand_name' => $product->brand_name,
        'category_name' => $product->category_name,
        'seller_name' => $product->seller_name,
        'expiry_date' => $product->expiry_date,
        'available_qty' => $product->available_qty,
        'mrp' => $product->mrp,
        'min_qty' => $product->min_qty,
        'min_qty_discount' => $product->min_qty_discount,
        'min_qty_bonus_deal' => $product->min_qty_bonus_deal,
        'min_qty_trade_rate' => $product->min_qty_trade_rate,
        'min_qty_net_rate' => $product->min_qty_net_rate,

        'item_mrp_total' => number_format(ceil($item_mrp_total), 0, '.', ''),
        'item_additional_discount_total' =>  number_format(ceil($item_additional_discount_total), 0, '.', ''),
        'item_delivery_charges' => number_format(ceil($item_delivery_charges), 0, '.', ''),
        'item_total_savings' => number_format(ceil($item_total_savings), 0, '.', ''),
        'item_total' => number_format(ceil($item_total), 0, '.', ''),
      ];
    } else {
      $product_data = [];
    }

    return ($product_data) ? $product_data : false;
  }

  /**
   * This function is used to get cart summary!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function getCartSummary(Request $request)
  {
    try {
      if (auth()) {
        $user_id = auth()->user()->id;

        $cortItems = [];
        $cortSummery = [];
        $cart_items = [];
        $cart_mrp_total = 0;
        $cart_additional_discount_total = 0;
        $total_delivery_charges = 0;
        $total_savings = 0;
        $cart_total = 0;
        $userCart = Cart::where('user_id', $user_id)->first();
        if (@$userCart) {
          $cortItems = json_decode($userCart['cart_items']);
          if (is_array($cortItems)) {
            foreach ($cortItems as $idx => $row) {
              $product = $this->getProductForCart($row->id, $row->quantity);
              $cart_items[] = $product;

              $cart_mrp_total = $cart_mrp_total + ($product['item_mrp_total'] ?? 0);
              $cart_additional_discount_total = $cart_additional_discount_total + ($product['item_additional_discount_total'] ?? 0);
              $total_delivery_charges = $total_delivery_charges + ($product['item_delivery_charges'] ?? 0);
              $total_savings = $total_savings + ($product['item_total_savings'] ?? 0);
              $cart_total = $cart_total + ($product['item_total'] ?? 0);
            }
          }
        }

        $total_payable = ($cart_total - $cart_additional_discount_total) + $total_delivery_charges;

        $cortSummery = [
          'cart_items' =>  $cart_items,
          'cart_mrp_total' =>   number_format(ceil($cart_mrp_total), 0, '.', ''),
          'cart_additional_discount_total' =>   number_format(ceil($cart_additional_discount_total), 0, '.', ''),
          'delivery_charges' =>  number_format(ceil($total_delivery_charges), 0, '.', ''),
          'cart_total' =>   number_format(ceil($cart_total), 0, '.', ''),
          'total_savings' =>  number_format(ceil($total_savings), 0, '.', ''),
          'total_payable' =>  number_format(ceil($total_payable), 0, '.', '')
        ];
        $message = $this->mCode->getMessage('get_cart_summary_success');
        $this->response_data = $cortSummery;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ProductController::getCartSummary',
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
   * This function is used to remove product from Cart!
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function removeProductFromCart(Request $request)
  {
    try {
      if (auth()) {
        $validator = Validator::make($request->all(), [
          'row_id' => ['required'],
        ]);
        if ($validator->fails()) {
          $this->errors = $validator->messages();
          $message = $this->mCode->getMessage('update_cart_validation_failed');
        } else {
          $user_id = auth()->user()->id;
          $cortItems = [];
          $userCart = Cart::where('user_id', $user_id)->first()->toArray();
          if (@$userCart) {
            $oldCortItems = json_decode($userCart['cart_items']);
            if (is_array($oldCortItems)) {
              foreach ($oldCortItems as $idx => $row) {
                if (@$row->id == $request->row_id) {
                } else {
                  $cortItems[] = $row;
                }
              }
            }
          } else {
            $cortItems = [];
          }
          $matchThese = ['user_id' => $user_id];
          Cart::updateOrCreate($matchThese, [
            'user_id' => $user_id,
            'cart_items' => json_encode($cortItems),
          ]);
          $message = $this->mCode->getMessage('update_cart_success');
          $this->response_data = $cortItems;
          $this->response_code = 200;
        }
      } else {
        $this->response_code = 504;
        $message = $this->mCode->getMessage('un_authorized_access');
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ProductController::removeProductFromCart',
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
   * This function is used to get hash order ID!
   *  
   * @param (int) product_id as $row_id
   * @return JSON response 
   */
  public function getOrderId($row_id)
  {
    $order_id = "2103#HEGAN-" . date('Y') . $row_id;
    return $order_id;
  }

  /**
   * This Function is used to send order confirmation to Seller in mobile!
   *  
   * @param Request $request
   * @return Boolen TRUE/FALSE
   */
  protected function sentOrderSmsToSeller($seller_id, $order_id)
  {
    $seller = User::where('id', $seller_id)->first();
    $sms = "Hello " . $seller->first_name . "  " . $seller->last_name . "
Your Inventory has received order " . $order_id . " from a customer.
Regards
Hegan";
    $mobile_no = $seller->mobile_no;
    // $mobile_no = '8802355218';
    return Helper::sentSms($mobile_no, $sms);
  }

  /**
   * This Function is used to send order confirmation to Buyer in mobile!
   *  
   * @param Request $request
   * @return Boolen TRUE/FALSE
   */
  protected function sentOrderSmsToBuyer($buyer_id, $order_id)
  {
    $buyer = User::where('id', $buyer_id)->first();
    $sms = "Hello " . $buyer->first_name . "  " . $buyer->last_name . "
Your Order " . $order_id . " has been successfully placed.
Hope you enjoy ordering with us.
Thank you
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

  protected function checkAvailableQuantity($product_id, $ordered_quantity, $update = 0)
  {
    try {
      $product = Medicine::where('id', $product_id)->first();
      if (@$product) {
        if ((int) $product->available_qty >=  (int) $ordered_quantity) {
          if ($update) {
            $remaining_quantity = (int) $product->available_qty - (int) $ordered_quantity;
            Medicine::where('id', $product_id)->update(['available_qty' => $remaining_quantity]);
          }
          return true;
        }
      } else {
        return false;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ProductController::checkAvailableQuantity',
        'errors' => $ex->getMessage(),
      ];
      $this->addApiExceptionInDb($data);
      $message = $this->mCode->getMessage('exception_error');
    }
  }

  /**
   * This function is used to create urder.
   *  
   * @param Request $request
   * @return JSON response 
   */
  public function createOrder(Request $request)
  {
    try {
      if (auth()) {
        $user_id = auth()->user()->id;
        $userCart = Cart::where('user_id', $user_id)->first();
        if (@$userCart) {
          $cortItems = json_decode($userCart['cart_items']);
          if (is_array($cortItems)) {
            $total_mrp = 0;
            $total_additional_discount = 0;
            $total_delivery_charges = 0;
            $total_savings = 0;
            $total_payable = 0;
            $orderData = [
              'user_id' => $user_id,
              'order_id' => '',
              'total_mrp' => $total_mrp,
              'total_savings' => $total_savings,
              'total_payable' => $total_payable,
              'transaction_id' => '',
              'payment_status' => 'pending',
              'status' => 'pending',

            ];
            $order = Order::Create($orderData);
            $order_id = $this->getOrderId($order->id);
            foreach ($cortItems as $idx => $row) {
              $product = $this->getProductForCart($row->id, $row->quantity);
              // Check Change available quantity.
              $is_valid_quantity = $this->checkAvailableQuantity($row->id, $row->quantity, 1);
              if ($is_valid_quantity) {
                $seller_id = @$product['user_id'];
                $orderProductData = [
                  'user_id' => $user_id,
                  'order_id' => $order->id,
                  'product_id' => $row->id,
                  'seller_id' => $seller_id,
                  'product' => json_encode($product),
                  'quantity' => $row->quantity,
                  'total_mrp' => @$product['item_mrp_total'],
                  'additional_discount' => @$product['item_additional_discount_total'],
                  'delivery_charges' => @$product['item_delivery_charges'],
                  'total_savings' => @$product['item_total_savings'],
                  'total_payable' => @$product['item_total'],
                  'status' => 'pending',
                ];
                OrderProduct::Create($orderProductData);
                //Send notification to selller
                $this->sentOrderSmsToSeller($seller_id, $order_id);

                $total_mrp = $total_mrp + ($product['item_mrp_total'] ?? 0);
                $total_additional_discount = $total_additional_discount + ($product['item_additional_discount_total'] ?? 0);
                $total_delivery_charges = $total_delivery_charges + ($product['item_delivery_charges'] ?? 0);
                $total_savings = $total_savings + ($product['item_total_savings'] ?? 0);
                $total_payable = $total_payable + ($product['item_total'] ?? 0);
              }
            }

            $orderUpdateData = [
              'order_id' => $order_id,
              'total_mrp' => $total_mrp,
              'additional_discount' => $total_additional_discount,
              'delivery_charges' => $total_delivery_charges,
              'total_savings' => $total_savings,
              'total_payable' => $total_payable,
            ];
            $orderDet = Order::where('id', $order->id)->update($orderUpdateData);
            //Send notification to Buyer
            $this->sentOrderSmsToBuyer(auth()->user()->id, $order_id);
            $order = Order::where('id', $order->id)->first();

            //Delete cart data after order success
            Cart::where('user_id', $user_id)->delete();
          } else {
          }
        }

        $message = $this->mCode->getMessage('order_success');
        $this->response_data = ($order) ?? [];
        $this->response_code = 200;
      } else {
        $this->response_code = 504;
        $message = $this->mCode->getMessage('un_authorized_access');
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ProductController::updateUserCart',
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
   * @param Request $request
   * @return JSON response 
   */
  public function getOrderDetails(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'order_id' => ['required'],
      ]);
      if ($validator->fails()) {
        $message = $this->mCode->getMessage('un_authorized_access');
        $this->errors = $validator->messages();
      } else {
        $user_id = auth()->user()->id;
        $order_id = $request->order_id;
        $order = Order::where('order_id', $order_id)->first();
        $order_items = [];
        $orderProducts = OrderProduct::where('order_id',  $order->id)->get();
        foreach ($orderProducts as $idx => $row) {
          $order_items[] = json_decode($row->product);
        }
        $orderDetail = $order;
        $orderDetail['order_items'] = $order_items;
        $message = $this->mCode->getMessage('order_success');
        $this->response_data = $order;
        $this->response_code = 200;
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ProductController::getOrderDetails',
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
