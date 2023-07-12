<?php

namespace App\HelperClass;

class MessageCode
{
    protected $postData = array();

    protected $messageCodes = array(

        /**
         * Globel message code
         */
        'exception_error' => 'Sorry something went wrong! Please try again later.',
        'un_authorized_access' => 'You are not authorized to access this API',
        'validation_error' => 'Validation errors in your request.',

        /**
         * Message for Hegan Admin APP
         */
        // Auth Component
        'invalid_email' => 'Please enter valid email address.',
        'invalid_password' => 'Incorrect password you have entered.',
        'invalid_credentials' => 'The provided credentials are incorrect.',

        'login_success' => 'You have successfully logged In.',
        'logout_success' => 'You have successfully logout.',
        'register_validation_failed' => 'Validation errors in your request.',



        // User / Client
        'pending_fetch_success' => 'Pending users list fetched successfully.',
        'pending_fetch_success_no_data' => 'No pending users list found.',

        'rejected_fetch_success' => 'Rejected users list fetched successfully.',
        'rejected_fetch_success_no_data' => 'No Rejected users list found.',

        'all_users_fetch_success' => 'All users list fetched successfully.',
        'all_users_fetch_success' => 'No users list found.',
        'user_fetch_success' => 'User info fetched successfully.',
        'user_update_success' => 'User updated successfully.',

        'mc_fetch_success' => 'Medicine Category fetched successfully.',
        'mc_fetch_success_no_data' => 'No Medicine Category list found.',
        'medicien_category_added_seccess' => 'Medicine Category added successfully.',
        'medicien_category_remove_seccess' => 'Medicine Category deleted successfully.',
        'medicien_category_validation_failed' => 'Validation errors in your request.',
        'medicien_category_active_success' => 'Medicine Category activated successfully.',
        'medicien_category_inactive_success' => 'Medicine Category deactivated successfully.',
        'mc_update_success' => 'Medicine Category updated successfully.',

        'mb_fetch_success' => 'Medicine Brand fetched successfully.',
        'mb_fetch_success_no_data' => 'No Medicine Brand list found.',
        'mb_added_seccess' => 'Medicine Brand added successfully.',
        'mb_remove_seccess' => 'Medicine Brand deleted successfully.',
        'mb_validation_failed' => 'Validation errors in your request.',
        'mb_update_success' => 'Medicine Brand updated successfully.',
        'mb_active_success' => 'Medicine Brand activated successfully.',
        'mb_inactive_success' => 'Medicine Brand deactivated successfully.',
        // -----------------------------------------------------------------------------
        /**
         * For Medicine
         */






        'get_state_success' => 'States fetched successfully.',
        'get_plan_success' => 'Plans fetched successfully.',
        'get_register_type_success' => 'Register type fetched successfully.',
        'register_validation_error' => 'Please check your entered data!',
        'register_success' => 'Thank you for showing interest in our B2B Pharma Marketplace. One of the associates will reach out to you shortly to verify your details.',



        /**
         * Message for Client App Backend (Dashboard)
         */
        // Medicine Conroller
        'medi_fetch_success' => 'Medicine  fetched successfully.',
        'no_medi_list_fount' => 'No Medicines found.',
        'medi_validation_failed' => 'Validation errors in your request.',
        'medi_remove_seccess' => 'Medicine removed successfully.',

        'medi_validation_failed' => 'Validation errors in your request!',
        'medi_add_stock_success' => 'Medicine stock added successfully.',
        'medi_update_stock_success' => 'Medicine stock updated successfully.',
        'medi_cat_fetch_success' => 'Medicine Categories fetched successfully.',
        'no_medi_cat_list_fount' => 'No Medicine Category list found.',
        'medi_brand_fetch_success' => 'Medicine brands fetched successfully.',
        'no_medi_brand_list_fount' => 'No Medicine brands list found.',
        'tax_cat_fetch_success' => 'Tax Category fetched successfully.',
        'no_tax_cat_list_fount' => 'No Tax Category list found.',
        'inventry_upload_success' => 'Your Inventory has been uploaded to the system.',
        'inventry_upload_falid' => 'Your Imported file is corrupted. Kindly check and retry again.',
        'inventry_import_success' => 'Your Inventory has been imported successfully.',
        'inventry_import_falid' => 'There was a problem impporting the data!',
        //Order History 
        'order_txn_id_update_success' =>'Order Transaction Id updated successfully.',
        'order_trans_id_update_validation_failed' =>'Validation errors in your request!',
        'order_detail_fetch_success' =>'Order detail fetched successfully.',
        'no_order_fount' =>'No order found.',
        'order_canceled_success' =>'Your Order has been successfully canceled.',
        'order_status_update_success'=>'Order status has been successfully updated.',
        'order_status_update_validation_failed'=>'Validation errors in your request!',
        'order_update_success'=>'Order has been successfully updated.',
        'order_return_success' =>'Your order return request has been send successfully.',


        /**
         * Message for Client App Frontend 
         */
        // Home Page
        'medi_cat_list_success' => 'Medicine Categories fetched successfully.',
        'no_medi_cat_list_fount' => 'No Active Medicine Category list found.',
        'update_cart_validation_failed' => 'Validation errors in your request.',
        'update_cart_success' => 'Cart updated successfully.',
        'get_cart_success' => 'Cart data fetched successfully.',
        'get_cart_summary_success' => 'Cart summary data fetched successfully.',
        'order_success' => 'Your order has been placed successfully.',

        

        

        


        // Product Page
        'related_pro_success' => 'Related products fetched successfully.',
        'no_related_pro_found' => 'No related products list found.',
        // Send OTP Page
        'send_otp_validation_error' => 'Enter valid mobile number.',
        'send_otp_no_user_error' => 'Provided mobile number does not exists.',
        'send_otp_user_in_active'   => 'Your account is disabled. Please contact to system administrator.',
        'otp_send_success' => 'OTP has been send to your mobile numnber.',
        // Varify Login page
        'check_otp_validation_error' => 'Please enter OTP.',
        'invalid_otp' => 'Entered OTP is incorrect. Please enter correct OTP.',
        'otp_expire' => 'OTP has been expired. Please resend OTP and login again.',
        'user_login_success' => 'You have successfully logged In.',




    );
    public function getMessage($mcode)
    {
        if (array_key_exists($mcode, $this->messageCodes)) {
            return $this->messageCodes[$mcode];
        } else {
            return $mcode;
        }
    }
}
