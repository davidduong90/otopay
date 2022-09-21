<?php
class Magepow_Ottopay_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'custompaymentmethod';
  protected $_formBlockType = 'custompaymentmethod/form_custompaymentmethod';
  protected $_infoBlockType = 'custompaymentmethod/info_custompaymentmethod';
 
 
  public function getOrderPlaceRedirectUrl()
  {
    return Mage::getUrl('ottopay/payment/redirect', array('_secure' => false));
  }
}