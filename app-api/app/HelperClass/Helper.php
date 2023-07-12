<?php

namespace App\HelperClass;
use Illuminate\Support\Facades\Http;

use App\Models\ApiExceptions;
use Exception;

class Helper
{

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
        'api_name' => 'Helper::addApiExceptionInDb',
        'errors' => $ex->getMessage(),
      ];
      ApiExceptions::updateOrCreate(['api_name' => $data['api_name']], $data);
    }
  }
  public static function sentSms($mobile_no, $message = '')
  {
    try {
      if ($mobile_no) {
        $apikey = '63eb3f9c56754';
        $sender = 'HeganH';
        $apiURL = 'http://www.mysmsapp.in/api/push.json?' . 'apikey=' . $apikey . '&sender=' . $sender . '&mobileno=' . $mobile_no . '&text=' . urlencode($message);
        $response = Http::get($apiURL);
        if ($response->status() == 200) {
          $data['status'] = $response->status();
          $data['ok'] = $response->ok();
          $data['successful'] = $response->successful();
          $data['failed'] = $response->failed();
          $data['body'] = $response->body();
          $data['object'] = $response->object();
          $data['json'] = $response->json();
          // $data = [
          //   'api_name' => 'Helper::sendSms',
          //   'errors' => $response->body(),
          // ];
          // ApiExceptions::updateOrCreate(['api_name' => $data['api_name']], $data);
          return true;
        } else {
          $data = [
            'api_name' => 'Helper::sendSms',
            'errors' => $response->body(),
          ];
          ApiExceptions::updateOrCreate(['api_name' => $data['api_name']], $data);
          return false;
        }
      } else {
      }
    } catch (\Exception $ex) {
      $data = [
        'api_name' => 'ClientAuthController::sendOtp',
        'errors' => $ex->getMessage(),
      ];
      ApiExceptions::updateOrCreate(['api_name' => $data['api_name']], $data);
    }
  }
}
