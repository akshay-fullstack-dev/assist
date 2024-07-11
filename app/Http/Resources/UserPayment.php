<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserPayment extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array(
            'startDate' => date("Y-m-d H:i:s", substr($this['startTimeMillis'], 0, 10)),
            'expiryDate' => date("Y-m-d H:i:s", substr($this['expiryTimeMillis'], 0, 10)),
            'autoRenewing' => $this['autoRenewing'],
            'priceCurrencyCode' => $this['priceCurrencyCode'],
            'priceAmount' => ($this['priceAmountMicros']) * pow(10,-6),
            'countryCode' => $this['priceCurrencyCode'],
            'developerPayload' => $this['developerPayload'] ? $this['developerPayload'] : "",
            'orderId' => $this['orderId'],
            'purchaseType' => $this['purchaseType'],
            'acknowledgementState' => $this['acknowledgementState'],
        );
    }
}
