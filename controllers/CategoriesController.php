<?php
/**
* CategoriesController.php
*
* @category Cammino
* @package  Cammino_Googlemerchant
* @author   Cammino Digital <suporte@cammino.com.br>
* @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link     https://github.com/cammino/magento-googlemerchant
*/

class Cammino_Googlemerchant_CategoriesController extends Mage_Core_Controller_Front_Action
{
    /**
    * Action to proxy Google Categories
    *
    * @return null
    */
    public function indexAction()
    {
        $response = file_get_contents('https://www.google.com/basepages/producttype/taxonomy-with-ids.pt-BR.txt');
        echo $response;
    }

}