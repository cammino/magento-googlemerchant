<?php

class Cammino_Googlemerchant_Model_Job
{
    public function createxml() {
        $minuteInterval = Mage::getStoreConfig('catalog/googlemerchant/xmlmininterval');
        $currentDateTimeString = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
        $lastCreatedAt = Mage::getStoreConfig('catalog/googlemerchant/xmllastcreated');
        if (empty($lastCreatedAt)) {
            Mage::getModel('core/date')->date('Y-m-d H:i:s');
        }
        $lastTimestamp = Mage::getModel('core/date')->timestamp(strtotime($lastCreatedAt));
        if (($currentTimestamp - $lastTimestamp) >= ($minuteInterval * 60)) {
            $fileName = Mage::getBaseDir() . DS . 'googlemerchant.xml';
            $feed = Mage::getModel('googlemerchant/feed');
            $xml = $feed->getXml();
            try {
                file_put_contents($fileName, $xml);
                Mage::getModel('core/config')->saveConfig('catalog/googlemerchant/xmllastcreated', $currentDateTimeString, 'default');
                Mage::app()->getCacheInstance()->cleanType('config');
                Mage::log('XML file created with success.', null, 'googlemerchant_job.log');
            } catch (Exception $e) {
                Mage::log('Error creating XML file: '. $e->getMessage(), null, 'googlemerchant_job.log');
            }
        }
    }
}