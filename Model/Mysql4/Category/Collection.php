<?php
/**
 * Collection.php
 *
 * @category Cammino
 * @package  Cammino_Googlemerchant
 * @author   Cammino Digital <suporte@cammino.com.br>
 * @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link     https://github.com/cammino/magento-googlemerchant
 */

class Cammino_Googlemerchant_Model_Mysql4_Category_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
    * Function responsible for init google merchant category
    *
    * @return null
    */
    public function _construct()
    {
        parent::_construct();
        $this->_init('googlemerchant/category');
    }

    /**
    * Function responsible for return object with 'title'
    *
    * @return object
    */
    public function toOptionArray()
    {
        return $this->_toOptionArray('title', 'title');
    }
}