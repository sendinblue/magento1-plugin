<?php
/**
* @author Sendinblue plateform <contact@sendinblue.com>
* @copyright  2013-2014 Sendinblue
* URL:  https:www.sendinblue.com
* Do not edit or add to this file if you wish to upgrade Sendinblue Magento plugin to newer
* versions in the future. If you wish to customize Sendinblue magento plugin for your
* needs then we can't provide a technical support.
**/

class Sendinblue_Sendinblue_Adminhtml_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
        $params = Mage::app()->getRequest()->getParams();
        $params = empty($params) ? array() : $params;
        if (isset($params['sendin_apikey']) && $params['sendin_apikey'] != '') {
            $this->CreateFolderCaseTwo();
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/config');
    }

    public function campaignAction()
    {
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) { 
                Mage::throwException($this->__('Invalid form data.'));
            }

            $sendinSwitch = Mage::getModel('core/config');
            $sendinSwitch->saveConfig('sendinblue/sms/campaign', $postData['campaignSetting']);
            $rspMsg = $this->__('Your setting has been successfully saved');
            Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
            Mage::app()->getResponse()->setBody($rspMsg);
        }
        catch (Exception $exception) {
           $this->__($exception->getMessage());
        }
    }

    public function orderAction()
    {
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) { 
                Mage::throwException($this->__('Invalid form data.'));
            }

            $sendinSwitch = Mage::getModel('core/config');
            $sendinSwitch->saveConfig('sendinblue/sms/order', $postData['orderSetting']);
           $rspMsg = $this->__('Your setting has been successfully saved');
            Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
            Mage::app()->getResponse()->setBody($rspMsg);
        }
        catch (Exception $exception) {
           $this->__($exception->getMessage());
        }
    }

    public function creditAction()
    {
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $sendinSwitch = Mage::getModel('core/config');
            $sendinSwitch->saveConfig('sendinblue/sms/credit', $postData['sms_credit']);
            $respMsg = $this->__('Your setting has been successfully saved');
            Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
            Mage::app()->getResponse()->setBody($rspMsg);
        }
        catch (Exception $exception) {
            $this->__($exception->getMessage());
        }
    }

    public function shipingAction()
    {
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $sendinSwitch = Mage::getModel('core/config');
            $sendinSwitch->saveConfig('sendinblue/sms/shiping', $postData['shipingSetting']);
            $respMsg = $this->__('Your setting has been successfully saved');
            Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
            Mage::app()->getResponse()->setBody($rspMsg);
        }
        catch (Exception $exception) {
           $this->__($exception->getMessage());
        }
    }

    public function codepostAction()
    {
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $sendinSwitch = Mage::getModel('core/config');
            $sendinSwitch->saveConfig('sendinblue/tracking/code', $postData['script']);
            $sendinSwitch->saveConfig('sendinblue/improt/history', $postData['script']);
            $respMsg = $this->__('Your setting has been successfully saved');
            Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
            Mage::app()->getResponse()->setBody($rspMsg);
        }
        catch (Exception $exception) {
           $this->__($exception->getMessage());
        }
    }

    public function emptySubsUserToSendinblueAction()
    {
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
               Mage::throwException($this->__('Invalid form data.'));
            }
            if ($postData['proc_success'] != '') {
                $fileName = Mage::getStoreConfig('sendinblue/CsvFileName');
                $handle = fopen(Mage::getBaseDir('media').'/sendinblue_csv/'.$fileName.'.csv', 'w+');
                $keyValue = array();
                $keyValue[] = '';           
                fputcsv($handle, $keyValue);
                fclose($handle);
            }
        }
        catch (Exception $exception) {
           $this->__($exception->getMessage());
        }
    }

    public function mailResponceAction()
    {
        $postData = $this->getRequest()->getParams();
        try {
            if (empty($postData)) {
               Mage::throwException($this->__('Invalid form data.'));
            }

            $userEmail = base64_decode($postData['value']);
            if ($userEmail != '') {         
                $newsletter = Mage::getResourceModel('newsletter/subscriber_collection')->addFieldToFilter('subscriber_email', array('eq' => $userEmail))->load();;
                foreach ($newsletter->getItems() as $subscriber) {
                    $subScriberData = $subscriber->getData();
                    $subScriberEmail = $subScriberData['subscriber_email'];
                    $subScriberStatus = $subScriberData['subscriber_status'];
                }

                $sendinModule = Mage::getModel('sendinblue/sendinblue');
                if (!empty($subScriberEmail) && $subScriberStatus == 1) {
                    $listId = $sendinModule->getUserlists();
                    $doubleOptinId = Mage::getStoreConfig('sendinblue/SendinOptinListId');
                    $apiDetails['api_key'] = $sendinModule->getApiKey();
                    $objPsmailin = Mage::getModel('sendinblue/psmailin',$apiDetails);

                    $userData = array( "email" => $subScriberEmail,
                        "attributes" => array("DOUBLE_OPT-IN"=>1),
                        "blacklisted" => 0,
                        "listid" => (is_array($listId)) ? $listId : array($listId),
                        "listid_unlink" => array($doubleOptinId),
                        "blacklisted_sms" => 0
                    );

                    $responce = $objPsmailin->createUpdateUser($userData);
                    $finalStatus = Mage::getStoreConfig('sendinblue/SendinFinalConfirmEmail');
                    if ($finalStatus === 'yes') {
                        $finalTemplateId = Mage::getStoreConfig('sendinblue/SendinTemplateFinal');
                        $sendinModule->sendWsTemplateMail($subScriberEmail, $finalTemplateId);
                    }

                    $urlStatus = $sendinModule->getOptinRedirectUrlCheck();

                    if ($urlStatus == 'yes') {
                        $urlValue = $sendinModule->getSendinDoubleoptinRedirectUrl();
                        $this->_redirectUrl($urlValue);
                    } else {
                        $configValue = Mage::getStoreConfig('web/secure/base_url');
                        $this->_redirectUrl($configValue);
                    }
                }
            }
        }
        catch (Exception $exception) {
           $this->__($exception->getMessage());
        }
    }
    
    public function emptyImportOldOrderAction()
    {
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
               Mage::throwException($this->__('Invalid form data.'));
            }
            
            if ($postData['proc_success'] != '') {
                $fileName = Mage::getStoreConfig('sendinblue/CsvFileName');
                $handle = fopen(Mage::getBaseDir('media').'/sendinblue_csv/'.$fileName.'.csv', 'w+');
                $keyValue = array();
                $keyValue[] = '';
                fputcsv($handle, $keyValue);
                fclose($handle);
            }
        }
        catch (Exception $exception) {
           $this->__($exception->getMessage());
        }
    }

    public function orderhistoryAction()
    {
        $sendinModule = Mage::getModel('sendinblue/sendinblue');
        $configObj = Mage::getModel('core/config');
        $apiDetails['api_key'] = $sendinModule->getApiKey();
        $psmailinObj = Mage::getModel('sendinblue/psmailin',$apiDetails);

        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            if ($postData['history_status'] == 1) {
                $value = $sendinModule->trackingSmtp();
                $dateValue = $sendinModule->getApiConfigValue();
                if (!is_dir(Mage::getBaseDir('media').'/sendinblue_csv')) {
                    mkdir(Mage::getBaseDir('media').'/sendinblue_csv', 0777, true);
                }
                $fileName = rand();
                $configObj->saveConfig('sendinblue/CsvFileName', $fileName);
                $handle = fopen(Mage::getBaseDir('media').'/sendinblue_csv/'.$fileName.'.csv', 'w+');
                fwrite($handle, 'EMAIL,ORDER_ID,ORDER_PRICE,ORDER_DATE'.PHP_EOL);
                
                $prefix = Mage::getConfig()->getTablePrefix();
                $tabName = $prefix .'newsletter_subscriber';
                $collection = Mage::getModel('customer/customer')->getCollection();
                $collection->addNameToSelect()
                    ->joinTable($tabName, 'customer_id = entity_id', array('subscriber_status'), '{{table}}.subscriber_status = 1');

                $salesOrderColection = Mage::getModel('sales/order');                
                foreach ($collection as $customer) {
                    $customerId = $customer->getData('entity_id');
                    $totalOrders = $salesOrderColection->getCollection()->addFieldToFilter('customer_id', $customerId); 
                    foreach($totalOrders as $orderData) {
                        if(count($orderData) > 0) {
                            if (isset($dateValue['data']['date_format']) && $dateValue['data']['date_format'] == 'dd-mm-yyyy') {
                                $orderDate = date('d-m-Y', strtotime($orderData['created_at']));
                            }
                            else {
                                $orderDate = date('m-d-Y', strtotime($orderData['created_at']));
                            }
                            $historyData= array();
                            $historyData[] = array($orderData['customer_email'], $orderData['increment_id'], $orderData['grand_total'], $orderDate);
                            foreach ($historyData as $line) {
                                fputcsv($handle, $line);
                            }
                        }
                    }
                }

                fclose($handle);
                $getUserLists = $sendinModule->getUserlists();
                $list = str_replace('|', ',', $getUserLists);
                $list = (preg_match('/^[0-9,]+$/', $list)) ? $list : '';
                $fileName = Mage::getStoreConfig('sendinblue/CsvFileName');
                $importData = array();
                $importData['url'] = Mage::getBaseUrl('media').'sendinblue_csv/'.$fileName.'.csv';
                $importData['listids'] = array($list);
                $importData['notify_url'] = Mage::getBaseUrl().'sendinblue/ajax/emptyImportOldOrder';
                /**
                * List id should be optional
                */
                
                $psmailinObj->importUsers($importData);
                $configObj->saveConfig('sendinblue/improt/history', 0);
                if($postData['langvalue'] == 'fr_FR') {
                    $msg = 'Historique des commandes a été importé avec succès.';
                }
                else {
                    $msg = 'Order history has been import successfully';
                }
                Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                Mage::app()->getResponse()->setBody($msg);
            }
        }
        catch (Exception $exception) {
           $this->__($exception->getMessage());
        }
    }

    public function smtppostAction()
    {
        $sendinModule = Mage::getModel('sendinblue/sendinblue');
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            } else {
                $sendinSwitch = Mage::getModel('core/config');
                $getKey = $sendinModule->getApiKey();
                $apiKeyStatus = $sendinModule->checkApikey($getKey);
                if (empty($apiKeyStatus['error'])) {
                    $sendinSwitch->saveConfig('sendinblue/smtp/status', $postData['smtptest']);
                    $smtpResponse = $sendinModule->trackingSmtp(); // get tracking code

                    if (isset($smtpResponse['data']['relay_data']['status']) && $smtpResponse['data']['relay_data']['status'] == 'enabled') {
                        $sendinSwitch->saveConfig('sendinblue/smtp/authentication', 'crammd5', 'default', 0);
                        $sendinSwitch->saveConfig('sendinblue/smtp/username', $smtpResponse['data']['relay_data']['data']['username'], 'default', 0);
                        $sendinSwitch->saveConfig('sendinblue/smtp/password', $smtpResponse['data']['relay_data']['data']['password'], 'default', 0);
                        $sendinSwitch->saveConfig('sendinblue/smtp/host', $smtpResponse['data']['relay_data']['data']['relay'], 'default', 0);
                        $sendinSwitch->saveConfig('sendinblue/smtp/port', $smtpResponse['data']['relay_data']['data']['port'], 'default', 0);
                        $sendinSwitch->saveConfig('sendinblue/smtp/ssl', 'null', 'default', 0);
                        $sendinSwitch->saveConfig('sendinblue/smtp/option', 'smtp', 'default', 0);
                        $msg = $this->__('Your setting has been successfully saved');
                        Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                        Mage::app()->getResponse()->setBody($msg);
                    } else {
                        $sendinSwitch->saveConfig('sendinblue/smtp/status', 0);
                        $msg = $this->__('Your SMTP account is not activated and therefore you can\'t use Sendinblue SMTP. For more informations, please contact our support to: contact@sendinblue.com');
                        Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                        Mage::app()->getResponse()->setBody($msg);
                    }
                }
                elseif(isset($responce['error'])) {
                    $msg = $this->__('You have entered wrong api key');
                    Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                    Mage::app()->getResponse()->setBody($msg);
                }
            }
        }
        catch (Exception $exception) {
            $this->__($exception->getMessage());
        }
    }

    public function ajaxcontentAction()
    {
        $sendinModule = Mage::getModel('sendinblue/sendinblue');
        $postData = $this->getRequest()->getPost();

        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            } else {
                $localeCode = Mage::app()->getLocale()->getLocaleCode();
                if ($localeCode == 'fr_FR') {
                    $title1 = 'Inscrire le contact';
                    $title2 = 'Désinscrire le contact';
                    $title3 = 'Inscrire le sms';
                    $title4 = 'Désinscrire le sms';
                    $first = 'Première page';
                    $last = 'Dernière page';
                    $previous = 'Précédente';
                    $next = 'Suivante';
                    $yes = 'oui';
                    $no = 'non';
                } else {
                    $title1 = 'Unsubscribe the contact';
                    $title2 = 'Subscribe the contact';
                    $title3 = 'Unsubscribe the sms';
                    $title4 = 'Subscribe the sms';
                    $first = 'First';
                    $last = 'Last';
                    $previous = 'Previous';
                    $next = 'Next';
                    $yes = 'yes';
                    $no = 'no';
                }
                
                $page = (int)$postData['page'];
                $currentPage = $page;
                $page -= 1;
                $perPage = 20;
                $previousButton = true;
                $nextButton = true;
                $firstButton = true;
                $lastButton = true;
                $start = $page * $perPage;
                $count = $sendinModule->getCustAndNewslCount();
                $noOfPaginations = ceil($count / $perPage);
                if ($currentPage >= 7) {
                    $startLoop = $currentPage - 3;
                    if ($noOfPaginations > $currentPage + 3) {
                        $endLoop = $currentPage + 3;
                    }
                    elseif ($currentPage <= $noOfPaginations && $currentPage > $noOfPaginations - 6) {
                        $startLoop = $noOfPaginations - 6;
                        $endLoop   = $noOfPaginations;
                    } else {
                        $endLoop = $noOfPaginations;
                    }
                } 
                else {
                    $startLoop = 1;
                    if ($noOfPaginations > 7) {
                        $endLoop = 7;
                    } else {
                        $endLoop = $noOfPaginations;
                    }
                }

                $collection = $sendinModule->getNewsletterSubscribe($start, $perPage);
                $sendinUserStatus = $sendinModule->checkUserSendinStatus($collection);

                $sendinUserResult = isset($sendinUserStatus['data']) ? $sendinUserStatus['data'] : '';
                if (count($collection) > 0) {
                    $i = 1;
                    $message = '';
                    $skinUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
                    foreach ($collection as $subscriber) {
                        $email = isset($subscriber['email']) ? $subscriber['email'] : '';
                        $phone = isset($subscriber['SMS']) ? $subscriber['SMS'] : '';

                        $client = (!empty($subscriber['client']) > 0) ? $yes : $no ;
                        $showStatus = '';
                        $smsStatus = '';
                        if (isset($sendinUserResult[$email])) {
                            $emailBalanceValue = isset($sendinUserResult[$email]['email_bl']) ? $sendinUserResult[$email]['email_bl'] : '';

                            if ($emailBalanceValue === 1 || $sendinUserResult[$email] == null) {
                                $showStatus = 0;
                            }

                            if ($emailBalanceValue === 0) {
                                $showStatus = 1;
                            }
                            
                            $smsBalance = isset($sendinUserResult[$email]['sms_bl']) ? $sendinUserResult[$email]['sms_bl'] : '';
                            $smsExist = isset($sendinUserResult[$email]['sms_exist']) ? $sendinUserResult[$email]['sms_exist'] : '';
                            $subScriberTelephone = isset($subscriber['SMS']) ? $subscriber['SMS'] : '';

                            if ($smsBalance === 1 && $smsExist > 0) {
                                $smsStatus = 0;
                            } elseif ($smsBalance === 0 && $smsExist > 0) {
                                $smsStatus = 1;
                            } elseif ($smsExist <= 0 && empty($subScriberTelephone)) {
                                 $smsStatus = 2;
                            } elseif ($smsExist <= 0 && !empty($subScriberTelephone)) {
                                $smsStatus = 3;
                            }
                        }

                        if ($subscriber['subscriber_status'] == 1) { 
                            $imgMagento = '<img src="'.$skinUrl.'adminhtml/default/default/sendinblue/images/enabled.gif" >';
                        } else {
                            $imgMagento = '<img src="'.$skinUrl.'adminhtml/default/default/sendinblue/images/disabled.gif" >';
                        }

                        $smsStatus = $smsStatus >= 0 ? $smsStatus : '';

                        if ($smsStatus === 1) {
                            $imgSms = '<img src="'.$skinUrl.'adminhtml/default/default/sendinblue/images/enabled.gif" id="ajax_contact_status_'.$i.'" title="'.$title3.'" >';
                        } elseif ($smsStatus === 0) {
                            $imgSms = '<img src="'.$skinUrl.'adminhtml/default/default/sendinblue/images/disabled.gif" id="ajax_contact_status_'.$i.'" title="'.$title4.'" >';
                        } elseif ($smsStatus === 2 || $smsStatus === '') {
                                $imgSms = '';
                        } elseif ($smsStatus === 3) {
                                $imgSms = 'Not synchronized';
                        }

                        $showStatus = !empty($showStatus) ? $showStatus : '0';

                        if ($showStatus == 1) {
                            $imgSendinBlue = '<img src="'.$skinUrl.'adminhtml/default/default/sendinblue/images/enabled.gif" id="ajax_contact_status_'.$i.'" title="'.$title1.'" >';
                        }
                        else {
                            $imgSendinBlue = '<img src="'.$skinUrl.'adminhtml/default/default/sendinblue/images/disabled.gif" id="ajax_contact_status_'.$i.'" title="'.$title2.'" >';
                        }

                        $message .= '<tr  class="even pointer"><td class="a-left">'.$email.'</td><td class="a-left">'.$client.'</td><td class="a-left">'.$phone.'</td><td class="a-left">'.$imgMagento.'</td>
                            <td class="a-left"><a status="'.$showStatus.'" email="'.$email.'" class="ajax_contacts_href" href="javascript:void(0)">
                    '.$imgSendinBlue.'</a></td><td class="a-left last"><a status="'.$smsStatus.'" email="'.$email.'" class="ajax_sms_subs_href" href="javascript:void(0)">
                    '.$imgSms.'</a></td></tr>';
                        
                        $i++;
                    }
                }
                $messagePaging = '';
                $messagePaging .= '<tr><td colspan="7"><div class="pagination"><ul class="pull-left">';
                
                if ($firstButton && $currentPage > 1) {
                    $messagePaging .= '<li p="1" class="active">'.$first.'</li>';
                } elseif ($firstButton) {
                    $messagePaging .= '<li p="1" class="inactive">'.$first.'</li>';
                }

                if ($previousButton && $currentPage > 1) {
                    $previousValue = $currentPage - 1;
                    $messagePaging .= '<li p="'.$previousValue.'" class="active">'.$previous.'</li>';
                } elseif ($previousButton) {
                    $messagePaging .= '<li class="inactive">'.$previous.'</li>';
                }

                for ($i = $startLoop; $i <= $endLoop; $i++) {
                    if ($currentPage == $i) {
                        $messagePaging .= '<li p="'.$i.'" style="color:#fff;background-color:#000000;" class="active">'.$i.'</li>';
                    }
                    else {
                        $messagePaging .= '<li p="'.$i.'"  class="active">'.$i.'</li>';
                    }
                }

                if ($nextButton && $currentPage < $noOfPaginations) {
                    $nextValue = $currentPage + 1;
                    $messagePaging .= '<li p="'.$nextValue.'" class="active">'.$next.'</li>';
                } elseif ($nextButton) {
                    $messagePaging .= '<li class="inactive">'.$next.'</li>';
                }

                if ($lastButton && $currentPage < $noOfPaginations) {
                     $messagePaging .= '<li p="'.$noOfPaginations.'" class="active">'.$last.'</li>';
                } elseif ($lastButton) {
                    $messagePaging .= '<li p="'.$noOfPaginations.'" class="inactive">'.$last.'</li>';
                }

                if ($count != 0) {
                    Mage::app()->getResponse()->setBody($message . $messagePaging).'</td></tr>';

                }
            }
        }
        catch (Exception $exception) {
            $this->__($exception->getMessage());
        }
    }

    public function ajaxsmssubscribeAction()
    {
        $sendinModule = Mage::getModel('sendinblue/sendinblue');
        $apiDetails['api_key'] = $sendinModule->getApiKey();
        $psmailinObj = Mage::getModel('sendinblue/psmailin',$apiDetails);

        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            $email = $postData['email'];
            $userData = array();
            $userData['email'] = $email;
            $psmailinObj->userSmsUnsubscribed($userData);
        }
        catch (Exception $exception) {
            $this->__($exception->getMessage());
        }
    }

    public function ajaxupdateAction()
    {
        $postData = $this->getRequest()->getPost();
        $coreResource = Mage::getSingleton('core/resource');
        $tableCustomer = $coreResource->getTableName('customer/entity');
        $tableNewsletter = $coreResource->getTableName('newsletter/subscriber');
        $sendinModule = Mage::getModel('sendinblue/sendinblue');
        $attributesName = $sendinModule->allAttributesName();

        $readDbObject = Mage::getSingleton("core/resource")->getConnection("core_read");
        $writeDbObject = Mage::getSingleton("core/resource")->getConnection("core_write");

        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            
            $listId = str_replace(',', '|', $sendinModule->getUserlists());
            $postEmail = !empty($postData['email']) ? $postData['email'] : '';
            $postNewsLetter = !empty($postData['newsletter']) ? $postData['newsletter'] : '';
            $templateSubscribeStatus = ($postNewsLetter == 0) ? 1 : 3;
            
            $queryCustomerEntity = $readDbObject->select()
                ->from($tableCustomer, array('store_id', 'entity_id'))
                ->where("email = ?", $postEmail);
            $stmtCustomerEntity = $readDbObject->query($queryCustomerEntity);
            $customerData = $stmtCustomerEntity->fetch();
                    
            if (!empty($postEmail) && $postNewsLetter == 0) {

                $localeCode = Mage::app()->getLocale()->getLocaleCode();
                $emailSubscribeResponce = $sendinModule->emailSubscribe($postEmail);

                $customerAddress = array();
                if (isset($emailSubscribeResponce['code']) && $emailSubscribeResponce['code'] == 'failure' && $emailSubscribeResponce['message'] == 'User not exists') {
                    if (isset($customerData['entity_id']) != '') {
                        $collectionAddress = Mage::getModel('customer/address')->getCollection()->addAttributeToSelect('telephone')->addAttributeToSelect('country_id')->addAttributeToSelect('company')->addAttributeToSelect('street')->addAttributeToSelect('postcode')->addAttributeToSelect('region')->addAttributeToSelect('city')->addAttributeToFilter('parent_id',(int)$customerData['entity_id']);
                        $telephone = '';
                        foreach ($collectionAddress as $customerPhno) {
                            $customerAddress = $customerPhno->getData();
                            if (!empty($customerAddress['telephone']) && !empty($customerAddress['country_id'])) {
                                $countryCode = $sendinModule->getCountryCode($customerAddress['country_id']);
                                $customerAddress['telephone'] = $sendinModule->checkMobileNumber($customerAddress['telephone'], $countryCode);    
                            }
                        }
                        $customer = Mage::getModel("customer/customer");
                        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                        $customer->loadByEmail($postEmail); //load customer by email id
                        $customerName = $customer->getData();
                        $userLanguage = isset($customerName['created_in'])?$customerName['created_in'] : '';
                        $customerData = array_merge($customerAddress, $customerName);
                        $resp = $sendinModule->mergeMyArray($attributesName, $customerData);
                        $resp['CLIENT'] = 1;
                        $resp['MAGENTO_LANG'] = $userLanguage;
                        $responce = $sendinModule->emailAdd($postEmail, $resp, $postNewsLetter, $listId);
                    }
                    else {
                        $client = 0;
                        $customerData = array();
                        $subsdata = Mage::getModel('newsletter/subscriber')->loadByEmail($postEmail)->getData();
                        $resp = $sendinModule->mergeMyArray($attributesName, $subsdata);
                        $resp['CLIENT'] = $client;
                        $responce = $sendinModule->emailAdd($postEmail, $resp, $postNewsLetter, $listId);   
                    }
                }
                
                $querySubscriberEmail = $readDbObject->select()
                    ->from($tableNewsletter, array('subscriber_email'))
                    ->where("subscriber_email = ?", $postEmail);
                $stmtSubscriberEmail = $readDbObject->query($querySubscriberEmail);
                $customerDataNews = $stmtSubscriberEmail->fetch();

                if (!empty($customerData['entity_id']) && empty($customerDataNews['subscriber_email'])) {                          
                    $newsLetterData = array(
                            "store_id" => $customerData['store_id'],
                            "customer_id" => $customerData['entity_id'],
                            "subscriber_email" => $postEmail,
                            "subscriber_status" => 1,
                    );
                    $writeDbObject->insert($tableNewsletter, $data);
                }
                else {  
                    $costomerInformation = Mage::getModel('newsletter/subscriber')->loadByEmail($postEmail);
                    $costomerInformation->setStatus($templateSubscribeStatus);
                    $costomerInformation->setIsStatusChanged(true);
                    $costomerInformation->save();
                }

            }
            else{
                $responce = $sendinModule->emailDelete($postEmail);
                $costomerInformation = Mage::getModel('newsletter/subscriber')->loadByEmail($postEmail);

                if (!$costomerInformation->getStoreId()) {
                    $costomerInformation->setSubscriberEmail($postEmail);
                    $costomerInformation->setCustomerId($customerData['entity_id']);
                    $costomerInformation->setStoreId($customerData['store_id']);
                }
                $costomerInformation->setStatus($templateSubscribeStatus);
                $costomerInformation->setIsStatusChanged(true);
                $costomerInformation->save();
            }
        }
        catch (Exception $exception) {
            $this->__($exception->getMessage());
        }
    }
    
    public function ajaxordersmsAction($sender='', $message='', $number='')
    {  
      $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            $number = $postData['number'];
            $charone = substr($number, 0, 1);
            $chartwo = substr($number, 0, 2);
            if ($charone == '0' && $chartwo == '00') { 
                $number = $number;
            }

            if (isset($number)) {
                $adminUserModel = Mage::getModel('admin/user');
                $userCollection = $adminUserModel->getCollection()->load(); 
                $adminData = $userCollection->getData();
                $firstname = isset($adminData[0]['firstname']) ? $adminData[0]['firstname'] : '';
                $lastname = isset($adminData[0]['lastname']) ? $admin_data[0]['lastname'] : '';
                $characters = '1234567890';
                $referenceNumber = '';
                for ($i = 0; $i < 9; $i++) {
                    $referenceNumber .= $characters[rand(0, strlen($characters) - 1)];
                }

                $localeCode = Mage::app()->getLocale()->getLocaleCode();
                $orderDateFormat = ($localeCode == 'fr_FR') ? date('d/m/Y') : date('m/d/Y');
                $orderprice = rand(10, 1000);
                $totalPay = $orderprice.'.00'.' '.Mage::app()->getStore()-> getCurrentCurrencyCode();
                $firstName = str_replace('{first_name}', $firstname, $postData['message']);
                $lastName = str_replace('{last_name}', $lastname."\r\n", $firstName);
                $procuctPrice = str_replace('{order_price}', $totalPay, $lastName);
                $orderDate = str_replace('{order_date}', $orderDateFormat."\r\n", $procuctPrice);
                $msgbody = str_replace('{order_reference}', $referenceNumber, $orderDate);
                $smsData = array();
                $smsData['to'] = $number;
                $smsData['from'] = isset($postData['sender']) ? $postData['sender'] : '';
                $smsData['text'] = $msgbody;

                $sendSmsResponce = Mage::getModel('sendinblue/sendinblue')->sendSmsApi($smsData);
                if (isset($sendSmsResponce['status']) && $sendSmsResponce['status'] == 'OK') {
                Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                Mage::app()->getResponse()->setBody('OK');

                }
                else {
                    Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                    Mage::app()->getResponse()->setBody('KO');
                }
            }
        }
        catch (Exception $exception) {
            $this->__($exception->getMessage());
        }        
    }

    public function ajaxordershippedAction($sender='', $message='', $number='')
    {
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $number = $postData['number'];
            $charone = substr($number, 0, 1);
            $chartwo = substr($number, 0, 2);

            if ($charone == '0' && $chartwo == '00') {
                $number = $number;
            }

            if (isset($number)) {       
                $adminUserModel = Mage::getModel('admin/user');
                $userCollection = $adminUserModel->getCollection()->load(); 
                $adminData = $userCollection->getData();
                $firstname = isset($adminData[0]['firstname'])?$adminData[0]['firstname']:'';
                $lastname = isset($adminData[0]['lastname'])?$adminData[0]['lastname']:'';
                $characters = '1234567890';
                $referenceNumber = '';
                for ($i = 0; $i < 9; $i++) {
                    $referenceNumber .= $characters[rand(0, strlen($characters) - 1)];
                }

                $localeCode = Mage::app()->getLocale()->getLocaleCode();
                $orderDateFormat = ($localeCode == 'fr_FR') ? date('d/m/Y') :  date('m/d/Y') ;
                
                $orderprice = rand(10, 1000);
                $totalPay = $orderprice.'.00'.' '.Mage::app()->getStore()-> getCurrentCurrencyCode();
                $msgbody = $postData['message'];
                $firstName = str_replace('{first_name}', $firstname, $msgbody);
                $lastName = str_replace('{last_name}', $lastname."\r\n", $firstName);
                $procuctPrice = str_replace('{order_price}', $totalPay, $lastName);
                $orderDate = str_replace('{order_date}', $orderDateFormat."\r\n", $procuctPrice);
                $msgbody = str_replace('{order_reference}', $referenceNumber, $orderDate);
                $smsData = array();
                $smsData['to'] = $number;
                $smsData['from'] = !empty($postData['sender'])?$postData['sender']:'';
                $smsData['text'] = $msgbody;

                $sendSmsResponce = Mage::getModel('sendinblue/sendinblue')->sendSmsApi($smsData);   
                if (isset($sendSmsResponce['status']) && $sendSmsResponce['status'] == 'OK') {
                    Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                    Mage::app()->getResponse()->setBody('OK');

                }
                else {
                    Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                    Mage::app()->getResponse()->setBody('KO');
                }
            }
        
        }
        catch (Exception $exception) {
            $this->__($exception->getMessage());
        }        
    }

    public function ajaxsmscampaignAction($sender='', $message='', $number='')
    {
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            $number = $postData['number'];
            $charone = substr($number, 0, 1);
            $chartwo = substr($number, 0, 2);
            if ($charone == '0' && $chartwo == '00') {
                $number = $number;
            }
            if (isset($number)) {
                $adminUserModel = Mage::getModel('admin/user');
                $userCollection = $adminUserModel->getCollection()->load(); 
                $adminData = $userCollection->getData();
                $firstname = isset($adminData[0]['firstname'])?$adminData[0]['firstname']:'';
                $lastname = isset($adminData[0]['lastname'])?$adminData[0]['lastname']:'';
                $msgbody = $postData['message'];
                $firstName = str_replace('{first_name}', $firstname, $msgbody);
                $msgbody = str_replace('{last_name}', $lastname."\r\n", $firstName);
                $smsData = array();
                $smsData['to'] = $number;
                $smsData['from'] = !empty($postData['sender'])?$postData['sender']:'';
                $smsData['text'] = $msgbody;            
                $sendSmsResponce = Mage::getModel('sendinblue/sendinblue')->sendSmsApi($smsData);
                if (isset($sendSmsResponce['status']) && $sendSmsResponce['status'] == 'OK') {
                    Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                    Mage::app()->getResponse()->setBody('OK');
                }
                else {
                    Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                    Mage::app()->getResponse()->setBody('KO');
                }
            }
        }
        catch (Exception $exception) {
            $this->__($exception->getMessage());
        }        
    }
    
    public function automationpostAction()
    {
        $sendinModule = Mage::getModel('sendinblue/sendinblue');
        $postData = $this->getRequest()->getPost();
        try {
            if (empty($postData)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            else {
                $sendinSwitch = Mage::getModel('core/config');
                $getKey = $sendinModule->getApiKey();
                $apiKeyStatus = $sendinModule->checkApikey($getKey);
                if (empty($apiKeyStatus['error'])) {
                    $sendinSwitch->saveConfig('sendinblue/tracking/automationscript', $postData['script']);
                    if($postData['script']) {
                        $smtpResponse = $sendinModule->trackingSmtp(); // get tracking code
                        if (isset($smtpResponse['data']['marketing_automation']['key']) && $smtpResponse['data']['marketing_automation']['enabled'] == 1) {
                            $sendinSwitch->saveConfig('sendinblue/automation/enabled', $smtpResponse['data']['marketing_automation']['enabled'], 'default', 0);
                            $sendinSwitch->saveConfig('sendinblue/automation/key', $smtpResponse['data']['marketing_automation']['key'], 'default', 0);
                            $msg = $this->__('Your setting has been successfully saved');
                            Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                            Mage::app()->getResponse()->setBody($msg);
                        }
                        else {
                            $sendinSwitch->saveConfig('sendinblue/tracking/automationscript', 0);
                            $msg = $this->__("To activate Marketing Automation , please go to your Sendinblue's account or contact us at contact@sendinblue.com");
                            Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                            Mage::app()->getResponse()->setBody($msg);
                        }
                    }
                    else {
                        $msg = $this->__('Your setting has been successfully saved');
                        Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                        Mage::app()->getResponse()->setBody($msg); 
                    }
                }
                else {
                    $msg = $this->__('You have entered wrong api key');
                    Mage::app()->getResponse()->setHeader('Content-type', 'application/text');
                    Mage::app()->getResponse()->setBody($msg);
                }
            }
        }
        catch (Exception $exception) {
            $this->__($exception->getMessage());
        }
    }
}
