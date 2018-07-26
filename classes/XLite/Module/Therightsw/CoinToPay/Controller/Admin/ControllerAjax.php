<?php
/**
 * Created by PhpStorm.
 * User: TRS-LAPTOP-05
 * Date: 7/18/2018
 * Time: 7:09 PM
 */

namespace XLite\Module\Therightsw\CoinToPay\Controller\Admin;


class ControllerAjax extends \XLite\Controller\Admin\AAdmin
{
    public function doNoAction()
    {
        $merchant_id = $_REQUEST['merchant_id'];
        $url = "https://app.cointopay.com/CloneMasterTransaction?MerchantID=".$merchant_id."&output=json";
        $curl = curl_init($url);
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 0
        ));
        $result = curl_exec($curl);
        if($result){
            $result = json_decode($result);
            $i=0;
            while($i<sizeof($result)){
                $response[$result[$i+1]] = $result[$i];
                $i = $i+2;
            }
            curl_close($curl);
        }else{
            $response = ['error' => 1];
        }
        echo json_encode($response, true);exit;
    }

}