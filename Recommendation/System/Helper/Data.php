<?php

namespace Recommendation\System\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    // Configurações gerais do módulo
    const SETTINGS_GENERAL = 'recommendation_system/settings_general/';
    // Configurações do cron
    const SETTINGS_CRON = 'recommendation_system/settings_cron/';
    // Configurações de exportação
    const SETTINGS_EXPORT = 'recommendation_system/settings_export/';
    // Configurações da estrutura dos dados
    const SETTINGS_DATA = 'recommendation_system/settings_data/';
    /**
     * Obtem as configurações gerais
     */
    public function getSettingsGeneral($configNode){
        return $this->scopeConfig->getValue(
            self::SETTINGS_GENERAL.$configNode,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Obtem as configurações do CRON
     */
    public function getSettingsCron($configNode){
        return $this->scopeConfig->getValue(
            self::SETTINGS_CRON.$configNode,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Obtem as configurações de exportação
     */
    public function getSettingsExport($configNode){
        return $this->scopeConfig->getValue(
            self::SETTINGS_EXPORT.$configNode,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Obtem as configurações da estrutura dos dados
     */
    public function getSettingsData($configNode)
    {
        return $this->scopeConfig->getValue(
            self::SETTINGS_DATA.$configNode,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}