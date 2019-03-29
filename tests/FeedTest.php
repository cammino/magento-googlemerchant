<?php


require_once 'app/Mage.php';
Mage::app('admin');
/**
 * FeedTeste.php
 *
 * @category Cammino
 * @package  Cammino_Googlemerchant
 * @author   Cammino Digital <suporte@cammino.com.br>
 * @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link     https://github.com/cammino/magento-googlemerchant
 */

class FeedTest extends PHPUnit_Framework_TestCase
{
    protected $_product;
    protected $_feed;

    /**
    * Function responsible for set up google merchant feed
    *
    * @return null
    */
    protected function setUp()
    {
        $this->_feed = Mage::getModel('googlemerchant/feed');
        $collection = $this->_feed->getProducts()->getFirstItem();
        $this->_product = Mage::getModel('catalog/product')->load($collection->getId());
    }

    /**
    * Function responsible for test product for test exists
    *
    * @return null
    */
    public function testProductForTestsExists()
    {
        $this->assertInternalType("string", $this->_product->getId(), "Não existe um produto de teste");
    }

    /**
    * Function responsible for test get price node
    *
    * @return null
    */
    public function testGetPriceNode() 
    {
        $priceNode =  $this->feed->getPriceNode($this->product);
        $priceNode = explode("<g:price>", $priceNode);
        $priceNode = explode("</g:price>", $priceNode[1]);
        $price = floatval($priceNode[0]);
        $this->assertEquals(true, $price > 0);
    }
}