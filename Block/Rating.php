<?php
/**
* Rating.php
*
* @category Cammino
* @package  Cammino_Googlemerchant
* @author   Cammino Digital <suporte@cammino.com.br>
* @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link     https://github.com/cammino/magento-googlemerchant
*/

class Cammino_Googlemerchant_Block_Rating extends Mage_Core_Block_Template
{

    /**
    * Function responsible check order id and add script in html
    *
    * @return null
    */
    protected function _toHtml()
    {

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
                "email": "' .  $order->getBillingAddress()->getEmail() . '",
                "delivery_country":"' . Mage::getStoreConfig('catalog/googlemerchant/deliverycountry') .  '",
                "estimated_delivery_date": "' . date('Y-m-d', strtotime("+" . Mage::getStoreConfig('catalog/googlemerchant/deliverydays') . " days")) . '",
                "opt_in_style": "' . Mage::getStoreConfig('catalog/googlemerchant/optinstyle') . '"
              });
          });
          }
		  </script>';
    }
}