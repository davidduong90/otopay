<?php

class Magepow_Ottopay_Block_Ottopay extends Mage_Core_Block_Template
{

    public function getRedirecturl(){
        $magepow_helper = Mage::helper('magepow_ottopay');
        $config = $magepow_helper->getConfig();
        $active = $config['active'];
        $merchantname = $config['merchantname'];
        $hostname = $config['request_payment'];
        
        $order = new Mage_Sales_Model_Order();
        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        
        $order->loadByIncrementId($orderId);

        $customerDetails = array();
        $customerDetails['email'] = $order->getData('customer_email');
        $customerDetails['firstName'] = $order->getData('customer_firstname');
        $customerDetails['lastName'] = $order->getData('customer_lastname');
        $customerDetails['phone'] = '';

        $transactionDetails = array();
        $transactionDetails['amount'] = ceil($order->getData('grand_total'));
        $transactionDetails['currency'] = $order->getData('base_currency_code');
        $transactionDetails['merchantName'] = $merchantname;
        $transactionDetails['orderId'] = $orderId;
        $transactionDetails['promoCode'] = '';
        $transactionDetails['vaOrderId'] = '';
        $transactionDetails['vabca'] = '';
        $transactionDetails['valain'] = '';
        $transactionDetails['vamandiri'] = '';

        $data_origin = array();
        $data_origin['customerDetails'] = $customerDetails;
        $data_origin['transactionDetails'] = $transactionDetails;

        $data_origin = json_encode($data_origin);

        $data = strtolower($data_origin);
        $data = preg_replace('/\s+/', '', $data);
        $data = str_replace('"', '', $data);
        $data = str_replace('@', '', $data);

        $time_stamp = time();

        $api_key = $config['apikey'];

        $v = $data.'&'.$time_stamp.'&'.$api_key;

        /*get signature sha512*/
        $signature = hash_hmac('sha512', $v, $api_key);

        $marchant_id = $config['merchantid'];

        $base_string = base64_encode($marchant_id); //for base64 encoding

        $authorization = 'Basic '.$base_string;

        /*get data from API*/
        $curl = curl_init();

        $data_string = $data;

        $curl = curl_init($hostname);

        $fp = fopen(BP . '/var/log/ottopay.log', 'w');

        $length = strlen($data_origin);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_origin);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_STDERR, $fp);
        // curl_setopt($curl, CURLOPT_PORT, 8955);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . $length,
                'Host: secure.ottopay.id',
                'Signature: ' . $signature,
                'Timestamp: ' . $time_stamp,
                'Authorization: ' . $authorization
                )
            );
        $resp = curl_exec($curl);

        // Not found and bugs
        if(!curl_errno($curl))
        {
            $info = curl_getinfo($curl);
            if ($info['http_code'] == 200){

                $resp_json = json_decode( $resp );
                
                if(isset($resp_json->responseData)){
                    $response_data = $resp_json->responseData;
                    $statusCode = $response_data->statusCode;
                    $status_message = $response_data->statusMessage;
                    $order_id = $response_data->orderId;
                    $end_point = $response_data->endpointUrl;
                    header("Location:  $end_point");
                    exit;
                }else{
                    if($resp_json->responseCode == "05"){
                        $responseCode = $resp_json->responseCode;
                        $responseDesc = $resp_json->responseDesc;
                        echo "bug: $responseDesc";
                    }
                }
            }
        }
        else
        {
            echo curl_error($curl);
        }

        curl_close($curl);
   }

    public function getSuccess($orderId){
        $magepow_helper = Mage::helper('magepow_ottopay');
        $config = $magepow_helper->getConfig();

        $active = $config['active'];

        $hostname = $config['transaction_status'];
        
        $param = array();
        $param['trxRef'] = $orderId;
        $data_origin = json_encode($param);
        
        $data = strtolower($data_origin);
        $data = preg_replace('/\s+/', '', $data);
        $data = str_replace('"', '', $data);
        $data = str_replace('@', '', $data);
        
        $time_stamp = time();
        
        $api_key = $config['apikey'];
        
        $v = $data.'&'.$time_stamp.'&'.$api_key;
        
        /*get signature sha512*/
        $signature = hash_hmac('sha512', $v, $api_key);
        
        $marchant_id = $config['merchantid'];
        
        $base_string = base64_encode($marchant_id); //for base64 encoding
        
        $authorization = 'Basic '.$base_string;
        
        /*get data from API*/
        $curl = curl_init();
        
        $data_string = $data;
        
        
        $curl = curl_init($hostname);
        
        $fp = fopen(BP . '/var/log/ottopay.log', 'w');
        
        $length = strlen($data_origin);
        
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_origin);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_STDERR, $fp);
        // curl_setopt($curl, CURLOPT_PORT, 8902);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . $length,
                'Host: secure.ottopay.id',
                'Signature: ' . $signature,
                'Timestamp: ' . $time_stamp,
                'Authorization: ' . $authorization
                )
            );
        $resp = curl_exec($curl);
        
        // Not found and bugs
        if(!curl_errno($curl))
        {
            $info = curl_getinfo($curl);
            if ($info['http_code'] == 200){
        
                $resp_json = json_decode( $resp );
                
                
                if(isset($resp_json->responseCode)){
                    if($resp_json->responseCode == "00"){
                        /*success order*/
                        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

                        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                        $invoice->register();
                        $invoice->getOrder()->setIsInProcess(true);
                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());
                        $transactionSave->save();

                        
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Payment Success.');
                        $order->save();
                        
                        Mage::getSingleton('checkout/session')->unsQuoteId();
                        $url_success = Mage::getUrl('checkout/onepage/success', array('_secure' => false));

                        header("Location:  $url_success");
                        exit;
                    }else{
                        /*cancel order*/
                        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
                        $order->cancel();
                        $order->save();

                        $url_error = Mage::getUrl('checkout/onepage/error', array('_secure' => false));

                        header("Location:  $url_error");
                        exit;
                    }
                }else{
                    if($resp_json->responseCode == "05"){
                        /*cancel order*/
                        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
                        $order->cancel();
                        $order->save();

                        $url_error = Mage::getUrl('checkout/onepage/error', array('_secure' => false));

                        header("Location:  $url_error");
                        exit;
                    }
                }
            }
        }
        else{
            /*cancel order*/
            $url_error = Mage::getUrl('checkout/onepage/error', array('_secure' => false));

            header("Location:  $url_error");
            exit;
        }
        
        curl_close($curl);
    }

}
