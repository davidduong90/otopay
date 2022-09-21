<?php
class Magepow_Ottopay_Model_Observer extends Varien_Event_Observer
{
    public function paymentMethodIsActive(Varien_Event_Observer $observer) {
            $event           = $observer->getEvent();
            $method          = $event->getMethodInstance();
            $result          = $event->getResult(); 
        $quote  = $observer->getEvent()->getQuote();
        if($quote){
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            $magepow_helper = Mage::helper('magepow_ottopay');
            $config = $magepow_helper->getConfig();
            $active = $config['active'];
            if(!$active){
                if($method->getCode() == 'custompaymentmethod' ){ // to hide this method
                    $result->isAvailable = false; // false means payment method is disable
                }
            }
        }
    }
    
    public function execute(){
        // $orders = Mage::getModel('sales/order')->getCollection()
        //     ->addFieldToFilter('status', 'pending')
        //     ->addAttributeToSelect('id')
        //     ->addAttributeToSelect('customer_email')
        //     ->addAttributeToSelect('status')
        //     ;
        
        // Mage::log(count($orders),null,'logfile.log',true);
        // foreach ($orders as $order) {
        //     $email = $order->getCustomerEmail();
        //     $message = $order->getId() . ": '" . $order->getStatus() . "', " . $email .  "\n";

        //     Mage::log($message,null,'logfile.log',true);
        // }
        
    }
}