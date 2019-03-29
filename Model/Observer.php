<?php
/**
 * Observer.php
 *
 * @category Cammino
 * @package  Cammino_Googlemerchant
 * @author   Cammino Digital <suporte@cammino.com.br>
 * @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link     https://github.com/cammino/magento-googlemerchant
 */

class Cammino_Googlemerchant_Model_Observer extends Varien_Object
{
    /**
     * Fuction responsible to import categories
     *
     * @param object $observer Magento observer
     *
     * @return null
     */
    public function importCategories(Varien_Event_Observer $observer)
    {
        $category = Mage::getModel('googlemerchant/category');
        $category->importCategories();
    }

    /**
     * Fuction responsible for inject rating blocks
     *
     * @param object $observer Magento observer
     *
     * @return null
     */
    public function injectRatingBlock(Varien_Event_Observer $observer)
    {
        $block = Mage::app()->getFrontController()->getAction()->getLayout()->createBlock("googlemerchant/rating");
        $blockContent = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('content');
        if ($blockContent) {
            $blockContent->append($block);
        }
    }
}