<?php
class Cammino_Googlemerchant_Block_Rating extends Mage_Core_Block_Template
{
    protected function _toHtml() {

		$orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		return '<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
		<script>

		  window.renderOptIn = function() { 
		    window.gapi.load("surveyoptin", function() {
		      window.gapi.surveyoptin.render(
		        {
		          "merchant_id":' .  Mage::getStoreConfig("catalog/googlemerchant/merchantid") . ',
		          "order_id": "' .  $orderId . '",
		          "email": "' .  $order->getShippingAddress()->getEmail() . '",
		          "delivery_country":"' . Mage::getStoreConfig('catalog/googlemerchant/deliverycountry') .  '",
		          "estimated_delivery_date": "' . date('Y-m-d', strtotime("+" . Mage::getStoreConfig('catalog/googlemerchant/deliverydays') . " days")) . '",
		          "opt_in_style": "' . Mage::getStoreConfig('catalog/googlemerchant/optinstyle') . '"
		        }); 
		     });
		  }
		</script>';
    }
}