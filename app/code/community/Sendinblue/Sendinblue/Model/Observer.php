<?php
/**
* @author Sendinblue plateform <contact@sendinblue.com>
* @copyright  2013-2014 Sendinblue
* URL:  https:www.sendinblue.com
* Do not edit or add to this file if you wish to upgrade Sendinblue Magento plugin to newer
* versions in the future. If you wish to customize Sendinblue magento plugin for your
* needs then we can't provide a technical support.
**/
class Sendinblue_Sendinblue_Model_Observer
{
    protected static $fields = array();
    public function adminSubcriberDelete($observer)
    {
        $requestParameters = Mage::app()->getRequest()->getParams();
        if (isset($requestParameters['subscriber']) && count($requestParameters['subscriber'] > 0)) {
            $customerEmail = array();
            $newsLatterSubscriber = Mage::getModel('newsletter/subscriber');
            foreach ($requestParameters['subscriber'] as $costomerId) {
                $costomerData = $newsLatterSubscriber->load($costomerId)->toArray();
                $customerEmail[] = empty($costomerData['subscriber_email']) ? array() : $costomerData['subscriber_email'];
            }
            $customerEmails = implode('|', $customerEmail);
            $emailDeleteResponce = Mage::getModel('sendinblue/sendinblue')->emailDelete($customerEmails);
        }

        if (isset($emailDeleteResponce['data']['unsubEmails'])){
            Mage::getModel('core/session')->addSuccess(Mage::helper('sendinblue')->__('Total of '. count($emailDeleteResponce['data']['unsubEmails']) .' record(s) were Unsubscribed'));
        }
        return $this;
    }

    public function adminCustomerDelete($observer)
    {
        $requestParameters = Mage::app()->getRequest()->getParams();
        if (isset($requestParameters['customer']) && count($requestParameters['customer'] > 0)) {
            $customerEmail = array();
            $customerObj = Mage::getModel('customer/customer');
            foreach ($requestParameters['customer'] as $costomerId) {
                $costomerData = $customerObj->load($costomerId)->toArray();
                $customerEmail[] = empty($costomerData['email'])?array():$costomerData['email'];
            }
            $customerEmails = implode('|', $customerEmail);
            $emailDeleteResponce = Mage::getModel('sendinblue/sendinblue')->emailDelete($customerEmails);
        }

        if (isset($emailDeleteResponce['data']['unsubEmails'])) {
            Mage::getModel('core/session')->addSuccess(Mage::helper('sendinblue')->__('Total of '. count($emailDeleteResponce['data']['unsubEmails']) .' record(s) were Unsubscribed'));
        }
        return $this;
    }

    public function adminCustomerSubscribe($observer)
    {
        $requestParameters = Mage::app()->getRequest()->getParams();
        if (isset($requestParameters['customer']) && count($requestParameters['customer'] > 0)) {
            $customerEmail = array();
            $customerObj = Mage::getModel('customer/customer');
            foreach ($requestParameters['customer'] as $costomerId) {
                $costomerData = $customerObj->load($costomerId)->toArray();
                $customerEmail[] = empty($costomerData['email'])?array():$costomerData['email'];
            }
            $customerEmails = implode('|', $customerEmail);
            $addEmailResponce = Mage::getModel('sendinblue/sendinblue')->addEmailList($customerEmails);
        }
        if (isset($addEmailResponce['code']) && $addEmailResponce['code'] == 'success') {
            Mage::getModel('core/session')->addSuccess(Mage::helper('sendinblue')->__('Email has been subscribed successfully.'));
        }
        return $this;
    }

    public function subscribeObserver($observer)
    {
        $extra = array();
        $requestParameters = Mage::app()->getRequest()->getParams();
        $collectionNews = Mage::getResourceModel('newsletter/subscriber_collection')->showStoreInfo()->addFieldToFilter('subscriber_email', $requestParameters['email'])->load();

        foreach ($collectionNews as $subsdata) {
            $extra = $subsdata;
		}
		$storeId = $extra['store_id'];
		$storeData = Mage::getModel('core/store')->load($storeId);
		$languageLabel = $storeData->getName();
        $sendinModule = Mage::getModel('sendinblue/sendinblue');
        $attributesName = $sendinModule->allAttributesName();

        if ($requestParameters['email'] != '') {
            $newsletterStatus = 0;
        }

        $client = 0;
        $mergeArrayResponse = $sendinModule->mergeMyArray($attributesName, $extra);
        $mergeArrayResponse['CLIENT'] = $client;
        $mergeArrayResponse['MAGENTO_LANG'] = $languageLabel;
        $emailAddResponce = $sendinModule->emailAdd($requestParameters['email'], $mergeArrayResponse, $newsletterStatus);
        return $this;
    }

    public function updateNewObserver($observer)
    {
        $requestParameters = Mage::app()->getRequest()->getParams();
        $costomerSession = Mage::getSingleton('customer/session')->getCustomer();
        $sendinModule = Mage::getModel('sendinblue/sendinblue');
        $attributesName = $sendinModule->allAttributesName();

        $customerSessionEmail = $costomerSession->getEmail();
        if (empty($customerSessionEmail)) {
            $customer = $observer->getCustomer();
            $customerData = $customer->getData();
        }
        else{
            $customerData = $costomerSession->getData();
        }

        $email = $customerData['email'];
        $costomerEntityId = isset($customerData['entity_id']) ? $customerData['entity_id'] : '';        
        $collectionAddress = Mage::getModel('customer/address')->getCollection()->addAttributeToSelect('telephone')->addAttributeToSelect('firstname')->addAttributeToSelect('lastname')->addAttributeToSelect('company')->addAttributeToSelect('street')->addAttributeToSelect('postcode')->addAttributeToSelect('region')->addAttributeToSelect('country_id')->addAttributeToSelect('city')->addAttributeToFilter('parent_id',$costomerEntityId);

        $telephone = '';
        $customerAddress = array();
        $customerAddressData = array();
        foreach ($collectionAddress as $customerPhno) {
            $customerAddress = $customerPhno->getData();
            if (!empty($customerAddress['telephone'])) {
                if(!empty($customerAddress['country_id'])) {
                    $countryCode = $sendinModule->getCountryCode($customerAddress['country_id']);
                    $countryCode = !empty($countryCode) ? $countryCode : '';
                    $customerAddress['telephone'] = $sendinModule->checkMobileNumber($customerAddress['telephone'], $countryCode);
                }
            }
        }

        $customerAddressData = array_merge($customerAddress, $customerData);
        if (!empty($customerData['firstname']) && !empty($customerData['lastname'])) {
            $client = 1;
        }
        else {
            $client = 0;
        }

        $requestSubscriber = isset($requestParameters['is_subscribed']) ? $requestParameters['is_subscribed'] : '';
        $isSubscribed = !empty($customerAddressData['is_subscribed']) ? $customerAddressData['is_subscribed'] : $requestSubscriber;
        if (!empty($customerData['firstname']) || !empty($customerAddress['telephone']) || !empty($email)) {
            $costomerData = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            $costomerDataStatus = $costomerData->getStatus();
            $mergeArrayResponse = $sendinModule->mergeMyArray($attributesName, $customerAddressData);
            $mergeArrayResponse['CLIENT'] = $client;
            if (isset($isSubscribed) && $isSubscribed == 1 && empty($costomerDataStatus)) {
                $responce = $sendinModule->emailAdd($email, $mergeArrayResponse, $isSubscribed);
                $sendinModule->sendWsTemplateMail($email);
            }
            elseif (!empty($costomerDataStatus)) {
                $responce = $sendinModule->emailAdd($email, $mergeArrayResponse);
            }   
        }
        
        if (isset($isSubscribed) && !empty($isSubscribed) && $isSubscribed === 0) {
            $responce = $sendinModule->emailDelete($email);
        }       
        return $this;
    }

    public function syncData()
    {
        $responce = Mage::getModel('sendinblue/sendinblue')->syncData();
        return $this;
    }

    public function updateStatus($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING) {
            $history = $order->getShipmentsCollection();
            $shipmentHistoryData = $history->toarray();
            if($shipmentHistoryData['totalRecords'] > 0) {
                $orderId = isset($shipmentHistoryData['items']['0']['order_id']) ? $shipmentHistoryData['items']['0']['order_id'] : '';
                $shippingaddrid = isset($shipmentHistoryData['items']['0']['shipping_address_id']) ? $shipmentHistoryData['items']['0']['shipping_address_id'] : '';
                $_order = Mage::getModel('sales/order')->load($orderId);
                $_shippingAddress = $_order->getShippingAddress();
                $locale = Mage::app()->getLocale()->getLocaleCode();
                $mobileSms = $_shippingAddress->getTelephone();
                $mobileSms = !empty($mobileSms) ? $mobileSms : '';
                $countryid = $_shippingAddress->getCountryId();
                $countryid = !empty($countryid) ? $countryid : '';
                $codeResource = Mage::getSingleton('core/resource');
                $tableCountry = $codeResource->getTableName('sendinblue_country_codes');
                $readDbObject = Mage::getSingleton("core/resource")->getConnection("core_read");
                $queryCountryCode = $readDbObject->select()
                    ->from($tableCountry, array('country_prefix'))
                    ->where("iso_code = ?", $countryid);
                $stmtCountryCode = $readDbObject->query($queryCountryCode);
                $data = $stmtCountryCode->fetch();
                $mobile = '';
                $countryPrefix = !empty($data['country_prefix']) ? $data['country_prefix'] : '';
                $sendinblueModule = Mage::getModel('sendinblue/sendinblue');
                if(isset($countryPrefix) && !empty($countryPrefix)){ 
                    $mobile = $sendinblueModule->checkMobileNumber($mobileSms, $countryPrefix);
                }
                $firstname = $_shippingAddress->getFirstname();
                $firstname = !empty($firstname ) ? $firstname : '';
                $lastname = $_shippingAddress->getLastname();
                $lastname = !empty($lastname) ? $lastname : '';
                $refNum = $_order->getIncrementId();
                $refNum = !empty($refNum) ? $refNum : '';
                $orderprice = $_order->getGrandTotal();
                $orderprice = !empty($orderprice) ? $orderprice:'';
                $courrencycode = $_order->getBaseCurrencyCode();
                $courrencycode = !empty($courrencycode) ? $courrencycode:'';
                $orderdate = $_order->getCreatedAt();
                $orderdate = !empty($orderdate) ? $orderdate : '';

                $ordDate = ($locale == 'fr_FR') ? date('d/m/Y', strtotime($orderdate)) : date('m/d/Y', strtotime($orderdate));
                
                $totalPay = $orderprice.' '.$courrencycode;
                $messageBody = $sendinblueModule->getSendSmsShipingMessage();
                $fname = str_replace('{first_name}', $firstname, $messageBody);
                $lname = str_replace('{last_name}', $lastname."\r\n", $fname);
                $procuctPrice = str_replace('{order_price}', $totalPay, $lname);
                $orderDate = str_replace('{order_date}', $ordDate."\r\n", $procuctPrice);
                $messageBody = str_replace('{order_reference}', $refNum, $orderDate);
                $senderShipment = $sendinblueModule->getSendSmsShipingSubject();

                $smsInformation = array();
                $smsInformation['to'] = $mobile;
                $smsInformation['from'] = $senderShipment;
                $smsInformation['text'] = $messageBody;
                if (!empty($senderShipment) && !empty($messageBody) && !empty($mobile)) {
                    $sendinblueModule->sendSmsApi($smsInformation);
                }
            }
        }
    }

    public function subscribedToNewsletter($observer)
    {
        $data = $observer->subscriber;
        $requestParameters = Mage::app()->getRequest()->getParams();
        if (empty($requestParameters['firstname']) && empty($requestParameters['lastname']) && empty($requestParameters['payment'])) {
            $sibObj = Mage::getModel('sendinblue/sendinblue');
            $subscriberEmail = $data->subscriber_email;

            if($data->subscriber_status == 3) {
                $sibObj->emailDelete($subscriberEmail);
            }
            else if ($data->subscriber_status == 1 && !empty($subscriberEmail)) {
                $sibObj->emailSubscribe($data->subscriber_email);
                if( !isset($requestParameters['newsletter'])) { 
                    $sibObj->sendWsTemplateMail($data->subscriber_email);
                }
            }
        }
    }
}
