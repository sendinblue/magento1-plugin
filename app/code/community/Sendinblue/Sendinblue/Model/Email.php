<?php
/**
* @author Sendinblue plateform <contact@sendinblue.com>
* @copyright  2013-2014 Sendinblue
* URL:  https:www.sendinblue.com
* Do not edit or add to this file if you wish to upgrade Sendinblue Magento plugin to newer
* versions in the future. If you wish to customize Sendinblue magento plugin for your
* needs then we can't provide a technical support.
**/

class Sendinblue_Sendinblue_Model_Email extends Mage_Core_Model_Email {
    /**
     * override send function 
     */
    public function send()
    {  
        // If it's not enabled, just return the parent result.
        if (Mage::helper('sendinblue')->isEnabled() == 0 || Mage::helper('sendinblue')->ModuleisEnabled() == 0)
             return parent::send($email, $name, $variables);
        Mage::log('SendinblueSMTP is enabled, sending email in Sendinblue_Sendinblue_Model_Sendinblue');
        $mail = new Zend_Mail();
        if (strtolower($this->getType()) == 'html') {
            $mail->setBodyHtml($this->getBody());
        }
        else {
            $mail->setBodyText($this->getBody());
        }
        $transport = Mage::helper('sendinblue')->getTransport();
        $email = Mage::getStoreConfig('contacts/email/recipient_email');
        $mail->setFrom($this->getFromEmail(), $this->getFromName())
             ->addTo($email, $this->getToName())
             ->setSubject($this->getSubject());
        Mage::log('About to send email');
        $mail->send($transport);
        Mage::log('Finished sending email');
        return $this;
    }
}
