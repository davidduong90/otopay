<?php
class Magepow_Ottopay_Helper_Data extends Mage_Core_Helper_Abstract
{
  function getPaymentGatewayUrl() 
  {
    return Mage::getUrl('custompaymentmethod/payment/gateway', array('_secure' => false));
  }

  public function getConfig(){
    return Mage::getStoreConfig('payment/ottopay');
  }
}