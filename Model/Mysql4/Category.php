<?php
/**
 * Category.php
 *
 * @category Cammino
 * @package  Cammino_Googlemerchant
 * @author   Cammino Digital <suporte@cammino.com.br>
 * @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link     https://github.com/cammino/magento-googlemerchant
 */

class Cammino_Googlemerchant_Model_Mysql4_Category extends Mage_Core_Model_Mysql4_Abstract
{
    /**
    * Function responsible for init google merchant category with 'category_id'
    *
    * @return null
    */
    public function _construct()
    {
        $this->_init('googlemerchant/category', 'category_id');
    }
}