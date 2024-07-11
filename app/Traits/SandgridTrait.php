<?php

namespace App\Traits;

use stdClass;

/**
 * this trait is used to sync the data with the sandgrid mentian the list of the user
 */
trait SandgridTrait
{
	/**
	 * this function is used to add the user to the sandgrid
	 *
	 * @param object $user_request
	 * @return void
	 */
	public function add_user_to_sandgrid($user_request,$sandgrid_list_category_token)
	{
		// get the request data 
		$data = $this->get_user_data($user_request,$sandgrid_list_category_token);
		// set the api token
		$api_token = config('sandgrid.sandgrid_api_key');
		// sandgrid url
		$sandgrid_url = "https://api.sendgrid.com/v3/marketing/contacts";
		// initialize the curl
		$curl = curl_init();
		// set the curl for the sandgrid
		curl_setopt_array($curl, array(
			CURLOPT_URL => $sandgrid_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_CUSTOMREQUEST => "PUT",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array(
				"content-type: application/json",
				"authorization: Bearer $api_token"
			),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			return false;
		} else {
			return true;
		}
	}

/**
 * this function is used to create the user data
 *
 * @param object $user_request
 * @param string $sandgrid_list_category_token
 * @return array $data
 */
	private function get_user_data($user_request,$sandgrid_list_category_token)
	{
		$data = array(
			'list_ids' => [$sandgrid_list_category_token],
			'contacts' =>
			array(
				array(
					"address_line_1" => $user_request->full_address ?? "",
					"address_line_2" => "",
					"alternate_emails" => array(),
					"city" => $user_request->city ?? "",
					"country" => $user_request->country ?? "",
					// email is required
					"email" => $user_request->email,
					"first_name" => $user_request->user_name,
					"last_name" => "",
					"postal_code" => $user_request->postal_code ?? "",
					"state_province_region" => "",
					"custom_fields" => new stdClass()
				)
			),
		);
		return $data;
	}
}
