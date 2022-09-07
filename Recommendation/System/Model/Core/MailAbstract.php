<?php
namespace Recommendation\System\Model\Core;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Area;
use Recommendation\System\Logger\Logger;

abstract class MailAbstract {

    protected $_storeManager;
    protected $_postObject;
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_logger;

    public function __construct(
        StoreManagerInterface $storeManager,
        DataObject $postObject,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Logger $logger
    )
    {
        $this->_storeManager = $storeManager;
        $this->_postObject = $postObject;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_logger = $logger;
    }

     /**
     * Método de envio de email
     */
    public function sendMail($data){
        try {
            $storeId = $this->_storeManager->getStore()->getId();

            //Obtem as variaveis do template
            $templateVars = $this->getTemplateVars($data['body']);

            //Seta as variaveis do template
            $this->_postObject->setData($templateVars);

            //Informa o email e o nome de quem esta enviando
            $from = ['email' => $data['sender_email'], 'name' => $data['sender_name']];

            $this->_inlineTranslation->suspend();
 
            $storeScope = ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            
            $transport = $this->_transportBuilder->setTemplateIdentifier($data['template_id'], $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars(['data' => $this->_postObject])
                ->setFrom($from)
                ->addTo($data['to_email'])
                ->getTransport();
            $transport->sendMessage();

            $this->_inlineTranslation->resume();
 
            return true;
        } 
        catch (\Exception $e) {
            $this->_logger->info('ERROR_CANCEL_ORDER: ' . $e->getMessage());
            //Gravar em log
        }
        return false;    
    }
    /**
     * Seta as variaveis do template de email 
     */
    private function getTemplateVars($body){
        $templateVars = array();
       
        foreach($body as $key => $value){
            $templateVars[$key] = $value;
        }

        return $templateVars;
    }
}