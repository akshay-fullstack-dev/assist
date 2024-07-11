<?php

namespace Intersoft\Stripe\Http\Services;

use Intersoft\Stripe\Http\Models\Stripe_payment_record;
use \Stripe\Transfer;
use \Stripe\StripeClient;

class StripePaymentProcess
{
	private $stripe_secret_key;
	// set stripe key to the package
	public function __construct($stripe_secret_key)
	{
		\Stripe\Stripe::setApiKey($stripe_secret_key);
		$this->$stripe_secret_key = $stripe_secret_key;
	}
	/**
	 * make a indent payment for the stripe
	 */
	public function IntentPayment($stripe_total_pay, $card_id, $currency_code, $orderId, $user_stripe_id)
	{
		$paymentIntent = \Stripe\PaymentIntent::create([
			'amount' => $stripe_total_pay * 100,
			'currency' => $currency_code,
			'payment_method_types' => ['card'],
			'source' => $card_id,
			'transfer_group' => "{$orderId}",
			'customer' => $user_stripe_id,
			'confirmation_method' => 'automatic',
			'confirm' => true
		]);
		if ($paymentIntent) {
			$this->save_history($paymentIntent, $currency_code);
			// return the stripe response
			return json_encode($paymentIntent);
		}
		return false;
	}


	private function save_history($paymentIntent, $currency_code)
	{
		// save the payment history of the stripe
		$stripe_record = new Stripe_payment_record;
		$stripe_record->payment_intent_id = $paymentIntent->id;
		$stripe_record->charge_id = $paymentIntent->charges->data[0]->id;
		$stripe_record->currency_code = $currency_code;
		$stripe_record->user_stripe_id = $paymentIntent->customer;
		$stripe_record->card_id = $paymentIntent->source;
		$stripe_record->order_id = $paymentIntent->transfer_group;
		return $stripe_record->save();
	}

	/**
	 * transfer payment to the another user
	 */
	public function transferCharges($transaction_data, $booking)
	{
		// get the stripe booking history
		$stripe_booking_history = Stripe_payment_record::where('order_id', $booking->id)->latest()->first();
		// Create a Transfer to a connected account (later):
		if ($stripe_booking_history) {
			$transfer = Transfer::create([
				'amount' => $transaction_data->vender_amount * 100,
				'currency' => $stripe_booking_history->currency_code,
				'destination' => $booking->vender->stripe_id,
				'transfer_group' => $booking->id,
			]);
			if ($transfer)
				return $transfer;
		}

		return false;
	}

	// refund the payment 
	public function refund_payment($booking_id)
	{
		$stripe_payment = Stripe_payment_record::where('order_id', $booking_id)->first();
		$stripe = new StripeClient($this->stripe_secret_key);
		$stripe->refunds->create([
			'charge' => $$stripe_payment->charge_id,
		]);
		if ($stripe)
			return true;
		return false;
	}
}
