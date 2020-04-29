<?php
/**
* @author Sendinblue plateform <contact@sendinblue.com>
* @copyright  2013-2014 Sendinblue
* URL:  https:www.sendinblue.com
* Do not edit or add to this file if you wish to upgrade Sendinblue Magento plugin to newer
* versions in the future. If you wish to customize Sendinblue magento plugin for your
* needs then we can't provide a technical support.
**/
class Sendinblue_Sendinblue_Model_Sendinblue extends Mage_Core_Model_Abstract
{
    var $apiKey;
    var $smtpStatus;
    var $errorMessage;
    var $errorCode;
    var $listsIds;
    var $moduleEnable;
    var $st;
    public function _construct()
    {
        parent::_construct();
        $this->_init('sendinblue/sendinblue');
        $this->MIAPI();

    }
    /**
    * functions used for set module config
    */
    public function MIAPI()
    {
        $scope = ($this->getScope()) ? $this->getScope() : Mage::app()->getStore()->getStoreId();       
        $this->moduleEnable = $this->getEnableStatus($scope);   
        $this->apiKey = $this->getApiKey();
        $valueLanguage = $this->getApiConfigValue();
        if(isset($valueLanguage['data']['language'])) {
            $this->userLanguage = $valueLanguage['data']['language'];
        }

        if (!$this->listsIds) {
            $this->listsIds = str_replace(',', '|', $this->getUserlists($scope));
        }

        $params = Mage::app()->getRequest()->getParams();
        $params = empty($params)?array():$params;
        if (isset($params['sendin_apikey']) && $params['sendin_apikey'] != '') {
            $this->CreateFolderCaseTwo();
        }
    }
    
    public function checkMobileNumber($number, $callPrefix)
    {
        $number = preg_replace('/\s+/', '', $number);
        $charOne = substr($number, 0, 1);
        $charTwo = substr($number, 0, 2);

        if (preg_match('/^'.$callPrefix.'/', $number)) {
            return '00'.$number;
        }

        elseif ($charOne == '0' && $charTwo != '00') {
            if (preg_match('/^0'.$callPrefix.'/', $number)) {
                return '00'.substr($number, 1);
            }
            else {
                return '00'.$callPrefix.substr($number, 1);
            }
        }
        elseif ($charTwo == '00') {
            if (preg_match('/^00'.$callPrefix.'/', $number)) {
                return $number;
            }
            else {
                return '00'.$callPrefix.substr($number, 2);
            }
        }
        elseif ($charOne == '+') {
            if (preg_match('/^\+'.$callPrefix.'/', $number)) {
                return '00'.substr($number, 1);
            }
            else {
                return '00'.$callPrefix.substr($number, 1);
            }
        }
        elseif ($charOne != '0') {
            return '00'.$callPrefix.$number;
        }
    }
    /**
     * functions used for getting module status
     */
    public function getEnableStatus()
    {
        $status = $this->getGeneralConfig('enabled', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }
    /**
    * functions used for send order sms module status
    */
    public function getOrderSmsStatus()
    {
        $status = $this->getGeneralConfig('sms/order', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }
    /**
    * functions used for getting notify sms status
    */
    public function getNotifySmsStatus()
    {
        $status = $this->getGeneralConfig('sms/credit', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }
    /**
    * functions used for getting Notify value limit 
    */
    public function getNotifyValueStatus()
    {
        $status = $this->getGeneralConfig('Sendin_Notify_Value', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }
    /**
    * functions used for getting Notify email limit 
    */
    public function getNotifyEmailStatus()
    {
        $status = $this->getGeneralConfig('Sendin_Notify_Email', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }
    /**
    * functions used for getting Notify email limit 
    */
    public function getNotifyCronStatus()
    {
        $status = $this->getGeneralConfig('Sendin_Notify_Cron_Executed', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }
    /**
    * functions used for getting shiping sms status
    */
    public function getShipingSmsStatus()
    {
        $status = $this->getGeneralConfig('sms/shiping', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }

    /**
    * functions used for getting campaign sms status
    */
    public function getCampaignStatus()
    {
        $status = $this->getGeneralConfig('sms/campaign', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }

    /**
    * functions used for getting send sms order subject
    */
    public function getSendSmsOrderSubject()
    {
        $status = $this->getGeneralConfig('Sendin_Sender_Order', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return '';
        }
        return $status;
    }

    /**
    * functions used for getting order sms message
    */
    public function getSendSmsmOrderMessage()
    {
        $status = $this->getGeneralConfig('Sendin_Sender_Order_Message', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return '';
        }
        return $status;
    }

    /**
    *functions used for getting send sms shiping subject
    */
    public function getSendSmsShipingSubject()
    {
        $status = $this->getGeneralConfig('Sendin_Sender_Shipment', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }

    /**
    *functions used for getting shiping sms message
    */
    public function getSendSmsShipingMessage()
    {
        $status = $this->getGeneralConfig('Sendin_Sender_Shipment_Message', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }

    /**
    * functions used for get api key
    */
    public function getApiKey()
    {
        $apikey = $this->getGeneralConfig('api', Mage::app()->getStore()->getStoreId());
        if (!$apikey) {
            return false;
        }
        return $apikey;
    }

    /**
    * functions used for get SMPT password
    */
    public function getSmtpPassword()
    {
        $smtpPassword = $this->getSendinSmtpStatus('password', Mage::app()->getStore()->getStoreId());
        if (!$smtpPassword) {
            return false;
        }
        return $smtpPassword;
    }

    /**
    * functions used for get user name
    */
    public function getUserName()
    {
        $userName = $this->getSendinSmtpStatus('username', Mage::app()->getStore()->getStoreId());
        if (!$userName) {
            return false;
        }
        return $userName;
    }

    /**
    * functions used for getting smtp status
    */
    public function getSmtpStatus()
    {
        $status = $this->getSendinSmtpStatus('status', Mage::app()->getStore()->getStoreId());
        if (!$status) { 
            return false;
        }
        return $status;
    }

    /**
    * functions used for getting tracking status
    */
    public function getTrackingStatus()
    {
        $status = $this->getSendinTrackingCodeStatus('code', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }

    /**
    * functions used for getting tracking status
    */
    public function getTrackingHistoryStatus()
    {
        $status = $this->getSendinTrackingHistoryStatus('history', Mage::app()->getStore()->getStoreId());
        if (!$status) {
            return false;
        }
        return $status;
    }

    /**
    * functions used for getting userlists
    */
    public function getUserlists()
    {
        $userlist = $this->getGeneralConfig('list', Mage::app()->getStore()->getStoreId());
        if (!$userlist) {
            return false;
        }
        return $userlist;
    }

    /**
    * functions used for getting importOldSubscribers status
    */
    public function getImportOldSubsStatus()
    {
        $importStatus = $this->getGeneralConfig('importOldUserStatus', Mage::app()->getStore()->getStoreId());      
        if (!$importStatus) {
            return false;
        }
        return $importStatus;
    }

    /**
    * functions used for get templateid
    */
    public function getTemplateId()
    {
        $templateId = $this->getGeneralConfig('SendinTemplateId', Mage::app()->getStore()->getStoreId());
        if (!$templateId) {
            return false;
        }
        return $templateId;
    }
    /**
    * functions used for get value final email recive.
    */
    public function getFinalTemplate()
    {
        $finalTemplate = $this->getGeneralConfig('SendinTemplateFinal', Mage::app()->getStore()->getStoreId());
        if (!$finalTemplate) {
            return false;
        }
        return $finalTemplate;
    }
    /**
    * functions used for get value subscribe type like doubleoptin and simple.
    */
    public function getSubscribeConfirmType()
    {
        $subscribeConfirmType = $this->getGeneralConfig('SendinSubscribeConfirmType', Mage::app()->getStore()->getStoreId());
        if (!$subscribeConfirmType) {
            return false;
        }
        return $subscribeConfirmType;
    }

    /**
    * functions used to get Double optin Template Id in case of doubleoptin type selected.
    */
    public function getDoubleoptinTemplateId()
    {
        $doubleoptinTemplateId = $this->getGeneralConfig('SendinDoubleoptinTemplateId', Mage::app()->getStore()->getStoreId());
        if (!$doubleoptinTemplateId) {
            return false;
        }
        return $doubleoptinTemplateId;
    }

    /**
    * functions used for get value for redirect url
    */
    public function getOptinRedirectUrlCheck()
    {
        $optionRedirectUrlCheck = $this->getGeneralConfig('SendinOptinRedirectUrlCheck', Mage::app()->getStore()->getStoreId());
        if (!$optionRedirectUrlCheck) {
            return false;
        }
        return $optionRedirectUrlCheck;
    }
    /**
    * functions used for get double optin redirect url after click email link.
    */
    public function getSendinDoubleoptinRedirectUrl()
    {
        $sendinDoubleoptinRedirectUrl = $this->getGeneralConfig('SendinDoubleoptinRedirectUrl', Mage::app()->getStore()->getStoreId());
        if (!$sendinDoubleoptinRedirectUrl) {
            return false;
        }
        return $sendinDoubleoptinRedirectUrl;
    }
    /**
    * functions used for get final confirmation email for double optin functionality.
    */
    public function getSendinFinalConfirmEmail()
    {
        $sendinFinalConfirmEmail = $this->getGeneralConfig('SendinFinalConfirmEmail', Mage::app()->getStore()->getStoreId());
        if (!$sendinFinalConfirmEmail) {
            return false;
        }
        return $sendinFinalConfirmEmail;
    }

    /**
    * functions used for get doubleoptin id geting by sendinblue.
    */
    public function getSendinOptinListId()
    {
        $sendinOptionListId = $this->getGeneralConfig('SendinOptinListId', Mage::app()->getStore()->getStoreId());
        if (!$sendinOptionListId) {
            return false;
        }
        return $sendinOptionListId;
    }

    /**
    * functions used for getting general config
    */
    public function getGeneralConfig($field, $store = null)
    {
        return Mage::getStoreConfig('sendinblue/'.$field, $store);
    }

    /**
     * functions used for get sendinsmtp status
     */
    public function getSendinSmtpStatus($field, $store = null)
    {
        return Mage::getStoreConfig('sendinblue/smtp/'.$field, $store);
    }

     /**
     * functions used for get sendinblue email status
     */
    public function getSyncronizeStatus()
    {
        return $this->getGeneralConfig('syncronize', Mage::app()->getStore()->getStoreId());
    }
    /**
    * functions used for get sendin tracking status
    */
    public function getSendinTrackingCodeStatus($field, $store = null)
    {
        return Mage::getStoreConfig('sendinblue/tracking/'.$field, $store);
    }

    /**
    * functions used for get sendin tracking history status
    */
    public function getSendinTrackingHistoryStatus($field, $store = null)
    {
        return Mage::getStoreConfig('sendinblue/improt/'.$field, $store);
    }

    /**
    * functions used for module functionality
    */
    public function getLists()
    {
        return $this->lists();
    }

    /**
     * functions used for email adds
     */
    public function emailAdd($email, $extra, $isSubscribed = '', $listId = '')
    {
        $attributesName = $this->allAttributesName();
        if ($this->moduleEnable == 1 && $this->getSyncronizeStatus())
        {
            $apikey = $this->apiKey;
            if (!$apikey) {
                return false;
            }
            $sendinConfirmType = Mage::getStoreConfig('sendinblue/SendinSubscribeConfirmType');
            if (empty($listId)) {
                if (isset($sendinConfirmType) && $sendinConfirmType === 'doubleoptin') {
                    $listId = Mage::getStoreConfig('sendinblue/SendinOptinListId');
                    $extra['DOUBLE_OPT-IN'] = 2;
                    $attributesName['DOUBLE_OPT-IN'] = 'DOUBLE_OPT-IN';
                } 
                else {
                    $listId = $this->listsIds;
                }
            }
            
            $userData = array();
            $userData['email'] = $email;
            $userData['id'] = '';
            if ($isSubscribed != '') {
                $userData['blacklisted'] = 0;
            }
            if ($extra != null) {
                $keyValue = array_keys($attributesName);
                $attributesName = is_array($keyValue) ? $keyValue : array($keyValue);
                $attributesValue = is_array($extra) ? $extra : array($extra);
                $userData['attributes'] = array_combine($attributesName, $attributesValue);
            } 
            else {
                $userData['attributes_value'] = $email;
            }
            $userData['listid'] = (is_array($listId)) ? $listId : array($listId);
            $params['api_key'] = $this->apiKey;
            $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
            return $psmailinObj->createUpdateUser($userData);   
        } 
        else {
            return false;
        }
    }

    /**
    * functions subscribeuser
    */
    public function emailSubscribe($email)
    {
        if ($this->moduleEnable == 1 && $this->getSyncronizeStatus()) {
            $apikey = $this->apiKey;
            $timezone = Mage::app()->getStore()->getConfig('general/locale/timezone');
            $userTimeZone = str_replace('Calcutta', 'Kolkata', $timezone);
            $dateValue = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));

            if (!$apikey) {
                return false;
            }
            $userData = array();
            $userData['timezone'] = $userTimeZone;
            $userData['user_status'] = $email.', '.'1'.', '.$dateValue;
            $params['api_key'] = $this->apiKey;
            $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
            return $psmailinObj->updateUserSubscriptionStatus($userData);
        } 
        else {
            return false;
        }
    }

    /**
    * functions used for sync data
    */
    public function syncData()
    { 
        if ($this->moduleEnable == 1 && $this->getSyncronizeStatus()) {
            $apikey = $this->apiKey;
            if (!$apikey) {
                return false;
            }
            $listIds = array();
            $listIds['listids'] = str_replace(',', '|', $this->listsIds);
            
            $params['api_key'] = $this->apiKey;         
            $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
            $blockUsersLists = $psmailinObj->getListUsersBlacklistStatus($listIds); 

            $blockUsers = $blockUsersLists['data'];
            $collection = Mage::getResourceModel('newsletter/subscriber_collection')->showStoreInfo()->showCustomerInfo()->toArray();
            $subScriberData = $collection['items'];
            $emails = array();          
            $subScriberDataEmail = array();
            foreach($subScriberData as $s) {
                $subScriberDataEmail[$s['subscriber_email']] = $s;
            }

            if (count($blockUsers) > 0) {
                $newsLetterSubscriber = Mage::getModel('newsletter/subscriber');
                foreach ($blockUsers as $key => $value) {
                    foreach ($value as $userData) {
                         if(isset($subScriberDataEmail[$userData['email']])) {
                            // on a trouvé le subscriber magento
                            $data = $subScriberDataEmail[$userData['email']];
                            $tempSubStatus = ($data['subscriber_status'] == 3) ? 1 : 0;
                            if ($tempSubStatus != $userData['blacklisted']) {
                                $emails[] = $data['subscriber_email'];
                                $subscribeData['subscriber_id'] = $data['subscriber_id'];
                                $subscribeData['subscriber_status'] = ($userData['blacklisted'] == 1) ? 3 : 1;
                                $costomerData = $newsLetterSubscriber->loadByEmail($data['subscriber_email']);
                                $costomerData->setStatus($subscribeData['subscriber_status']);
                                $costomerData->setIsStatusChanged(true);
                                $costomerData->save();
                            }
                        }
                    }
                }
            }
            
            if (count($emails) > 0) {
                Mage::getModel('core/session')->addSuccess(count($emails).Mage::helper('sendinblue')->__(' Total of record(s) have been updated'));
            }
            else {
                Mage::getModel('core/session')->addSuccess(count($emails).Mage::helper('sendinblue')->__(' Total of record(s) have been updated'));
            }
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * This method is used for add email list
     */
    public function addEmailList($email, $listId = '')
    {
        if ($this->moduleEnable == 1 && $this->getSyncronizeStatus()) {
            $apikey = $this->apiKey;
            if (!$apikey) {
                return false;
            }
            $sendinConfirmType = Mage::getStoreConfig('sendinblue/SendinSubscribeConfirmType');
            if (empty($listId)) {
                if (isset($sendinConfirmType) && $sendinConfirmType === 'doubleoptin') {
                    $listId = Mage::getStoreConfig('sendinblue/SendinOptinListId');
                } 
                else {
                    $listId = $this->listsIds;
                }
            }
            $userData = array();
            $userData['email'] = $email;
            $userData['id'] = '';
            $userData['blacklisted'] = 0;
            $userData['attributes']  = array();
            $userData['listid'] = (is_array($listId)) ? $listId : array($listId);
            $params['api_key'] = $this->apiKey;
            $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
            return $psmailinObj->createUpdateUser($userData);
        } 
        else {
            return false;
        }
    }
    
    /**
     * This method is used used for email unsubscribe
     */
    public function emailDelete($email)
    {
        if ($this->moduleEnable == 1 && $this->getSyncronizeStatus()) {
            $apikey = $this->apiKey;
            if (!$apikey) {
                return false;
            }
            $userData = array();
            $userData['email']  = $email;
            $userData['listid'] = $this->listsIds;
            $params['api_key'] = $this->apiKey;
            $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
            return $psmailinObj->unSubscribApi($userData);            
        } 
        else {
            return false;
        }
    }

    /**
    * This method is used used for check api status
    */
    public function checkApikey($userApiKey)
    {
        $params['api_key'] = (!empty($userApiKey)) ? $userApiKey : $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin',$params);
        $keyResponse = $psmailinObj->getAccount();      

        if (isset($keyResponse['code']) && $keyResponse['code'] == 'failure' && isset($keyResponse['message']) && $keyResponse['message'] == 'Key Not Found In Database') {
            $lists['error'] = $keyResponse['message'];
            return $lists;
        }
    }

    /**
     * Fetches all the list of the user from the Sendinblue platform.
     */
    public function lists($filters = array())
    {
        $params   = array();
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $response = $psmailinObj->displayListForPlugin();

        if (isset($response['code']) && $response['code'] == 'failure'){
            $this->errorMessage = $response['message'];
            $lists['error'] = $response['message'];
        } 
        else {
            $i = 0;
            $lists = array();
            foreach ($response['data'] as $listData) {
                $lists[$i]['id'] = $listData['id'];
                $lists[$i]['name'] = $listData['name'];
                $i++;
            }
        }
        return $lists;
    }

    /**
     * Fetches the list status of the user from the Sendinblue platform.
     */
    public function getUserListStats()
    {
        if ($this->moduleEnable == 1) {
            $params['api_key'] = $this->apiKey;
            $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
            return $psmailinObj->displayListForPlugin();
        } 
        else {
            return Mage::getModel('core/session')->addError('Sendinblue not enabled');
        }
    }

    /**
     * Fetches all folders and all list within each folder of the user's Sendinblue 
     * account and displays them to the user. 
    */
    public function checkFolderListDoubleoptin()
    {
        $returnData = array();
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $foldersListResponse = $psmailinObj->displayFoldersLists();       
        if (!empty($foldersListResponse['data'])) {
            foreach ($foldersListResponse['data'] as $value) {
                if (strtolower($value['name']) == 'form') {
                    if (!empty($value['lists'])) {
                        foreach ($value['lists'] as $key => $val) {
                            if ($val['name'] == 'Temp - DOUBLE OPTIN') {
                                $returnData['optin_id'] = $key;
                            }
                        }
                    }
                }
            }
            if (count($returnData) > 0) {
                $return = $returnData;
            } 
            else {
                $return = false;
            }
        }
        return $return;
    }
    
    /**
     * Create temporary doubleoptin list if not exist in Sendinblue.
     */
    public function createListIdDoubleoptin()
    {
        $folderInfomation = array();
        $folderId = ''; $listId = '';
        $folderInfomation['name'] = 'FORM';
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $returnFolderId = $psmailinObj->createFolder($folderInfomation);

        if(isset($returnFolderId['data']['id'])) {
            $folderId = $returnFolderId['data']['id'];
        }
        if (!empty($folderId)) {
            $listInformation = array();
            $listInformation['list_name'] = 'Temp - DOUBLE OPTIN';
            $listInformation['list_parent'] = $folderId;
            $listIdResponse = $psmailinObj->createList($listInformation);
            $listId = $listIdResponse['data']['id'];
        }
        return $listId;
    }

    /**
     * Fetches all folders and all list within each folder of the user's Sendinblue 
     * account and displays them to the user. 
     */
    public function checkFolderList()
    {
        $params = array();
        $array = array();
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $foldersListResponse = $psmailinObj->displayFoldersLists();
        
        if (!empty($foldersListResponse['data'])) {
            foreach ($foldersListResponse['data'] as $key => $value) {
                if (strtolower($value['name']) == 'magento') {
                    $array[] = $key;
                    $array[] = $value['name'];
                }
                if (!empty($value['lists'])) {
                    foreach ($value['lists'] as $val) {
                        if (strtolower($val['name']) == 'magento') {
                            $array[] = $val['name'];
                        }
                    }
                }
            }
        }
        return $array;
    }

    /**
     *  folder create in Sendinblue after removing from Sendinblue
     */
    public function createFolderCaseTwo()
    {
        $apiKey = $this->apiKey;        
        if($apiKey == '') {
            return false;
        }
        $apiKeyResponse = $this->checkApikey($apiKey); // check api key is valid or not
        if($this->moduleEnable != 1 && $apiKey == '' && $apiKeyResponse['error'] != '' && $this->getSyncronizeStatus()) {
                return false;
        }
        $result = $this->checkFolderList();
        $listName = 'magento';
        $data  = array();
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $configObj = Mage::getModel('core/config');
        $folderId = $result[0];
        $existList = $result[2];
        if (empty($result[1])) {
            $createListParameters = array();
            $createListParameters['name'] = 'magento';
            $folderResponse = $psmailinObj->createFolder($data);
            if(isset($folderResponse['data']['id'])) {
                $folderId = $folderResponse['data']['id'];
            }
            $createListParameters = array();
            $createListParameters['list_name'] = $listName;
            $createListParameters['list_parent'] = $folderId; //folder id
            $listResponse = $psmailinObj->createList($createListParameters);
            $listId = !empty($listResponse['data']['id']) ? $listResponse['data']['id'] : '';
            $configObj->saveConfig('sendinblue/list', $listId, 'default', 0);
        } 
        elseif (empty($existList)) {
            $createListParameters = array();
            $createListParameters['list_name'] = $listName;
            $createListParameters['list_parent'] = $folderId; //folder id
            $listResponse = $psmailinObj->createList($createListParameters);
            $listId = !empty($listResponse['data']['id']) ? $listResponse['data']['id'] : '';
            $configObj->saveConfig('sendinblue/list', $listId, 'default', 0);
        }
    }

    /**
     *  folder create in Sendinblue after installing
     */
    public function createFolderName($apiKey)
    {
        $this->apiKey = $apiKey;        
        $this->createAttributesName();
        $folderInformation = $this->checkFolderList();
        $folderId = '';
        if (empty($folderInformation[1])) {
            $folderData = array();
            $params['api_key'] = $this->apiKey;
            $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
            $folderData['name'] = 'magento';
            $responseFolderId = $psmailinObj->createFolder($folderData);
            if(isset($responseFolderId['data']['id'])) {
                $folderId = $responseFolderId['data']['id'];
            }
            $existList = '';
        } 
        else {
            $folderId  = $folderInformation[0];
            $existList = $folderInformation[2];
        }
        $this->createNewList($folderId, $existList);
        $this->partnerMagento();
    }

    /**
     * Method is used to add the partner's name in Sendinblue.
     * In this case its "MAGENTO".
     */
    public function partnerMagento()
    {
        $mailinPartnerParameters = array();
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $mailinPartnerParameters['partner'] = 'MAGENTO';
        $psmailinObj->updateMailinParter($mailinPartnerParameters);
    }

    /**
     * Creates a list by the name "magento" on user's Sendinblue account.
    */
    public function createNewList($response, $existList)
    {
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $configObj = Mage::getModel('core/config');
        if ($existList != '') {
            $date     = date('dmY');
            $listName = 'magento_'.$date;
        }
        else {
            $listName = 'magento';
        }
        $listData = array();
        $listData['list_name'] = $listName;
        $listData['list_parent'] = $response;
        $listResponse = $psmailinObj->createList($listData);
        $listId = !empty($listResponse['data']['id']) ? $listResponse['data']['id'] : '';
        $configObj->saveConfig('sendinblue/list', $listId, 'default', 0);       
        $this->createAttributesName();
    }

    /**
     * Fetch attributes and their values
     * on Sendinblue platform. This is necessary for the Prestashop to add subscriber's details.
     */
    public function allAttributesName()
    {
        if ($this->userLanguage == 'fr') {
            $attributesName = array('PRENOM'=>'firstname', 'NOM'=>'lastname', 'MAGENTO_LANG'=>'created_in','CLIENT'=>'client','SMS'=>'telephone','COMPANY'=>'company','CITY'=>'city','COUNTRY_ID'=>'country_id','POSTCODE'=>'postcode','STREET'=>'street','REGION'=>'region','STORE_ID'=>'store_id');
        }
        else {
            $attributesName = array('NAME'=>'firstname', 'SURNAME'=>'lastname', 'MAGENTO_LANG'=>'created_in','CLIENT'=>'client','SMS'=>'telephone','COMPANY'=>'company','CITY'=>'city','COUNTRY_ID'=>'country_id','POSTCODE'=>'postcode','STREET'=>'street','REGION'=>'region','STORE_ID'=>'store_id');
        }
        return $attributesName;
    }

    /**
    * Fetch attributes name and type
    * on Sendinblue platform. This is necessary for the Prestashop to add subscriber's details.
    */
    public function allAttributesType()
    {
        if ($this->userLanguage == 'fr') {
            $attributesType = array('PRENOM'=>'text', 'NOM'=>'text', 'MAGENTO_LANG'=>'text','CLIENT'=>'number','SMS'=>'text','COMPANY'=>'text','CITY'=>'text','COUNTRY_ID'=>'text','POSTCODE'=>'number','STREET'=>'text','REGION'=>'text','STORE_ID'=>'number');
        }
        else {
            $attributesType = array('NAME'=>'text', 'SURNAME'=>'text', 'MAGENTO_LANG'=>'text','CLIENT'=>'number','SMS'=>'text','COMPANY'=>'text','CITY'=>'text','COUNTRY_ID'=>'text','POSTCODE'=>'number','STREET'=>'text','REGION'=>'text','STORE_ID'=>'number');
        }
        return $attributesType;
    }

    /**
    * Fetch all Transactional Attributes 
    * on Sendinblue platform. This is necessary for the Prestashop to add subscriber's details.
    */
    public function allTransactionalAttributes()
    {
        $transactionalAttributes = array('ORDER_ID'=>'id', 'ORDER_DATE'=>'date', 'ORDER_PRICE'=>'number');
        return $transactionalAttributes;
    }

    /**
    * Create Normal, Transactional, Calculated and Global attributes and their values
    * on Sendinblue platform. This is necessary for the Prestashop to add subscriber's details.
    */
    public function createAttributesName()
    {
        $normalAttributesData = array();
        $transactionalAttributesData = array();
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $noramalAttributes = $this->allAttributesType();        
        $transactionalAttributes = $this->allTransactionalAttributes();        
        
        $normalAttributesData = array(
            "type" => "normal",
            "data" => $noramalAttributes
        );
        $psmailinObj->createAttribute($normalAttributesData);

        $transactionalAttributesData = array(
            "type" => "transactional",
            "data" => $transactionalAttributes
        );
        $psmailinObj->createAttribute($transactionalAttributesData);              
    }
    
    /**
     * Method is used to send all the subscribers from magento to
     * Sendinblue for adding / updating purpose.
    */
    public function sendAllMailIDToSendin($list)
    {
        $sendinSwitch = Mage::getModel('core/config');
        $allEmail = $this->getcustomers();
        if ($allEmail > 0) {
            $userDataInformation = array();
            $params['api_key'] = $this->apiKey;
            $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
            $fileName = Mage::getStoreConfig('sendinblue/CsvFileName');
            $userDataInformation['key'] = $this->apiKey;
            $userDataInformation['url'] = Mage::getBaseUrl('media').'sendinblue_csv/'.$fileName.'.csv';
            $userDataInformation['listids'] = array($list); // $list;
            $userDataInformation['notify_url'] = Mage::getBaseUrl().'sendinblue/ajax/emptySubsUserToSendinblue';
            $responseValue = $psmailinObj->importUsers($userDataInformation);
            $sendinSwitch->saveConfig('sendinblue/importOldUserStatus', 0, 'default', 0);
            if (empty($responseValue['data']['process_id'])) {
                $sendinSwitch->saveConfig('sendinblue/importOldUserStatus', 1);
            }                       
        }
        $sendinSwitch->saveConfig('sendinblue/list', $list, 'default', 0);
    }

    /**
    * Send SMS from Sendin.
    */
    public function sendSmsApi($array)
    {
        $smsSendData = array();
        $smsSendData['to'] = $array['to'];
        $smsSendData['from'] = $array['from'];
        $smsSendData['text'] = $array['text'];
        $smsSendData['source'] = 'api';
        $smsSendData['plugin'] = 'magento1-plugin';
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $sendSmsStatus = $psmailinObj->sendSms($smsSendData);
        return $sendSmsStatus['data'];
    }
    
    public function sendOrder($mobile)
    {      
        if (isset($mobile)) {
            $sendOrderData = array();
            $sendOrderData['to'] = $mobile;
            $sendOrderData['from'] = $this->getSendSmsOrderSubject();
            $sendOrderData['text'] = $this->getSendSmsmOrderMessage();
            return $this->sendSmsApi($sendOrderData);           
        }
    }

    public function notifySmsEmail()
    {
        $sendinSwitch = Mage::getModel('core/config');
        if($this->getSmsCredit() < $this->getNotifyValueStatus() && $this->moduleEnable == 1 && $this->getNotifySmsStatus() == 1) {
            if($this->getNotifyCronStatus() == 0) { 
                $sendinSwitch->saveConfig('sendinblue/Sendin_Notify_Cron_Executed', 1, 'default', 0);   
                $localeCode = Mage::app()->getLocale()->getLocaleCode();
                $emailTemplateVariables = array();

                $emailTemplateVariables['text0'] = '[Sendinblue] Alert: You do not have enough credits SMS';
                $senderName = 'SendinBlue';
                $senderEmail = 'contact@sendinblue.com';

                if ($localeCode == 'fr_FR') {
                    $emailTemplateVariables['text0'] = ' [Sendinblue] Alerte: Vos crédits SMS seront bientôt épuisés';
                    $senderName = 'SendinBlue';
                    $senderEmail = 'contact@sendinblue.com';
                }

                $email = $this->getNotifyEmailStatus();             
                $emailTemplate = Mage::getModel('core/email_template')->loadDefault('notification_template');
                $templateText = $emailTemplate->template_text;
                $webSite = Mage::app()->getWebsite()->getName();
                $credit = $this->getSmsCredit();
                preg_match_all('#{(.*)}#', $templateText, $match);
                
                $tempParams = array(
                '{site_name}'=>$webSite,
                '{present_credit}'=>$credit                 
                );
                foreach($match[0] as $var=>$value){ 
                    $templateText = preg_replace('#'.$value.'#',$tempParams[$value],$templateText);
                }
                $emailTemplate->template_text = $templateText;
                $emailTemplate->getProcessedTemplate($emailTemplateVariables);
                $emailTemplate->setSenderName($senderName);
                $emailTemplate->setSenderEmail($senderEmail);
                $emailTemplate->setTemplateSubject($emailTemplateVariables['text0']);
                $emailTemplate->send($email, '', $emailTemplateVariables);
            }           
        }
        else {
            $sendinSwitch->saveConfig('sendinblue/Sendin_Notify_Cron_Executed', 0, 'default', 0);
        }       
        Mage::getModel('core/session')->addSuccess(Mage::helper('sendinblue')->__('Notification mail has been sent'));
    }

    /**
     * show  SMS  credit from Sendinblue.
     */
    public function getSmsCredit()
    {
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $userCurrentPlanInfo = $psmailinObj->getAccount();
        if(isset($userCurrentPlanInfo['data'])) {
            foreach($userCurrentPlanInfo['data'] as $planData) {
                if(isset($planData['plan_type']) && $planData['plan_type'] == 'SMS') {
                    return $planData['credits'];
                }               
            }
        }
    }

    /**
     * Method is used to send test email to the user.
     */
    public function sendTestMail($email)
    {
        $localeCode = Mage::app()->getLocale()->getLocaleCode();
        $emailTemplateVariables = array();

        $emailTemplateVariables['text0'] = '[Sendinblue SMTP] test email';
        $senderName = 'SendinBlue';
        $senderEmail = 'contact@sendinblue.com';

        if ($localeCode == 'fr_FR') {
            $emailTemplateVariables['text0'] = '[SendinBlue SMTP] e-mail de test';
            $senderName = 'SendinBlue';
            $senderEmail = 'contact@sendinblue.com';
        }

        try {
            $emailTemplate = Mage::getModel('core/email_template')->loadDefault('custom_template');
            $emailTemplate->getProcessedTemplate($emailTemplateVariables);
            $emailTemplate->setSenderName($senderName);
            $emailTemplate->setSenderEmail($senderEmail);
            $emailTemplate->setTemplateSubject($emailTemplateVariables['text0']);
            return $emailTemplate->send($email, '', $emailTemplateVariables);
        }
        catch(Exception $e) {
            
        }
    }

    /**
     *  This method is used to compare key and value 
     * return all value in array whose present in array key
    */
    public function mergeMyArray($one, $two, $email = "")
    {
        $emailData = $email ? array('EMAIL'=> $email) : array();
        if (count($one) > 0) {
            foreach($one as $k => $v) {
                $emailData[$k] = isset($two[$v])?str_replace(';',',', $two[$v]):'';
            }
        }
        return $emailData;
    }

    /**
     *  This method is used to fetch all users from the default customer table to list
     * them in the Sendinblue magento module.
    */
    public function getcustomers()
    {
        $configObj = Mage::getModel('core/config');
        $attributes = $this->allAttributesName();

        if (!is_dir(Mage::getBaseDir('media').'/sendinblue_csv')) {
            mkdir(Mage::getBaseDir('media').'/sendinblue_csv', 0777, true);
        }

        $fileName = 'ImportContact-'.time();
        $configObj->saveConfig('sendinblue/CsvFileName', $fileName);
        $handle = fopen(Mage::getBaseDir('media').'/sendinblue_csv/'.$fileName.'.csv', 'w+');
        $headRow = array_keys($attributes);
        array_splice($headRow, 0, 0, 'EMAIL');
        fwrite($handle, implode(';', $headRow)."\n");

        $customersCollection = Mage::getModel('customer/customer')->getCollection()
        ->addAttributeToSelect(array('email', 'firstname', 'lastname', 'created_in'))
        ->joinAttribute('country_id', 'customer_address/country_id', 'default_billing', null, 'left')
        ->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left')
        ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left')
        ->joinAttribute('street', 'customer_address/street', 'default_billing', null, 'left')
        ->joinAttribute('postcode', 'customer_address/postcode', 'default_billing', null, 'left')
        ->joinAttribute('region', 'customer_address/region', 'default_billing', null, 'left')
        ->joinAttribute('city', 'customer_address/city', 'default_billing', null, 'left');

        $customers = array();
        foreach ($customersCollection as $customerCollection) {
            $customer = $customerCollection->getData();
            $email = $customer['email'];
            $customers[$email]['EMAIL'] = $email;
            foreach ($attributes as $alias => $attribute) {
                $customers[$email][$alias] = isset($customer[$attribute]) ? $customer[$attribute] : '';
            }
            $customers[$email]['CLIENT'] = $customer['entity_id'] > 0 ? 1 : 0;
            if (!empty($customer['country_id']) && !empty($customer['telephone'])) {
                $customers[$email]['SMS'] = $this->checkMobileNumber($customer['telephone'], $this->getCountryCode($customer['country_id']));
            }
        }

        $totalCount = 0;

        $subscribersCollection = Mage::getResourceModel('newsletter/subscriber_collection')->showStoreInfo()->addFieldToFilter('subscriber_status', array('eq' => 1));
        foreach ($subscribersCollection->getData() as $subscriber) {
            $contact = array();
            $email = $subscriber['subscriber_email'];
            $contact['EMAIL'] = $email;
            //Registered Users
            if (isset($customers[$email])) {
                $contact = $customers[$email];
            }
            //Non-registered users i.e users only subscribed for newsletter
            else {
                foreach ($attributes as $alias => $attribute) {
                    $contact[$alias] = isset($subscriber[$attribute]) ? $subscriber[$attribute] : '';
                    $contact['CLIENT'] = 0;
                    $contact['MAGENTO_LANG'] = Mage::getModel('core/store')->load($subscriber['store_id'])->getName();
                }
            }

            $row = array();
            foreach ($headRow as $column) {
                $row[] = $contact[$column];
            }
            fwrite($handle, str_replace("\n", ' ', implode(';', $row))."\n");
            ++$totalCount;
        }
        fclose($handle);

        return $totalCount;
    }

     /**
     *  This method is count all distinct record in customer and newsletter emails.
     */
    public function getCustAndNewslCount()
    {
        $prefix = Mage::getConfig()->getTablePrefix();        
        $db = Mage::getSingleton('core/resource')->getConnection('core/write');
        $query = "SELECT COUNT( * ) c
                    FROM (
                    SELECT cu.email
                    FROM ". $prefix ."customer_entity cu
                    UNION
                    SELECT n.subscriber_email
                    FROM ". $prefix ."newsletter_subscriber n) x ";
        $countAllRec = $db->fetchAll($query);
        return !empty($countAllRec['0']['c']) ? $countAllRec['0']['c'] : 0;
    }
    /**
     *  This method is used to fetch all users from the default newsletter table to list
     * them in the Sendinblue magento module.
     */
    public function getNewsletterSubscribe($start, $perPage)
    {
        $prefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_write');
        $customerAddressCollection = Mage::getModel('customer/address');
        $customerAddressData = array();
        $allData = array();
        $query = "select email from ". $prefix ."customer_entity
                union
                select subscriber_email from ". $prefix ."newsletter_subscriber limit $start , $perPage";

        if (count($readConnection->fetchAll($query)) > 0) {
            foreach ($readConnection->fetchAll($query) as $emailValue) {
                $email = !empty($emailValue['email']) ? $emailValue['email'] : '';
                $customerAddressData['email'] = $email;
                $customerAddressData['SMS'] = '';

                $custTable = $resource->getTableName('customer/entity');
                $queryTb = $readConnection->select()->from($custTable)->where('email=?', $email);
                $rowData = $readConnection->fetchAll($queryTb);

                $customerId = !empty($rowData['0']['entity_id']) ? $rowData['0']['entity_id'] : '';
                if (!empty($customerId)) {
                    $collectionAddress = $customerAddressCollection->getCollection()->addAttributeToSelect('telephone')->addAttributeToSelect('country_id')->addAttributeToFilter('parent_id',(int)$customerId);
                    $customerAddress = array();
                    foreach ($collectionAddress as $customerPhno) {
                        $customerAddress = $customerPhno->getData();
                        if (!empty($customerAddress['telephone']) && !empty($customerAddress['country_id'])) {
                            $countryCode = $this->getCountryCode($customerAddress['country_id']);
                            $customerAddressData['SMS'] = $this->checkMobileNumber($customerAddress['telephone'], $countryCode);
                        }
                    }
                    $customerAddressData['client'] = 1;
                } else {
                    $customerAddressData['client'] = 0;
                }
                
                $newsTable = $resource->getTableName('newsletter/subscriber');
                $queryTable = $readConnection->select()->from($newsTable)->where('subscriber_email=?', $email);
                $customerSubscribe = $readConnection->fetchAll($queryTable);

                $subsStatus = !empty($customerSubscribe[0]['subscriber_status']) ? $customerSubscribe[0]['subscriber_status'] : 0;
                if ($subsStatus == 1){
                    $customerAddressData['subscriber_status'] = 1;
                } else {
                    $customerAddressData['subscriber_status'] = 0;
                }
                $allData[] = $customerAddressData;
            }
        }
        return $allData;
    }


    /**
     *  This method is used to fetch total count unsubscribe users from the default newsletter table to list
     * them in the Sendinblue magento module.
    */
    public function getNewsletterUnSubscribeCount()
    {
        $prefix = Mage::getConfig()->getTablePrefix();
        $db = Mage::getSingleton('core/resource')->getConnection('core/write');
        $query = "SELECT COUNT( email ) as email FROM ". $prefix ."customer_entity";
        $querySecond = "SELECT COUNT( subscriber_email ) as email
                        FROM ". $prefix ."newsletter_subscriber where subscriber_status = 1 AND customer_id > 0";
        $querythird = "SELECT COUNT( subscriber_email ) as email
                        FROM ". $prefix ."newsletter_subscriber where subscriber_status != 1 AND customer_id = 0";
        $countCust = $db->fetchAll($query);
        $countUnsubsCust = $db->fetchAll($querySecond);
        $countUnsubs = $db->fetchAll($querythird);

        $custAll = !empty($countCust['0']['email']) ? $countCust['0']['email'] : 0;
        $allsubsUser = !empty($countUnsubsCust['0']['email']) ? $countUnsubsCust['0']['email'] : 0;
        $UnsNl = !empty($countUnsubs['0']['email']) ? $countUnsubs['0']['email'] : 0;
        return $totalUns =  ($custAll + $UnsNl) - $allsubsUser; 
    }

    /**
     *  This method is used to fetch total count subscribe users from the default newsletter table to list
     * them in the Sendinblue magento module.
    */
    public function getNewsletterSubscribeCount()
    {
        $coreResource = Mage::getSingleton('core/resource');
        $tableNewsletter = $coreResource->getTableName('newsletter/subscriber');
        $readDbObject = Mage::getSingleton("core/resource")->getConnection("core_read");
        $queryUnSubscribCounter = $readDbObject->select()
            ->from($tableNewsletter,array('totalvalue' => 'COUNT(*)'))            
            ->where("subscriber_status = 1");
        $stmtUnSubscribCounter = $readDbObject->query($queryUnSubscribCounter);
        $unSubscribCounter = $stmtUnSubscribCounter->fetch();
        return $unSubscribCounter['totalvalue'];
    }

    /**
     * This method is used to check the subscriber's newsletter subscription status in Sendinblue
     */
    public function checkUserSendinStatus($result)
    { 
        $userStatus = array();
        foreach ($result as $subscriber) {
            $userStatus[] = $subscriber['email'];
        }
        $allUsers = array('users' => $userStatus );
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $usersBlackListData = $psmailinObj->getUsersBlacklistStatus($allUsers);     
        return $usersBlackListData;

    }

    /**
     * Fetches the SMTP and order tracking details
    */
    public function trackingSmtp()
    {
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $smtpDetails = $psmailinObj->getSmtpDetails();
        return $smtpDetails;
    }

    public function removeOldEntry()
    {
        $sendinSwitch = Mage::getModel('core/config');
        $sendinSwitch->saveConfig('sendinblue/smtp/status', '');
        $sendinSwitch->saveConfig('sendinblue/smtp/authentication', '');
        $sendinSwitch->saveConfig('sendinblue/smtp/username', '');
        $sendinSwitch->saveConfig('sendinblue/smtp/password', '');
        $sendinSwitch->saveConfig('sendinblue/smtp/host', '');
        $sendinSwitch->saveConfig('sendinblue/smtp/port', '');
        $sendinSwitch->saveConfig('sendinblue/smtp/ssl', '');
        $sendinSwitch->saveConfig('sendinblue/smtp/option', '');
        $sendinSwitch->saveConfig('sendinblue/tracking/code', '');
        $sendinSwitch->saveConfig('sendinblue/tracking/automationscript', '');
        $sendinSwitch->saveConfig('sendinblue/tracking/abandonedcartstatus', '');
        $sendinSwitch->saveConfig('sendinblue/automation/enabled', '');
        $sendinSwitch->saveConfig('sendinblue/automation/key', '');

    }
    
    protected function _uninstallResourceDb($version)
    {
        Mage::dispatchEvent('module_uninstall', array('resource' => $this->_resourceName));        
        $this->_modifyResourceDb(self::TYPE_DB_UNINSTALL, $version, '');
        return $this;
    }

    /**
     *  This method is used to fetch all subscribe users from the default customer table to list
     * them in the Sendinblue magento module.
     */
    public function smsCampaignList()
    {
        $customerAddressData = array();
        $attributesName = $this->allAttributesName();
        $collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('email')->addAttributeToSelect('firstname')->addAttributeToSelect('lastname')->addAttributeToSelect('created_in');
        $customerAddressCollection = Mage::getModel('customer/address');

        foreach ($collection as $customer) {
            $responceData = array();
            $customerData = array();

            $customerData = $customer->getData();
            $email  = $customerData['email'];
            $customerId = $customerData['entity_id'];

            $collectionAddress = $customerAddressCollection->getCollection()->addAttributeToSelect('telephone')->addAttributeToSelect('country_id')->addAttributeToSelect('company')->addAttributeToSelect('street')->addAttributeToSelect('postcode')->addAttributeToSelect('region')->addAttributeToSelect('city')->addAttributeToFilter('parent_id',(int)$customerId);
            $telephone = '';
            $customerAddress = array();
            foreach ($collectionAddress as $customerPhno) {
                $customerAddress = $customerPhno->getData();
                if (!empty($customerAddress['telephone']) && !empty($customerAddress['country_id'])) {
                    $countryCode = $this->getCountryCode($customerAddress['country_id']);
                    $customerAddress['telephone'] = $this->checkMobileNumber($customerAddress['telephone'], $countryCode);    
                }
                $customerAddress['client'] = $customerId > 0 ? 1 : 0;
            }
            $customerAddressData[$email] = array_merge($customerData, $customerAddress);
        }
        $newsLetterData = array();
        $newsletter = Mage::getResourceModel('newsletter/subscriber_collection')->addFieldToFilter('subscriber_status', array('eq' => 1))->load();
        $count = 0;
        
        foreach ( $newsletter->getItems() as $subscriber) {
            $subsdata = $subscriber->getData();
            $subscriberEmail = $subsdata['subscriber_email'];
            $subscriberStatus = $subsdata['subscriber_status'];
            if ( !empty($customerAddressData[$subscriberEmail]) ) {
                $customerAddressData[$subscriberEmail]['email'] = $subscriberEmail;
                $responceData[$count] = $this->mergeMyArray($attributesName, $customerAddressData[$subscriberEmail]);
                $responceData[$count]['EMAIL'] = $subscriberEmail;
                $responceData[$count]['subscriber_status'] = $subscriberStatus;
            }
            else {
                $newsLetterData['client'] = $subsdata['customer_id']>0?1:0;
                $responceData[$count] = $this->mergeMyArray($attributesName, $newsLetterData);
                $responceData[$count]['EMAIL'] = $subscriberEmail;
                $responceData[$count]['subscriber_status'] = $subscriberStatus;
                $responceData[$count]['STORE_ID'] = $subsdata['store_id'];
            }
            $count++;
        }
        
        $i = 0;
        $data = array();
        foreach($responceData as $result) {                 
            if(!empty($result['SMS'])) { 
                $data[$i]= $result; 
            }
            $i++;
        }
        
        return json_encode($data);
    }
    
    /**
    * API config value from SendinBlue.
    */
    public function getApiConfigValue()
    {
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $valueConfig = $psmailinObj->getPluginConfig();
        return $valueConfig;
    }

    /**
    * Send template email by sendinblue for newsletter subscriber user  .
    */
    public function sendWsTemplateMail($to, $templateId = false)
    {
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        $sendinConfirmType = $this->getSubscribeConfirmType();
        $doubleoptinTemplateId = $this->getDoubleoptinTemplateId();
        if (empty($sendinConfirmType) || $sendinConfirmType == 'nocon') {
            return false;
        }

        if (!$templateId) {
            if ($sendinConfirmType == 'simplemail') {
                $templateIdValue = $this->getTemplateId();
                $templateId = !empty($templateIdValue) ? $templateIdValue : '';// should be the campaign id of template created on mailin. Please remember this template should be active than only it will be sent, otherwise it will return error.
                
                $simpleEmailData = array(
                    "id" => intval($templateId),
                    "to" => $to,
                    "cc" => "",
                    "bcc" => "",
                    "attr" => "",
                    //"headers" => array("Content-Type"=> "text/html;charset=iso-8859-1", "X-Mailin-tag"=>$transactional_tags )
                );  
                return $psmailinObj->sendTransactionalTemplate($simpleEmailData);
            }

            $pathResponce = '';
            $emailUser = base64_encode($to);
            $pathResponce = Mage::getBaseUrl().'admin/ajax/mailResponce?value='.base64_encode($to);
            if ($sendinConfirmType == 'doubleoptin' && $doubleoptinTemplateId == '') {      
                return $this->defaultDoubleoptinTemp($to, $pathResponce);
            }
            else if ($sendinConfirmType == 'doubleoptin' && $doubleoptinTemplateId > 0) {
                $templateId = $doubleoptinTemplateId;
                $senderName = 'SendinBlue';
                $senderEmail = 'contact@sendinblue.com';
                $doubleOptinRedirectUrlCheck = $this->getOptinRedirectUrlCheck();
                $data = array(
                    'id' => $templateId
                );
                $response = $psmailinObj->getCampaignV2($data);        
               
                if($response['code'] == 'success') {
                    $htmlContent = $response['data'][0]['html_content'];
                    if (trim($response['data'][0]['subject']) != '') {
                        $subject = trim($response['data'][0]['subject']);
                    }
                    if (($response['data'][0]['from_name'] != '[DEFAULT_FROM_NAME]') &&
                        ($response['data'][0]['from_email'] != '[DEFAULT_FROM_EMAIL]') &&
                        ($response['data'][0]['from_email'] != '')) {
                        $senderName = $response['data'][0]['from_name'];
                        $senderEmail = $response['data'][0]['from_email'];
                    }
                    $transactionalTags = $response['data'][0]['campaign_name'];
                }
                $doubleoptinRedirectUrl = Mage::getBaseUrl();
                $sendinFinalConfirmEmail = $this->getSendinFinalConfirmEmail();
                $getFinalTemplateId = $this->getFinalTemplate();
                $searchValue = "({{\s*doubleoptin\s*}})";

                $from = array($senderEmail, $senderName);
                $htmlContent = str_replace('{title}', $subject, $htmlContent);
                $htmlContent = str_replace('https://[DOUBLEOPTIN]', $pathResponce, $htmlContent);
                $htmlContent = str_replace('http://[DOUBLEOPTIN]', $pathResponce, $htmlContent);
                $htmlContent = str_replace('https://{{doubleoptin}}', $pathResponce, $htmlContent);
                $htmlContent = str_replace('http://{{doubleoptin}}', $pathResponce, $htmlContent);
                $htmlContent = str_replace('https://{{ doubleoptin }}', $pathResponce, $htmlContent);
                $htmlContent = str_replace('http://{{ doubleoptin }}', $pathResponce, $htmlContent);
                $htmlContent = str_replace('[DOUBLEOPTIN]', $pathResponce, $htmlContent);
                $htmlContent = preg_replace($searchValue, $pathResponce, $htmlContent);
                $headers = array("Content-Type"=> "text/html;charset=iso-8859-1", "X-Mailin-tag"=>$transactionalTags );
                $sendMailData = array( "to" => array("$to" => ""),
                    "from" => $from,
                    "subject" => $subject,
                    "html" => $htmlContent,
                    "attachment" => array(),
                    "headers" => $headers,                  
                );
                return $psmailinObj->sendEmail($sendMailData);
            }
        }
        else
        {
            $reConfirmEmail = array(
                "id" => intval($templateId),
                "to" => $to,
                "cc" => "",
                "bcc" => "",
                "attr" => "",
                //"headers" => array("Content-Type"=> "text/html;charset=iso-8859-1", "X-Mailin-tag"=>$transactional_tags )
            );  
            return $psmailinObj->sendTransactionalTemplate($reConfirmEmail);
        }       
    }
    
    /**
    * send double optin template and manage.
    */
    public function defaultDoubleoptinTemp($subscriberEmail, $doubleoptinUrl)
    {
        $localeCode = Mage::app()->getLocale()->getLocaleCode();
        $emailTemplateVariables = array();

        $emailTemplateVariables['text0'] = 'Please confirm your subscription';
        $senderName = 'SendinBlue';
        $senderEmail = 'contact@sendinblue.com';

        if ($localeCode == 'fr_FR') {
            $emailTemplateVariables['text0'] = 'Confirmez votre inscription';
            $senderName = 'SendinBlue';
            $senderEmail = 'contact@sendinblue.com';
        }
        try {
            $emailTemplate = Mage::getModel('core/email_template')->loadDefault('doubleoptin_template');
            $templateText = $emailTemplate->template_text;
            $webSite = Mage::app()->getWebsite()->getName();
            preg_match_all('#{(.*)}#', $templateText, $match);

            $templateData = array(
                '{double_optin}'=>$doubleoptinUrl,
                '{site_name}'=> $webSite            
            );
            foreach($match[0] as $var=>$value){ 
                $templateText = preg_replace('#'.$value.'#',$templateData[$value],$templateText);
            }
            $emailTemplate->template_text = $templateText;
            $emailTemplate->getProcessedTemplate($emailTemplateVariables);
            $emailTemplate->setSenderName($senderName);
            $emailTemplate->setSenderEmail($senderEmail);
            $emailTemplate->setTemplateSubject($emailTemplateVariables['text0']);
            return $emailTemplate->send($subscriberEmail, '', $emailTemplateVariables);
        }
        catch(Exception $e) {           
        }           
    }

    /**
    * Get all temlpate list id by sendinblue.
    */
    public function templateDisplay()
    {
        $listData = array();
        $listData['show'] = 'ALL';
        $listData['messageType'] = 'template';
        $params['api_key'] = $this->apiKey;
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        return $psmailinObj->getCampaignsV2($listData);
    }

    /**
    * Get getCountryCode from sendinblue_country table,
    */
    public function getCountryCode($countryids)
    {
        $tableCountry = Mage::getSingleton('core/resource')->getTableName('sendinblue_country_codes');
        $readDbObject = Mage::getSingleton("core/resource")->getConnection("core_read");

        $queryCountryCode = $readDbObject->select()
            ->from($tableCountry,array('country_prefix'))            
            ->where("iso_code = ?", $countryids);
        $stmtCountryCode = $readDbObject->query($queryCountryCode);
        $countryPrefixData = $stmtCountryCode->fetch();

        $countryPrefix = $countryPrefixData['country_prefix'];
        return $countryPrefix;
    }

    /**
    * Import transactional data,
    */
    public function importTransactionalData($email, $attributesValues, $getUserList)
    {
        $userData = array();
        $userData['email'] = $email;
        $userData['attributes']  = $attributesValues;
        $userData['listid'] = (is_array($getUserList)) ? $getUserList : array($getUserList);
        $params['api_key'] = $this->apiKey;
        
        $psmailinObj = Mage::getModel('sendinblue/psmailin', $params);
        return $psmailinObj->createUpdateUser($userData);
    }
    
    /**
    * check port 587 open or not, for using Sendinblue smtp service.
    */
    public function checkPortStatus()
    {
        $relay_port_status = @fsockopen('smtp-relay.sendinblue.com', 587);
        if (!$relay_port_status) {
            return 0;
        }
    }
}
