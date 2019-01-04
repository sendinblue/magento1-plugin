<?php
/**
* @author Sendinblue plateform <contact@sendinblue.com>
* @copyright  2013-2014 Sendinblue
* URL:  https:www.sendinblue.com
* Do not edit or add to this file if you wish to upgrade Sendinblue Magento plugin to newer
* versions in the future. If you wish to customize Sendinblue magento plugin for your
* needs then we can't provide a technical support.
**/

class Sendinblue_Sendinblue_Model_Email_Template extends Mage_Core_Model_Email_Template {
    
    public function send($email, $name=null, array $variables = array()) 
    {                
        // If it's not enabled, just return the parent result.        
        // If it's not enabled, just return the parent result.
        if (Mage::helper('sendinblue')->isEnabled()==0 || Mage::helper('sendinblue')->ModuleisEnabled()==0) { 
             return parent::send($email, $name, $variables);  
        }

        if(!$this->isValidForSend()) {
            Mage::log('SMTP: Email not valid for sending - check template, and smtp enabled/disabled setting');     
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }
        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }
        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);        
        $mail = $this->getMail();

        if (true) {
            $email = Mage::getStoreConfig('contacts/email/recipient_email', $this->getDesignConfig()->getStore());
            Mage::log("Development mode set to send all emails to contact form recipient: " . $email);          
        }        
        // In Magento core they set the Return-Path here, for the sendmail command.       
        
        foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
        }
        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        if($this->isPlain()) {
            $mail->setBodyText($text);
        } 
        else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject('=?utf-8?B?'.base64_encode($this->getProcessedTemplateSubject($variables)).'?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());
        $transport = Mage::helper('sendinblue')->getTransport();

        try {        
            // adding new Event dispatch in case anyone wants to interrogate an email before being sent
            // throwing an Exception in the Event Observer will prevent the mail being sent,
            // and will return false to the calling function
            Mage::dispatchEvent('sendin_email_after_send', array(
                'mail' => $mail,
                'template' => $this->getTemplateId(),
                'subject' => $this->getProcessedTemplateSubject($variables),
            ));
               
            Mage::log('About to send email');
            $mail->send($transport); // Zend_Mail warning..
           
            Mage::log('Finished sending email');
            $this->_mail = null;
        } 
        catch (Exception $exception) {            
            Mage::logException($exception);
            $responceData = array('result'=>false, 'error'=>$exception->getMessage());
            return json_encode($responceData);
        }
        $responceData = array('result'=>true);
        return json_encode($responceData);
    }
}
