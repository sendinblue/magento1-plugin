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
    /**
     * This hook called for event checkout_type_onepage_save_order_after
     */
    public function orderComplete($observer) {
        if (Mage::helper('sendinblue')->ModuleisEnabled() == 0)
        {
            return false;
        }

        $order = $observer->getEvent()->getOrder();
        $orderData = $order->getData();

        $requestParameters = Mage::app()->getRequest()->getParams();
        $automationKey = Mage::getStoreConfig('sendinblue/automation/key');
        $abandonedCartStatus = Mage::getStoreConfig('sendinblue/tracking/abandonedcartstatus');
        $email = Mage::helper('sendinblue')->getEmail();
       
        if (empty(email)) {
            $email = $orderData['customer_email'];
        }
        if (empty($automationKey) || $abandonedCartStatus != 1 || empty($email)) { 
            return false;
        }
        $data = array(
            'email' => $email,
            'event' => 'order_completed',
            'properties' => array(
                'FIRSTNAME' => !empty($orderData['customer_firstname']) ? $orderData['customer_firstname'] : '',
                'LASTNAME' =>  !empty($orderData['customer_firstname']) ? $orderData['customer_firstname'] : ''
            ),
            'eventdata' => array(
                'id' => "cart:".$orderData['quote_id'],
                'data' => array()
            )
        );
        $totalDiscount =  !empty($orderData['discount_amount']) ? $orderData['discount_amount'] : 0;
        $totalShipping =  !empty($orderData['shipping_amount']) ? $orderData['shipping_amount'] : 0;
        $totalShippingTaxInc = !empty($orderData['shipping_incl_tax']) ? $orderData['shipping_incl_tax'] : 0;
        $totalTax =  !empty($orderData['tax_amount']) ? $orderData['tax_amount'] : 0;

        $data['eventdata']['data']['id'] = $orderData['increment_id'];
        $data['eventdata']['data']['currency'] = !empty($orderData['order_currency_code']) ? $orderData['order_currency_code'] : '';
        $data['eventdata']['data']['discount'] = $totalDiscount;
        $data['eventdata']['data']['discount_taxinc'] = $totalDiscount;
        $data['eventdata']['data']['revenue'] =  !empty($orderData['grand_total']) ? $orderData['grand_total'] : 0;
        $data['eventdata']['data']['shipping'] = $totalShipping;
        $data['eventdata']['data']['shipping_taxinc'] = $totalShippingTaxInc;
        $data['eventdata']['data']['subtotal']= !empty($orderData['subtotal']) ? $orderData['subtotal'] + $totalDiscount : 0;
        $data['eventdata']['data']['subtotal_predisc'] = !empty($orderData['subtotal']) ? $orderData['subtotal'] : 0;
        $data['eventdata']['data']['subtotal_taxinc'] =   !empty($orderData['subtotal']) ? $orderData['subtotal'] + $totalDiscount + $totalTax : 0;
        $data['eventdata']['data']['subtotal_predisc_taxinc'] = $orderData['subtotal'] + $totalTax;
        $data['eventdata']['data']['tax']= $totalTax;
        $data['eventdata']['data']['total'] = !empty($orderData['grand_total']) ? $orderData['grand_total'] : 0;
        $data['eventdata']['data']['total_before_tax'] = $orderData['grand_total'] - $totalTax;
        $data['eventdata']['data']['url'] = Mage::getUrl('sales/order');

        $allProducts = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $productData = $item->getData();
            $productId = !empty($productData['product_id']) ? $productData['product_id']: '';
            $product = Mage::getModel('catalog/product')->load($productId);
            if(empty($product)){
                continue;
            }
            //getImage() will return relative path of image or "no_selection" id there is no image
            $image = $product->getImage();
            $imageFullUrl = !empty($image) && $image != 'no_selection' ?  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' .$image : $product->getImageUrl();
            $discAmountSum = !empty($productData['discount_amount']) ? $productData['discount_amount']:0;
            $qty = !empty($productData['qty_ordered']) ? $productData['qty_ordered']:1;
            $discAmountPerItem = $discAmountSum/$qty;
            $pricePreDisc = !empty($productData['price']) ? $productData['price'] : 0;
            $pricePreDiscTaxInc = !empty($productData['price_incl_tax']) ? $productData['price_incl_tax'] : 0;
            $priceDiscInc = $pricePreDisc - $discAmountPerItem; // here minus discount because discount is already negative
            $priceDiscIncTaxInc = $pricePreDiscTaxInc - $discAmountPerItem; // here minus discount because discount is already negative
            $allProducts[] = array(
                'id' => !empty($productData['product_id']) ? $productData['product_id'] : '',
                'name' => !empty($productData['name']) ? $productData['name'] : '',
                'category' => !empty($productData['product_type']) ? $productData['product_type'] : '',
                'description_short' => $product->getData('short_description'),
                'available_now' => !empty($product->getStockItem()->getQty()) ? 'In Stock': 0,
                'price' => round($priceDiscInc, 3),
                'quantity' => $qty,
                'variant_id_name' => '',
                'variant_id' => '',
                'size' => !empty($product->getData('size')) ? $product->getData('size') : '',
                'sku' => !empty($productData['sku']) ? $productData['sku'] : '',
                'url' => $product->getProductUrl(),
                'image' => $imageFullUrl,
                'price_predisc' =>  round($pricePreDisc, 3),
                'price_predisc_taxinc' => round($pricePreDiscTaxInc, 3),
                'price_taxinc' => round($priceDiscIncTaxInc, 3),                                   
                'tax_amount' => round($pricePreDiscTaxInc - $pricePreDisc, 3),
                'tax_rate' => !empty($productData['tax_percent']) ? $productData['tax_percent'] : 0,
                'tax_name' => !empty($productData['tax_name']) ? $productData['tax_name'] : '',
                'disc_amount' => round($discAmountPerItem, 3),
                'disc_amount_taxinc' => round($discAmountPerItem,3),
                'disc_rate' => round($discAmountPerItem/$pricePreDisc, 3)*100,
            );
        }
        $data['eventdata']['data']['items'] = $allProducts;

        $billingAddress = $order->getBillingAddress()->getData();
        $shippingAddress = $order->getShippingAddress()->getData();
        $data['eventdata']['data']['shipping_address'] = array(
            'firstname' => !empty($shippingAddress['firstname']) ? $shippingAddress['firstname'] : '',
            'lastname' => !empty($shippingAddress['lastname']) ? $shippingAddress['lastname'] : '',
            'company' => !empty($shippingAddress['company']) ? $shippingAddress['company'] : '',
            'phone' => !empty($shippingAddress['telephone']) ? $shippingAddress['telephone'] : '',
            'country' => !empty($shippingAddress['country_id']) ? $shippingAddress['country_id'] : '',
            'state' => !empty($shippingAddress['region']) ? $shippingAddress['region'] : '',
            'address1' => !empty($shippingAddress['street']) ? $shippingAddress['street'] : '',
            'address2' => !empty($shippingAddress['street']) ? $shippingAddress['street'] : '',
            'city' => !empty($shippingAddress['city']) ? $shippingAddress['city'] : '',
            'zipcode' => !empty($shippingAddress['postcode']) ? $shippingAddress['postcode'] : ''
        );
        $data['eventdata']['data']['billing_address'] = array(
            'firstname' => !empty($billingAddress['firstname']) ? $billingAddress['firstname'] : '',
            'lastname' => !empty($billingAddress['lastname']) ? $billingAddress['lastname'] : '',
            'company' => !empty($billingAddress['company']) ? $billingAddress['company'] : '',
            'phone' => !empty($billingAddress['telephone']) ? $billingAddress['telephone'] : '',
            'country' => !empty($billingAddress['country_id']) ? $billingAddress['country_id'] : '',
            'state' => !empty($billingAddress['region']) ? $billingAddress['region'] : '',
            'address1' => !empty($billingAddress['street']) ? $billingAddress['street'] : '',
            'address2' => !empty($billingAddress['street']) ? $billingAddress['street'] : '',
            'city' => !empty($billingAddress['city']) ? $billingAddress['city'] : '',
            'zipcode' => !empty($billingAddress['postcode']) ? $billingAddress['postcode'] : ''
        );
        Mage::helper('sendinblue')->curlPostAbandonedEvents($data, 'trackEvent');
    }
    /**
     * This hook called for event sales_quote_save_after
     */
    public function cartUpdate($observer) {

        if (Mage::helper('sendinblue')->ModuleisEnabled() == 0)
        {
            return false;
        }
        $requestParameters = Mage::app()->getRequest()->getParams();
        $automationKey = Mage::getStoreConfig('sendinblue/automation/key');
        $abandonedCartStatus = Mage::getStoreConfig('sendinblue/tracking/abandonedcartstatus');
        $email = Mage::helper('sendinblue')->getEmail();
    
        //Cart Quote related to current session
        $cartQuote =  $observer->getQuote();
        // Get Total Items in Cart
        $cartItemsCount = $cartQuote->getItemsCount();
        // Get Cart Id of current session
        $cartId = $cartQuote->getId();
         
        //check if Order already reserved or placed / If yes then do not sent "cart_updated event"
        $orderId = $cartQuote->getReservedOrderId();

        if (empty($automationKey) || $abandonedCartStatus != 1 || empty($email) || empty($cartId) || !empty($orderId)) { 
            return false;
        }
        // Get all atributes of cart in array
        $cartData = $cartQuote->getData();
        $isCartEmptyAction = !empty($requestParameters['update_cart_action']) ? $requestParameters['update_cart_action'] == 'empty_cart' : 0;
        $data = array(
            'email' => $email,
            'event' => '',
            'properties' => array(
                'FIRSTNAME' => $cartData['customer_firstname'],
                'LASTNAME' =>  $cartData['customer_lastname']
            ),
            'eventdata' => array(
                'id' => "cart:".$cartId,
                'data' => array()
            )
        );
       
        if ($cartItemsCount > 0) {
            // Get Total object of the Cart like total, discount, shipping, grand_total and subtotal
            $totals = $cartQuote->getTotals();
                    
            $totalDiscount = !empty($totals['discount']) ? $totals['discount']->getValue() : 0;
            $totalShipping = !empty($totals['shipping']) ? $totals['shipping']->getValue() : 0;
            $totalTax = !empty($totals['tax']) ? $totals['tax']->getValue() : 0;

            $data['eventdata']['data']['currency'] = $cartQuote->getQuoteCurrencyCode();
            $data['eventdata']['data']['discount'] = $totalDiscount;
            $data['eventdata']['data']['discount_taxinc'] = $totalDiscount;
            $data['eventdata']['data']['revenue'] = !empty($cartData['grand_total']) ? $cartData['grand_total']: 0;
            $data['eventdata']['data']['shipping'] = $totalShipping;
            $data['eventdata']['data']['shipping_taxinc'] = $totalShipping;
            $data['eventdata']['data']['subtotal']= !empty($cartData['subtotal_with_discount']) ? $cartData['subtotal_with_discount']: 0;
            $data['eventdata']['data']['subtotal_predisc']= !empty($cartData['subtotal']) ?$cartData['subtotal']: 0;
            $data['eventdata']['data']['subtotal_taxinc']= !empty($cartData['subtotal_with_discount']) ? $cartData['subtotal_with_discount'] + $totalTax : 0;
            $data['eventdata']['data']['subtotal_predisc_taxinc'] = !empty($cartData['subtotal']) ? $cartData['subtotal'] + $totalTax: 0;
            $data['eventdata']['data']['tax']= $totalTax;
            $data['eventdata']['data']['total']= !empty($cartData['grand_total']) ? $cartData['grand_total']: 0;
            $data['eventdata']['data']['total_before_tax']=  !empty($cartData['grand_total']) ? $cartData['grand_total'] - $totalTax: 0;
            $data['eventdata']['data']['url']= Mage::getUrl('checkout/cart');
            $allProducts = array();
            foreach ($cartQuote->getAllVisibleItems() as $item) {
                $productData = $item->getData();
                $productId = !empty($productData['product_id']) ? $productData['product_id']: '';
                $product = Mage::getModel('catalog/product')->load($productId);
                if(empty($product)){
                    continue;
                }
                //getImage() will return relative path of image or "no_selection" id there is no image
                $image = $product->getImage();
                $imageFullUrl = !empty($image) && $image != 'no_selection' ?  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' .$image : $product->getImageUrl();
                $discAmountSum = !empty($productData['discount_amount']) ? $productData['discount_amount']:0;
                $qty = !empty($productData['qty']) ? $productData['qty']:1;
                $discAmountPerItem = $discAmountSum/$qty;
                $pricePreDisc = !empty($productData['price']) ? $productData['price'] : 0;
                $pricePreDiscTaxInc = !empty($productData['price_incl_tax']) ? $productData['price_incl_tax'] : 0;
                $priceDiscInc = $pricePreDisc - $discAmountPerItem; // here minus discount because discount is already negative
                $priceDiscIncTaxInc = $pricePreDiscTaxInc - $discAmountPerItem; // here minus discount because discount is already negative
                $allProducts[] = array(
                    'id' => !empty($productData['product_id']) ? $productData['product_id'] : '',
                    'name' => !empty($productData['name']) ? $productData['name'] : '',
                    'category' => !empty($productData['product_type']) ? $productData['product_type'] : '',
                    'description_short' => $product->getData('short_description'),
                    'available_now' => !empty($product->getStockItem()->getQty()) ? 'In Stock': 0,
                    'price' => round($priceDiscInc, 3),
                    'quantity' => $qty,
                    'variant_id_name' => '',
                    'variant_id' => '',
                    'size' => !empty($product->getData('size')) ? $product->getData('size') : '',
                    'sku' => !empty($productData['sku']) ? $productData['sku'] : '',
                    'url' => $product->getProductUrl(),
                    'image' => $imageFullUrl,
                    'price_predisc' =>  round($pricePreDisc, 3),
                    'price_predisc_taxinc' => round($pricePreDiscTaxInc, 3),
                    'price_taxinc' => round($priceDiscIncTaxInc, 3),                                   
                    'tax_amount' => round($pricePreDiscTaxInc - $pricePreDisc, 3),
                    'tax_rate' => !empty($productData['tax_percent']) ? $productData['tax_percent'] : 0,
                    'tax_name' => !empty($productData['tax_name']) ? $productData['tax_name'] : '',
                    'disc_amount' => round($discAmountPerItem, 3),
                    'disc_amount_taxinc' => round($discAmountPerItem,3),
                    'disc_rate' => round($discAmountPerItem/$pricePreDisc, 3)*100,
                ) ;
            }
            $data['eventdata']['data']['items'] = $allProducts;
            $data['event'] = 'cart_updated';
            Mage::helper('sendinblue')->curlPostAbandonedEvents($data, 'trackEvent');
        } else {
            // on every refresh of checkout/cart , this hook(cartUpdate) trigger, so Add this "uenc" or "item" check to prevent "cart_deleted" event trigger on simple refresh checkout page when the cart is already empty
            if($isCartEmptyAction || (empty($cartItemsCount) && (!empty($requestParameters['uenc']) || !empty($requestParameters['item'])))) {
                $data['event'] = 'cart_deleted';
                $data['eventdata']['data']['items'] = array();
                Mage::helper('sendinblue')->curlPostAbandonedEvents($data, 'trackEvent');
            }
        }
    }
}
