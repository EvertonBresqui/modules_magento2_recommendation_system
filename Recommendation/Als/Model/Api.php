<?php
/**
 * <img src="http://localhost/magento2.3.3/pub/static/version1609875432/frontend/Magento/luma/en_US/Magento_Catalog/images/product/placeholder/image.jpg"
 */
namespace Recommendation\Als\Model;

use Recommendation\System\Model\Core\ApiAstract;
use Recommendation\System\Helper\Data;

class Api extends ApiAstract {

    protected $_helper;

    public function __construct(
        Data $helper
    )
    {
        $this->_helper = $helper;
        
        $this->setDomain($this->_helper->getSettingsExport('host'));
        // Faz a autenticação na api
        $this->_connectedApi = $this->auth();
    }

    public function getRecommendations($customerIds)
    {
        $recommendations = array();

        $path = 'sysrecommendation';
        $body = array(
            'sale_group' => (int) $this->_helper->getSettingsExport('sale_group'),
            'increment_id' => (int) $this->_helper->getSettingsExport('increment_id'),
            'users' => $customerIds
        );

        $result = $this->post($path, $body);

        if($result['httpCode'] == 200){
            $recommendations = $result['body'];
        }
        return $recommendations;
    }
}