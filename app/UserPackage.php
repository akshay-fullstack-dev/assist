<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
  const activePackage = '1';
  const inActivePackage = '0';
  const androidPlatform = '1';
  const iosPlatform = '0';

  const testEnvironment = 'test';
  const liveEnvironment = 'live';

  protected $table = 'user_packages';
  protected $fillable = ['user_id', 'package_name', 'platform', 'purchase_token', 'transaction_id', 'order_id', 'transaction_receipt', 'product_id',  'developer_payload', 'transaction_date', 'expiry_date', 'number_of_attempt', 'is_verified', 'is_processed'];

  public function users()
  {
    return $this->belongsTo('App\User', 'user_id', 'id');
  }

  public function scopeProVendor($query)
  {
    return $query->where('expiry_date', '>=', now());
  }

  /**
   * this funtion is used to get the transaction reciept from google
   * @param [string] $product_id
   * @param [string] $token
   * @param [string] $access_token
   * @return google_resposne
   */
  public static function GetUserPackageDetailsFromGoogle($purchase_token, $access_token, $product_id = '')
  {
    $google_app_name =  env("GOOGLE_APP_NAME");
    // make the google url for curl
    $google_url = "https://www.googleapis.com/androidpublisher/v3/applications/$google_app_name/purchases/subscriptions/$product_id/tokens/" . $purchase_token;

    $ch1 = curl_init($google_url);
    curl_setopt($ch1, CURLOPT_URL, $google_url);
    curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt(
      $ch1,
      CURLOPT_HTTPHEADER,
      array(
        'Authorization: Bearer ' . $access_token
      )
    );
    $response = curl_exec($ch1);
    $headers_info = curl_getinfo($ch1);
    curl_close($ch1);
    // resposne 200 response in api
    if ($headers_info['http_code'] == '200') {
      $google_response = (array) json_decode($response);
      return $google_response;
    } else {
      return false;
    }
  }
  /**
   * function name :- GetGoogleToken
   * desciption :- this function return the google api token
   * @return access_token
   */
  public static function GetGoogleToken()
  {
    $ch = curl_init('https://accounts.google.com/o/oauth2/token');
    $data_string = array(
      'grant_type' => 'refresh_token',
      'client_id' => env("GOOGLE_CLIENT_ID"),
      'client_secret' =>  env("GOOGLE_CLIENT_SECRET"),
      'redirect_uri' =>  env("GOOGLE_REDIRECT_URL"),
      'refresh_token' => env("GOOGLE_REFRESH_TOKEM")
    );

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $output = curl_exec($ch);
    $headers_info = curl_getinfo($ch);
    curl_close($ch);
    if ($headers_info['http_code'] == '200') {
      $decoded = json_decode($output, TRUE);
      $access_token = $decoded['access_token'];
      if ($access_token) {
        return $access_token;
      }
      return false;
    }
    return false;
  }


  public static function getIosPackageDetails($package_data, $build_mode)
  {
    if ($build_mode == self::testEnvironment) {
      $url = "https://sandbox.itunes.apple.com/verifyReceipt";
    } else {
      $url = "https://buy.itunes.apple.com/verifyReceipt";
    }
    $ch = curl_init($url);
    $data_string = json_encode(array(
      'receipt-data' => $package_data->purchase_token,
      'password' => env('IOS_SHARED_KEY', ' '),
      'exclude-old-transactions' => 1
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($data_string)
    ));
    $output = curl_exec($ch);
    $headers_info = curl_getinfo($ch);
    curl_close($ch);
    $decoded = json_decode($output, TRUE);
    if ($headers_info['http_code'] != '200') {
      return false;
    }
    return $decoded;
  }
}
