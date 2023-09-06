<?php

class Cammino_Googlemerchant_Model_Job
{
    public function createxml() {
        $minuteInterval = Mage::getStoreConfig('catalog/googlemerchant/xmlmininterval');
        $currentDateTimeString = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
        $lastCreatedAt = Mage::getStoreConfig('catalog/googlemerchant/xmllastcreated');
        $lastTimestamp = Mage::getModel('core/date')->timestamp(strtotime($lastCreatedAt));        
        if ((($currentTimestamp - $lastTimestamp) >= (($minuteInterval * 60) * 60) || empty($lastCreatedAt))) {
            $fileName = Mage::getBaseDir() . DS . 'googlemerchant.xml';
            $feed = Mage::getModel('googlemerchant/feed');
            try {
                $allStores = Mage::app()->getStores();
                foreach($allStores as $store) {
                    if($store->getId() > 0) {
                        $xml = $feed->getXml($store->getId());
                        file_put_contents(($store->getId() > 1) ? Mage::getBaseDir() . DS . 'googlemerchant' . '_' . $store->getCode() . '.xml' : $fileName, $xml);
                        Mage::log('XML file created with success (store: ' . $store->getId() . ' - ' . $store->getName() . ')' , null, 'googlemerchant_job.log');
                    }
                }
                Mage::getModel('core/config')->saveConfig('catalog/googlemerchant/xmllastcreated', $currentDateTimeString, 'default');
                Mage::app()->getCacheInstance()->cleanType('config');
            } catch (Exception $e) {
                Mage::log('Error creating XML file: '. $e->getMessage(), null, 'googlemerchant_job.log');
            }
        }
    }
}