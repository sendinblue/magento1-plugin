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
    
    public function getAutomationScript()
    {
	if(Mage::helper('sendinblue')->ModuleisEnabled() == 0) {
           return '';
       	}
        $email = '';
        $clientId = Mage::getStoreConfig('sendinblue/automation/key');
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $email = Mage::getModel('customer/customer')->load($customer->getId())->getEmail();
        }
        return <<<EOT
        <script type="text/javascript">
            (function() {
                window.sib = { equeue: [], client_key: "$clientId" };
                /* OPTIONAL: email for identify request*/
                window.sib.email_id = "$email";
                window.sendinblue = {}; for (var j = ['track', 'identify', 'trackLink', 'page'], i = 0; i < j.length; i++) { (function(k) { window.sendinblue[k] = function() { var arg = Array.prototype.slice.call(arguments); (window.sib[k] || function() { var t = {}; t[k] = arg; window.sib.equeue.push(t);})(arg[0], arg[1], arg[2]);};})(j[i]);}var n = document.createElement("script"),i = document.getElementsByTagName("script")[0]; n.type = "text/javascript", n.id = "sendinblue-js", n.async = !0, n.src = "https://sibautomation.com/sa.js?key=" + window.sib.client_key, i.parentNode.insertBefore(n, i), window.sendinblue.page();
            })();   
        </script>
EOT;
    }
}
