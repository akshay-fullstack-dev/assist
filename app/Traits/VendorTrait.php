<?php

namespace App\Traits;

use App\Booking;
use App\Transaction;
use Auth;
use Illuminate\Support\Facades\Request;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait VendorTrait
{
	/**
	 * get the selected month data of current year
	 *
	 * @param [type] $user_id
	 * @param [type] $month
	 * @return void
	 */
	private function get_selected_month_data_of_current_year($user_id, $month)
	{
		$data = [];
		$data['totalEarning'] = "0";
		$data['adminCommision'] = "0";

		$current_year = date('Y');
		$bookings = Booking::whereNotIn('status_id', $this->status)->where('vender_id', $user_id)->whereMonth('booking_date', $month)->whereYear('booking_date', $current_year)->groupBy('booking_date')->get();

		$dates = array_column($bookings->toArray(), 'created_at');
		$keys = array();
		foreach ($dates as $date) {
			$keys[] = date('Y-m-d', strtotime($date));
		}
		$keys = array_unique($keys);
		$data = array();
		$keys = array_values($keys);

		foreach ($keys as $k => $val) {
			$booking = Booking::select('id')->whereNotIn('status_id', $this->status)
				->where('created_at', 'like', '%' . $val . '%')
				->get()->toArray();
			$amount = '';

			if (count($booking) > 0) {
				$ids = array_column($booking, 'id');
				$data[$val]['totalEarning'] = sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('vender_amount'));
				$data[$val]['adminCommision'] = sprintf('%0.2f',  Transaction::whereIn('booking_id', $ids)->sum('admin_amount'));
				$data[$val]['val'] = $val;
				$data[$val]['completedJobs'] = Transaction::whereIn('booking_id', $ids)->get()->count();
				$data[$val]['totalRefunds'] = Booking::filterByMonth; // it should be change wen we implement the refund functionality
			}
		}
		$response = array();
		$response = ['data' => array_values($data), 'filter_by' => Booking::filterByMonth];
		return  $response;
	}

	/**
	 * In this function return the selected month year data
	 *
	 * @param string $month
	 * @param string $year
	 * @return array $response
	 */
	private function get_selected_month_year_data($user_id, $month, $year)
	{
		$bookings = Booking::whereNotIn('status_id', $this->status)->where('vender_id', $user_id)->whereMonth('booking_date', $month)->whereYear('booking_date', $year)->groupBy('booking_date')->get();

		$dates = array_column($bookings->toArray(), 'created_at');
		$keys = array();
		foreach ($dates as $date) {
			$keys[] = date('Y-m-d', strtotime($date));
		}
		$keys = array_unique($keys);
		$keys = array_values($keys);

		$data = array();
		foreach ($keys as $k => $val) {
			$booking = Booking::select('id')->whereNotIn('status_id', $this->status)
				->where('created_at', 'like', '%' . $val . '%')
				->get()->toArray();
			$amount = '';

			if (count($booking) > 0) {
				$ids = array_column($booking, 'id');
				$data[$val]['totalEarning'] =  sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('vender_amount'));
				$data[$val]['adminCommision'] =  sprintf('%0.2f', Transaction::whereIn('booking_id', $ids)->sum('admin_amount'));
				$data[$val]['completedJobs'] = Transaction::whereIn('booking_id', $ids)->get()->count();
				$data[$val]['val'] = $val;
				$data[$val]['totalRefunds'] = "0"; // it should be change wen we implement the refund functionality
			}
		}
		$response = array();
		$response = ['data' => array_values($data), 'filter_by' => Booking::filterByMonth];
		return  $response;
	}

	/**
	 * this function is used to get the selected year data with whole month
	 *
	 * @param string $user_id
	 * @param string $year
	 * @return array $response
	 */
	private function get_selected_year_data($user_id, $year)
	{
		$data = array();
		for ($month = 1; $month <= 12; $month++) {
			$bookings = Booking::where('vender_id', $user_id)
				->whereNotIn('status_id', $this->status)
				->whereMonth('booking_date', $month)
				->whereYear('booking_date', $year)
				->get();

			$booking_ids = array_column($bookings->toArray(), 'id');
			if (count($bookings) > 0) {
				$data[$month]['totalEarning'] =  sprintf('%0.2f', Transaction::whereIn('booking_id', $booking_ids)->sum('vender_amount'));
				$data[$month]['adminCommision'] =  sprintf('%0.2f', Transaction::whereIn('booking_id', $booking_ids)->sum('admin_amount'));
				$data[$month]['totalRefunds'] = "0"; // it should be change wen we implement the refund functionality
				$data[$month]['completedJobs'] = Transaction::whereIn('booking_id', $booking_ids)->get()->count();
				$data[$month]['val'] = (string) $month; // value of month which we are sending the data
			}
		}
		$response = ['data' => array_values($data), 'filter_by' => Booking::filterByYear];
		return  $response;
	}

	/**
	 * this function is used to return the response of today data
	 *
	 * @param [type] $user_id
	 * @return void
	 */
	private function get_today_data($user_id)
	{
		$today_date = Carbon::now()->subDay()->format('Y-m-d');
		$data = array();
		$bookings = Booking::where('vender_id', $user_id)
			->where('booking_date', 'like', '%' . $today_date . '%')
			->get();
		if ($bookings->count() > 0) {
			$booking_ids = array_column($bookings->toArray(), 'id');
			$data['totalEarning'] =  sprintf('%0.2f', Transaction::whereIn('booking_id', $booking_ids)->sum('vender_amount'));
			$data['adminCommision'] =  sprintf('%0.2f', Transaction::whereIn('booking_id', $booking_ids)->sum('admin_amount'));
			$data['totalRefunds'] = "0"; // it should be change wen we implement the refund functionality
			$data['completedJobs'] = Transaction::whereIn('booking_id', $booking_ids)->get()->count();
			$data['val'] = (string) Carbon::now()->format('Y-m-d'); // value of month which we are sending the data
		}
		if (count($data) > 0) {
			$data =	array($data);
		}
		return  ['data' => $data, 'filter_by' => Booking::filterByDay];
	}


	/**
	 * send current year data by default if non of filter applied
	 *
	 * @param [type] $user_id
	 * @return void
	 */
	private function send_current_year_data($user_id)
	{
		$data = [];
		$current_year = date('Y');
		$data = array();
		for ($month = 1; $month <= 12; $month++) {
			$bookings = Booking::where('vender_id', $user_id)
				->whereNotIn('status_id', $this->status)
				->whereMonth('booking_date', $month)
				->whereYear('booking_date', $current_year)
				->get();

			$booking_ids = array_column($bookings->toArray(), 'id');
			if (count($bookings) > 0) {
				$data[$month]['totalEarning'] =  sprintf('%0.2f', Transaction::whereIn('booking_id', $booking_ids)->sum('vender_amount'));
				$data[$month]['adminCommision'] =  sprintf('%0.2f', Transaction::whereIn('booking_id', $booking_ids)->sum('admin_amount'));
				$data[$month]['totalRefunds'] = "0"; // it should be change wen we implement the refund functionality
				$data[$month]['completedJobs'] = Transaction::whereIn('booking_id', $booking_ids)->get()->count();
				$data[$month]['val'] = (string) $month; // value of month which we are sending the data
			}
		}
		$response = array();
		$response = ['data' => array_values($data), 'filter_by' => Booking::filterByYear];
		return  $response;
	}

	/**
	 * this function is used to vendor this week report data
	 *
	 * @param string $user_id
	 * @return array $response
	 */
	public function get_weekly_vendor_report($user_id)
	{
		$data = array();
		$current_date = Carbon::now();
		$seven_day_back_date = Carbon::now()->subWeek(1);
		$days_in_week = 7;
		for ($week_day = 0; $week_day < $days_in_week; $week_day++) {
			$bookings = Booking::where('vender_id', $user_id)
				->whereNotIn('status_id', $this->status)
				->whereRaw("WEEKDAY(booking_date) = $week_day")
				->where('booking_date', '>=', $seven_day_back_date)
				->where('booking_date', '<=', $current_date)
				->get();
			// if bookings found for that day 
			if ($bookings->count()) {
				// add one day to the day index because mysql start week day numbering form 0 and that is monday
				$day_index = $week_day + 1;
				$booking_ids = array_column($bookings->toArray(), 'id');
				$data[$day_index]['totalEarning'] =  sprintf('%0.2f', Transaction::whereIn('booking_id', $booking_ids)->sum('vender_amount'));
				$data[$day_index]['adminCommision'] =  sprintf('%0.2f', Transaction::whereIn('booking_id', $booking_ids)->sum('admin_amount'));
				$data[$day_index]['totalRefunds'] = "0"; // it should be change wen we implement the refund functionality
				$data[$day_index]['completedJobs'] = Transaction::whereIn('booking_id', $booking_ids)->get()->count();
				$data[$day_index]['val'] = (string) date('Y-m-d', strtotime(Carbon::now()->addDays($week_day))); // get the date of the day which we are sending the data

			}
		}
		$response = array();
		$response = ['data' => array_values($data), 'filter_by' => Booking::filterByWeek];
		return  $response;
	}
}
