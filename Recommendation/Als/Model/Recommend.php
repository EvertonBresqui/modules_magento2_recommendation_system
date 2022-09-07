<?php
/**
 * <img src="http://localhost/magento2.3.3/pub/static/version1609875432/frontend/Magento/luma/en_US/Magento_Catalog/images/product/placeholder/image.jpg"
 */
namespace Recommendation\Als\Model;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Recommendation\Als\Helper\Data;
use Recommendation\Als\Model\Api;
use Recommendation\Als\Model\Mail;
use Recommendation\System\Logger\Logger;
use Recommendation\Als\Model\PageRecommendedUser;

class Recommend
{

    protected $_logger;
    protected $_helper;
    protected $_collectionFactory;
    protected $_mail;
    protected $_api;
    protected $_storeManager;
    protected $_pageRecommendedUser;

    public function __construct(
        Logger $logger,
        Data $helper,
        CollectionFactory $collectionFactory,
        Mail $mail,
        Api $api,
        StoreManagerInterface $storeManager,
        PageRecommendedUser $pageRecommendedUser
    ) {
        $this->_logger = $logger;
        $this->_helper = $helper;
        $this->_collectionFactory = $collectionFactory;
        $this->_mail = $mail;
        $this->_api = $api;
        $this->_storeManager = $storeManager;
        $this->_pageRecommendedUser = $pageRecommendedUser;

        $this->_helper->init();
    }

    /**
     * Processa as recomendações para os usuários
     *
     * @return void
     */
    public function processRecommendations()
    {
        $stores = $this->_storeManager->getStores();

        foreach ($stores as $store) {
            //Seta o id da loja
            $this->_storeManager->setCurrentStore($store->getId());
            // Verifica se o módulo foi habilitado na visão da loja 
            if ($this->_helper->getSettingsGeneral('enable')) {
                $this->_logger->info('[Recommendation_Als] Cron recommend als executed!');
                try {
                    //Verifica se a api esta ativa
                    if ($this->isApiActive()) {
                        // Obtem clientes para efetuar recomendações
                        $collectionCustomer = $this->getCustomersCollection($store->getId());

                        if ($collectionCustomer != false) {
                            // Envia recomendações para os clientes
                            $this->sentRecommendationsCustomers($collectionCustomer);
                            // Salva a paginação
                            $this->saveIncrementPagination();
                        }
                    }
                } catch (\Exception $e) {
                    $this->_logger->critical('[Recommendation_Als] Cron execution failed: ' . $e->getMessage());
                }
            }
        }
    }

    public function isApiActive()
    {
        if ($this->_api->getToken() != null) {
            return true;
        }
        return false;
    }

    public function sendMailRecommendation($customer, $recommendations)
    {
        $result = $this->_mail->sendMailRecommendation($customer, $recommendations);
        return $result;
    }

    public function getCustomerIds($collectionCustomer)
    {
        $customerIds = array();

        foreach ($collectionCustomer as $customer) {
            $customerIds[] = $customer->getId();
        }

        return $customerIds;
    }

    public function sentRecommendationsCustomers($collectionCustomers)
    {
        $customersIds = $this->getCustomerIds($collectionCustomers);

        if(count($customersIds) > 0){
            $recommendations = $this->_api->getRecommendations($customersIds);

            if (isset($recommendations->result) && is_array($recommendations->result) && count($recommendations->result) > 0) {
                foreach ($collectionCustomers as $customer) {
                    // Envia o email das recomendações para o cliente
                    $this->sendMailRecommendation($customer, $recommendations);
                }
            }
        }
    }

    public function getCustomersCollection()
    {
        $collectionCustomer = $this->_collectionFactory->create();
        $collectionCustomer->addAttributeToSelect('entity_id');
        $collectionCustomer->addAttributeToSelect('email');
        $collectionCustomer->setPageSize($this->_helper->getSettingsGeneral('qty_registrys_cicle'));
        $collectionCustomer->setCurPage($this->getCurrentPage());
        $collectionCustomer->addFieldToFilter("store_id", array("eq" => $this->_storeManager->getStore()->getId()));

        return $collectionCustomer;
    }

    public function saveIncrementPagination()
    {
        $currentPagination = $this->getCurrentPage();

        if (!is_numeric($currentPagination)) {
            $currentPagination = 0;
        }

        $this->setPage($currentPagination + 1);
    }

    public function getCurrentPage(){
        $page = $this->getPage();

        if($page->count() > 0){
            return $page->getData()[0]['page_id'];
        }
        return 0;
    }

    public function getPage(){
        $currentPage = $this->_pageRecommendedUser->getCollection()
            ->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId());
        
        return $currentPage;
    }

    public function isPageExists(){
        $page = $this->getPage();
        
        if($page->count() > 0)
            return true;
        return false;
    }

    public function setPage($pageIncrement){
        $pageExists = $this->isPageExists($this->_storeManager->getStore()->getId());

        if($pageExists){
            //update
            $page = $this->getPage();
            $pageRegistry = $this->_pageRecommendedUser->load($page->getData()[0]['id']);
            $pageRegistry->setStoreId($this->_storeManager->getStore()->getId());
            $pageRegistry->setPageId($pageIncrement);
            return $pageRegistry->save();
        }
        else{
            //insert
            $this->_pageRecommendedUser->setData(
                array(
                    'page_id' => $pageIncrement,
                    'store_id' => $this->_storeManager->getStore()->getId()
                )
            );
            return $this->_pageRecommendedUser->save();
        }
    }
}
