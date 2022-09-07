<?php

namespace Recommendation\Als\Helper;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_configWriter;
    protected $_objectManager;
    // Configurações gerais do módulo
    const SETTINGS_GENERAL = 'recommendation_als/settings_general/';

    public function init(){
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_configWriter = $this->_objectManager->get('Magento\Framework\App\Config\Storage\WriterInterface');
    }

    /**
     * Obtem as configurações gerais
     */
    public function getSettingsGeneral($configNode)
    {
        return $this->scopeConfig->getValue(
            self::SETTINGS_GENERAL . $configNode,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Seta valores nas configurações gerais
     */
    public function setSettingsGeneral($configNode, $value)
    {
        $this->_configWriter->save(
            self::SETTINGS_GENERAL . $configNode,  
            $value, 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            1
        );
    }

}
