<?php
/**
 * <img src="http://localhost/magento2.3.3/pub/static/version1609875432/frontend/Magento/luma/en_US/Magento_Catalog/images/product/placeholder/image.jpg"
 */
namespace Recommendation\Als\Model;

use Recommendation\System\Model\Core\MailAbstract;
use Recommendation\Als\Helper\Data as HelperData;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Recommendation\System\Logger\Logger;

class Mail extends MailAbstract {

    protected $_helper;

    public function __construct(
        HelperData $helper,
        StoreManagerInterface $storeManager,
        DataObject $postObject,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Logger $logger
    )
    {
        $this->_helper = $helper;
        parent::__construct(
            $storeManager,
            $postObject,
            $inlineTranslation,
            $transportBuilder,
            $logger
        );
    }

    public function sendMailRecommendation($customer, $recommendations)
    {
        $data = array();
        // Get Settings Mail
        $data['template_id'] = $this->_helper->getSettingsGeneral('template_id');
        $data['sender_name'] = $this->_helper->getSettingsGeneral('name_sale');
        $data['sender_email'] = $this->_helper->getSettingsGeneral('email_sale');

        $data['to_email'] = $customer->getEmail();
        $data['to_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
        //Get Data
        $data['body'] = array(
            'customer_name' => $data['to_name'],
            'recommendations' => json_encode($recommendations)
        );
        $result = $this->sendMail($data);

        return $result;
    }
}