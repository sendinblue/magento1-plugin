<?php
/**
* @author Sendinblue plateform <contact@sendinblue.com>
* @copyright  2013-2014 Sendinblue
* URL:  https:www.sendinblue.com
* Do not edit or add to this file if you wish to upgrade Sendinblue Magento plugin to newer
* versions in the future. If you wish to customize Sendinblue magento plugin for your
* needs then we can't provide a technical support.
**/

class Sendinblue_Sendinblue_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSMTP()
    {
        return Mage::getStoreConfig('sendinblue/smtp/option') == 'smtp';
    }

    public function getTransport()
    {
        if ($this->getSMTP()) {
            $username = Mage::getStoreConfig('sendinblue/smtp/username');
            $password = Mage::getStoreConfig('sendinblue/smtp/password');
            $host = Mage::getStoreConfig('sendinblue/smtp/host');
            $port = Mage::getStoreConfig('sendinblue/smtp/port');
            $auth = Mage::getStoreConfig('sendinblue/smtp/authentication');
            $config = array();
            $config['username'] = $username;
            $config['password'] = $password;
            $config['port'] = $port;
            $config['ssl'] = null;
            $config['auth'] = $auth;
            $transport = new Zend_Mail_Transport_Smtp($host, $config);
        }
        else {
            Mage::log('Disabled, or no matching transport');
            return null;
        }
        Mage::log('Returning transport');
        return $transport;
    }

    public function ModuleisEnabled()
    {
        return Mage::getStoreConfig('sendinblue/enabled');
    }
    
    public function isEnabled()
    {
        return Mage::getStoreConfig('sendinblue/smtp/status');
    }
}
