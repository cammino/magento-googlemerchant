<?php
class Cammino_Googlemerchant_FeedController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		$feed = Mage::getModel('googlemerchant/feed');
		$xml = $feed->getXml();
		echo $xml;
	}

}