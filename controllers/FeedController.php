<?php
/**
* FeedController.php
*
* @category Cammino
* @package  Cammino_Googlemerchant
* @author   Cammino Digital <suporte@cammino.com.br>
* @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link     https://github.com/cammino/magento-googlemerchant
*/

class Cammino_Googlemerchant_FeedController extends Mage_Core_Controller_Front_Action
{
    /**
    * Function responsible controller index action
    *
    * @return null
    */
    public function indexAction()
    {
        $feed = Mage::getModel('googlemerchant/feed');
        $xml = $feed->getXml();
        echo $xml;
    }

}