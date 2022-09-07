<?php
namespace Recommendation\System\Block\Adminhtml;

use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Block\Template;
use Recommendation\System\Helper\Data as HelperData;
use Recommendation\System\Model\Export as ModelExport;
use \Magento\Framework\UrlInterface;

class Export extends Template
{
    /**
     * Helper Data
     */
    protected $_helper;
    /**
     * Model Export
     */
    protected $_modelExport;
    /**
     * URL Interface
     */
    protected $_urlInterface;

    public function __construct(
        Context $context,
        HelperData $helper,
        ModelExport $modelExport,
        UrlInterface $urlInterface)
    {
        $this->_helper = $helper;
        $this->_modelExport = $modelExport;
        $this->_urlInterface = $urlInterface;
        parent::__construct($context);
    }
    /**
     * Obtem as tabelas
     */
    public function getTables(){
        $return = array();
        if(trim($this->_helper->getSettingsData('tables')) != ''){
            $attributes = explode(PHP_EOL, $this->_helper->getSettingsData('tables'));
            foreach($attributes as $attributeLine){
                $attributeArray = explode('=', $attributeLine);
                $return[$attributeArray[0]]['attributes'] = explode(',', $attributeArray[1]);
                $return[$attributeArray[0]]['qty_total'] = $this->_modelExport->getQtyTotal($attributeArray[0]);
                $return[$attributeArray[0]]['qty_exported'] = 0;
                $return[$attributeArray[0]]['registry_position_exported'] = 0;
            }
        }
        return $return;
    }

    /**
     * Obtem a quantidade de registros por ciclo de cron
     */
    public function getQtyPerCicle(){
        $qtyPerCicle = (int) $this->_helper->getSettingsData('qty_registers_cicle');
        return $qtyPerCicle;
    }

    /**
     * Obtem a url base
     */
    public function getUrlExport(){
        $baseURL = $this->_urlInterface->getUrl('recommendation/export/export');
        return $baseURL;
    }
}