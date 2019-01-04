<?php
/**
* @author Sendinblue plateform <contact@sendinblue.com>
* @copyright  2013-2014 Sendinblue
* URL:  https:www.sendinblue.com
* Do not edit or add to this file if you wish to upgrade Sendinblue Magento plugin to newer
* versions in the future. If you wish to customize Sendinblue magento plugin for your
* needs then we can't provide a technical support.
**/

class Sendinblue_Sendinblue_Adminhtml_NotifyController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $responce = Mage::getModel('sendinblue/sendinblue')->notifySmsEmail();
        $messageDisplay = $this->__('The CRON has been well executed.');
        Mage::getModel('adminhtml/session')->addSuccess($messageDisplay);
        $this->_redirect("adminhtml/myform/");
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/config');
    }
}
