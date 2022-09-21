<?php
class Magepow_Ottopay_PaymentController extends Mage_Core_Controller_Front_Action 
{
  public function gatewayAction() 
  {
    if ($this->getRequest()->get("orderId"))
    {
      $arr_querystring = array(
        'flag' => 1, 
        'orderId' => $this->getRequest()->get("orderId")
      );
       
      Mage_Core_Controller_Varien_Action::_redirect('ottopay/payment/response', array('_secure' => false, '_query'=> $arr_querystring));
    }
  }
   
  public function redirectAction() 
  {
    $this->loadLayout();
    $block = $this->getLayout()->createBlock('Magepow_Ottopay_Block_Ottopay','custompaymentmethod',array('template' => 'ottopay/redirect.phtml'));
    $this->getLayout()->getBlock('content')->append($block);
    $this->renderLayout();
  }

  public function successAction() 
  {
    $this->loadLayout();
    $block = $this->getLayout()->createBlock('Magepow_Ottopay_Block_Ottopay','custompaymentmethod',array('template' => 'ottopay/success.phtml'));
    $this->getLayout()->getBlock('content')->append($block);
    $this->renderLayout();
  }
 
  public function failedAction() 
  {
    $this->loadLayout();
    $block = $this->getLayout()->createBlock('Magepow_Ottopay_Block_Ottopay','custompaymentmethod',array('template' => 'ottopay/failed.phtml'));
    $this->getLayout()->getBlock('content')->append($block);
    $this->renderLayout();
  }

  public function callbackAction() 
  {
    $this->loadLayout();
    $block = $this->getLayout()->createBlock('Magepow_Ottopay_Block_Ottopay','custompaymentmethod',array('template' => 'ottopay/failed.phtml'));
    $this->getLayout()->getBlock('content')->append($block);
    $this->renderLayout();
  }

  public function responseAction() 
  {
    if ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId")) 
    {
      $orderId = $this->getRequest()->get("orderId");
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
      $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, 'Payment Success.');
      $order->save();
       
      Mage::getSingleton('checkout/session')->unsQuoteId();
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=> false));
    }
    else
    {
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/error', array('_secure'=> false));
    }
  }
}