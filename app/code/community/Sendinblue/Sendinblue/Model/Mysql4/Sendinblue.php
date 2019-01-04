<?php
/**
* @author Sendinblue plateform <contact@sendinblue.com>
* @copyright  2013-2014 Sendinblue
* URL:  https:www.sendinblue.com
* Do not edit or add to this file if you wish to upgrade Sendinblue Magento plugin to newer
* versions in the future. If you wish to customize Sendinblue magento plugin for your
* needs then we can't provide a technical support.
**/

class Sendinblue_Sendinblue_Model_Mysql4_Sendinblue extends Mage_Core_Model_Mysql4_Abstract
{
     public function _construct()
    {   
        $this->_init('sendinblue/sendinblue', 'sendinblue_country_code_id');
    }
}
