<?php
require_once 'app/Mage.php';
Mage::app('admin');

class FeedTest extends PHPUnit_Framework_TestCase
{  
    protected $product;
    protected $feed;

    protected function setUp(){ 
        $this->feed = Mage::getModel('googlemerchant/feed');
        $collection = $this->feed->getProducts()->getFirstItem();
        $this->product = Mage::getModel('catalog/product')->load($collection->getId());
    }

    public function testProductForTestsExists() {
        $this->assertInternalType("string", $this->product->getId(), "Não existe um produto de teste");
    }

    public function testGetPriceNode() 
    {
        $priceNode =  $this->feed->getPriceNode($this->product);
        $priceNode = explode("<g:price>", $priceNode);
        $priceNode = explode("</g:price>", $priceNode[1]);
        $price = floatval($priceNode[0]);
        $this->assertEquals(true, $price > 0);
    }
}