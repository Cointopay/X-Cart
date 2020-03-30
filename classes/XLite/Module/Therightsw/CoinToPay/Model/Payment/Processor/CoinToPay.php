<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present The right software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\Therightsw\CoinToPay\Model\Payment\Processor;

/**
 * Cointopay payment processor
 */
class CoinToPay extends \XLite\Model\Payment\Base\WebBased
{
    /**
     * production form url
     */
    const FORM_URL_PRODUCTION = 'https://app.cointopay.com/MerchantAPI';
    const FORM_METHOD_GET  = 'get';

    public function isTestMode(\XLite\Model\Payment\Method $method)
    {
        return false;
    }

    /**
     * Process return
     *
     * @param \XLite\Model\Payment\Transaction $transaction Return-owner transaction
     *
     * @return void
     */
    public function processReturn(\XLite\Model\Payment\Transaction $transaction)
    {
        parent::processReturn($transaction);

        $request = \XLite\Core\Request::getInstance();
        $status = $request->status;
        $not_enough = isset($request->notenough) ? $request->notenough : 0;
        $response_data['TransactionID'] = $request->TransactionID;
        $response_data['ConfirmCode'] = $request->ConfirmCode;
        $response_data['Status'] = $status;
		$api_key = $this->getSetting('api_key');
		$transactionData = $this->getTransactiondetail($response_data);
		if(200 !== $transactionData['status_code']){
			$this->transaction->setNote($transactionData['message']);
            \XLite\Core\TopMessage::addWarning('Data tempered ! '.$transactionData['message']);
		}
		$value_data = "MerchantID=" . $transactionData['data']['MerchantID'] . "&AltCoinID=" . $transactionData['data']['AltCoinID'] . "&TransactionID=" . $request->TransactionID . "&coinAddress=" . $transactionData['data']['coinAddress'] . "&CustomerReferenceNr=" . 
		$request->CustomerReferenceNr . "&SecurityCode=" . $transactionData['data']['SecurityCode'] . "&inputCurrency=" . $transactionData['data']['inputCurrency'];
		$ConfirmCode = $this->fn_cointopay_calculateRFC2104HMAC($api_key, $value_data);
		if($ConfirmCode !== $request->ConfirmCode){
			$this->transaction->setNote('Data mismatch! Data doesn\'t match with Cointopay.');
            \XLite\Core\TopMessage::addWarning('Data tempered ! Data mismatch! Data doesn\'t match with Cointopay.');
		}
        $validation = $this->validateResponse($response_data);
        if(!$validation) {
            $this->transaction->setNote('Credentials do not match to Cointopay');
            \XLite\Core\TopMessage::addWarning('Data tempered ! Credentials do not match to Cointopay');

        }elseif ($request->cancel) {
            $this->setDetail(
                'status',
                'Customer has canceled checkout before completing their payments',
                'Status'
            );
            $this->transaction->setNote('Customer has canceled checkout before completing their payments');
            $this->transaction->setStatus($transaction::STATUS_CANCELED);

        }elseif ($status == 'paid') {
            $str = 'Payment successfully paid';
            $str .= 'Transaction ID: ' . $request->transaction_id . PHP_EOL;
            $transaction_status = $transaction::STATUS_SUCCESS;
            if($not_enough) {
                $str = 'Payment partially paid';
                $str .= 'Transaction ID: ' . $request->transaction_id . PHP_EOL;
                $transaction_status = $transaction::STATUS_PENDING;
                \XLite\Core\TopMessage::addWarning('Your payment was partially paid (Payment was not fully paid to Cointopay)');

            }
            $this->setDetail('payment_details',$str, 'Status');
            $this->transaction->setNote($str);
            $this->transaction->setStatus($transaction_status);

        } else {
            //order pending
            $str = 'Payment Failed';
            $this->setDetail('payment_details', $str, 'Status');
            $this->transaction->setNote($str);
            $this->transaction->setStatus($transaction::STATUS_FAILED);
        }
    }

    /**
     * Process callback
     *
     * @param \XLite\Model\Payment\Transaction $transaction Callback-owner transaction
     *
     * @return void
     */
    public function processCallback(\XLite\Model\Payment\Transaction $transaction)
    {
        parent::processCallback($transaction);

        $request = \XLite\Core\Request::getInstance();

        static::log(
            array('request' => $request->getData())
        );

        $this->saveDataFromRequest();

        $status = $request->status;
        $not_enough = (bool)$request->notenough;
        $response_data['TransactionID'] = $request->TransactionID;
        $response_data['ConfirmCode'] = $request->ConfirmCode;
        $response_data['Status'] = $status;
		$api_key = $this->getSetting('api_key');
		$transactionData = $this->getTransactiondetail($response_data);
		if(200 !== $transactionData['status_code']){
			$this->transaction->setNote($transactionData['message']);
            \XLite\Core\TopMessage::addWarning('Data tempered ! '.$transactionData['message']);
		}
		$value_data = "MerchantID=" . $transactionData['data']['MerchantID'] . "&AltCoinID=" . $transactionData['data']['AltCoinID'] . "&TransactionID=" . $request->TransactionID . "&coinAddress=" . $transactionData['data']['coinAddress'] . "&CustomerReferenceNr=" . 
		$request->CustomerReferenceNr . "&SecurityCode=" . $transactionData['data']['SecurityCode'] . "&inputCurrency=" . $transactionData['data']['inputCurrency'];
		$ConfirmCode = $this->fn_cointopay_calculateRFC2104HMAC($api_key, $value_data);
		if($ConfirmCode !== $request->ConfirmCode){
			$this->transaction->setNote('Data mismatch! Data doesn\'t match with Cointopay.');
            \XLite\Core\TopMessage::addWarning('Data tempered ! Data mismatch! Data doesn\'t match with Cointopay.');
		}
        $validation = $this->validateResponse($response_data);
        if(!$validation) {
            $this->transaction->setNote('Credentials do not match to Cointopay');
            \XLite\Core\TopMessage::addWarning('Data tempered ! Credentials do not match to Cointopay');

        }
		elseif($validation->Status !== $status)
		{
			$this->transaction->setNote('We have detected different order status. Your order has been halted.');
            \XLite\Core\TopMessage::addWarning('Data tempered ! We have detected different order status. Your order has been halted.');
		}elseif ($status == 'paid') {
            $str = 'Payment successfully paid';
            $str .= 'Transaction ID: ' . $request->transaction_id . PHP_EOL;
            $transaction_status = $transaction::STATUS_SUCCESS;
            if($not_enough) {
                $str = 'Payment partially paid';
                $str .= 'Transaction ID: ' . $request->transaction_id . PHP_EOL;
                $transaction_status = $transaction::STATUS_PENDING;
            }
            $this->transaction->setDetail('payment_details',$str);
            $this->transaction->setStatus($transaction_status);

        } else {
            //order pending
            $str = 'Payment Failed';
            $this->setDetail('payment_details', $str);
            $this->transaction->setStatus($transaction::STATUS_FAILED);
        }
        static::log(
            array('tx' => $this->transaction)
        );

    }

    /**
     * @param $response
     * @return bool
     */
    public function validateResponse($response) {
        $validate = true;
        $merchant_id = $this->getSetting('merchantId');
        $transaction_id = $response['TransactionID'];
        $confirm_code = $response['ConfirmCode'];
        $url = "https://app.cointopay.com/v2REAPI?MerchantID=$merchant_id&Call=QA&APIKey=_&output=json&TransactionID=$transaction_id&ConfirmCode=$confirm_code";
        $curl = curl_init($url);
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 0
        ));
        $result = curl_exec($curl);
        $result = json_decode($result, true);
        if(!$result || !is_array($result)) {
            $validate == false;
        }else{
            if($response['Status'] != $result['Status']) {
                $validate = false;
            }
        }
        return $validate;
    }
    /**
     * Logging the data under Cointopay
     * Available if developer_mode is on in the config file
     *
     * @param mixed $data Log data
     *
     * @return void
     */
    protected static function log($data)
    {
        if (LC_DEVELOPER_MODE) {
            \XLite\Logger::logCustom('Cointopay', $data);
        }
    }

    /**
     * Get settings widget or template
     *
     * @return string Widget class name or template path
     */
    public function getSettingsWidget()
    {
        return 'modules/Therightsw/CoinToPay/config.twig';
    }
    /**
     * @return string
     */
    public function getFormMethod()
    {
        return static::FORM_METHOD_GET;
    }
    /**
     * Check - payment method is configured or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isConfigured(\XLite\Model\Payment\Method $method)
    {

        return parent::isConfigured($method)
            && $method->getSetting('merchantId')
            && $method->getSetting('security_code')
            && $method->getSetting('coin_id');
    }

    /**
     * Get payment method admin zone icon URL
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getAdminIconURL(\XLite\Model\Payment\Method $method)
    {
        return true;
    }

    /**
     * Returns the list of settings available for this payment processor
     *
     * @return array
     */
    public function getAvailableSettings()
    {
        return array(
            'merchantId',
            'security_code',
            'coin_id'
        );
    }

    /**
     * Get redirect form URL
     *
     * @return string
     */
    protected function getFormURL()
    {
        return static::FORM_URL_PRODUCTION;
    }


    /**
     * Get redirect form fields list
     *
     * @return array
     */
    protected function getFormFields()
    {
        $coin_id = intval($this->getSetting('coin_id'));
        $fields = array(
            'Checkout' => 'true',
            'MerchantID' => $this->getSetting('merchantId'),
            'SecurityCode' => $this->getSetting('security_code'),
            'AltCoinID' => $coin_id ? $coin_id : 1,
            'inputCurrency' => $this->getCurrencyCode(),
            'Amount' => $this->getFormattedPrice($this->transaction->getValue()),
            'item_name' => $this->getItemName(),
            'CustomerReferenceNr' => $this->transaction->getOrder()->getOrderNumber() ?: $this->transaction->getOrder()->getOrderId(),
            'want_shipping' => 0,
            'transactionconfirmurl' => $this->getReturnURL('transaction_id', true),
            'transactionfailurl' => $this->getReturnURL('transaction_id', true, true)
        );

        $billingAddress = $this->getProfile()->getBillingAddress();
        if ($billingAddress) {
            $fields += array(
                'first_name' => $billingAddress->getFirstname(),
                'last_name' => $billingAddress->getLastname(),
                'address1' => $billingAddress->getStreet(),
                'city' => $billingAddress->getCity(),
                'state' => $this->getStateField($billingAddress),
                'country' => $this->getCountryField($billingAddress),
                'phone' => $billingAddress->getPhone(),
            );
        }

        static::log(
            array('main_form_fields' => $fields)
        );

        return $fields;
    }

    /**
     * Get currency code
     *
     * @return string
     */
    protected function getCurrencyCode()
    {
        return strtoupper($this->transaction->getCurrency()->getCode());
    }

    /**
     * Return formatted price.
     *
     * @param float $price Price value
     *
     * @return string
     */
    protected function getFormattedPrice($price)
    {
        return sprintf('%.2f', round((double)($price) + 0.00000000001, 2));
    }

    /**
     * Return ITEM NAME for request
     *
     * @return string
     */
    protected function getItemName()
    {
        return $this->getSetting('description') . '(Order #' . $this->getTransactionId() . ')';
    }

    /**
     * Return State field value. If country is US then state code must be used.
     *
     * @param \XLite\Model\Address $address Address
     *
     * @return string
     */
    protected function getStateField(\XLite\Model\Address $address)
    {
        return 'US' === $this->getCountryField($address)
            ? $address->getState()->getCode()
            : $address->getState()->getState();
    }

    /**
     * Return Country field value. if no country defined we should use '' value
     *
     * @param \XLite\Model\Address $address Address model (could be shipping or billing address)
     *
     * @return string
     */
    protected function getCountryField(\XLite\Model\Address $address)
    {
        return $address->getCountry()
            ? $address->getCountry()->getCode3()
            : '';
    }
		
	/**
     * @param $response
     * @return array
    */
	public function getTransactiondetail( $data) {
		$merchant_id = $this->getSetting('merchantId');
		$params = array(
       "authentication:1",
       'cache-control: no-cache',
       );
       $ch = curl_init();
       curl_setopt_array($ch, array(
       CURLOPT_URL => 'https://app.cointopay.com/v2REAPI?',
       //CURLOPT_USERPWD => $this->apikey,
       CURLOPT_POSTFIELDS => 'Call=Transactiondetail&MerchantID='.$merchant_id.'&output=json&ConfirmCode='.$data['ConfirmCode'].'&APIKey=a',
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_SSL_VERIFYPEER => false,
       CURLOPT_HTTPHEADER => $params,
       CURLOPT_USERAGENT => 1,
       CURLOPT_HTTPAUTH => CURLAUTH_BASIC
       )
       );
       $response = curl_exec($ch);
       $results = json_decode($response, true);
       /*if($results->CustomerReferenceNr)
       {
           return $results;
       }*/
       return $results;
       exit();

	}//end getTransactiondetail()
	
	/**
     * @param $key,$response
     * @return string
    */
	public function calculateRFC2104HMAC ($key, $data)
	{
		$s = hash_hmac('sha256', $data, $key, true);

		return strtoupper($this->base64url_encode($s));
	}//end calculateRFC2104HMAC()
	
	/**
     * @param $response
     * @return string
    */
	public function base64url_encode($data) {
		return strtoupper(rtrim(strtr(base64_encode($data), '+/', '-_'), '='));
	}//end base64url_encode()

}
