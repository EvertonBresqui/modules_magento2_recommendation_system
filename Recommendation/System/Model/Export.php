<?php
/**
 * <img src="http://localhost/magento2.3.3/pub/static/version1609875432/frontend/Magento/luma/en_US/Magento_Catalog/images/product/placeholder/image.jpg"
 */
namespace Recommendation\System\Model;

use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\ResourceConnection;
use Recommendation\System\Helper\Data;
use Recommendation\System\Model\Core\Writer;
use Recommendation\System\Logger\Logger;
use Recommendation\System\Model\Core\ExporterInterface;
use Recommendation\System\Model\Core\ApiAstract;

class Export extends ApiAstract implements ExporterInterface{

    protected $_logger;
    protected $_helper;
    protected $_storeManager;
    protected $_resource;
    protected $_connection;
    protected $_connectedApi;
    //Objeto para a escrita da exportaçaõ por JSON
    protected $_writer;
    /**
     * Attributos
     */
    protected $_table;
    protected $_attributes;
    protected $_qtyTotal;
    protected $_qtyExported;
    protected $_qtyPositionExported;
    protected $_data;
    protected $_linkFile;
    protected $_isDrop;

    public function __construct(
        Logger $logger,
        Data $helper,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        Writer $writer
    ) {
        $this->_logger = $logger;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_connection = $this->_resource->getConnection();
        $this->_writer = $writer;
        $this->setDomain($this->_helper->getSettingsExport('host'));

        // Faz a autenticação na api
        if($this->_helper->getSettingsExport('type_export') == 0){
            $this->_connectedApi = $this->auth();
        }

    }

    public function init($table, $data){
        $this->_table = $table;
        $this->_attributes = $data->attributes;
        $this->_qtyTotal = $data->qty_total;
        $this->_qtyExported = $data->qty_exported;
        $this->_qtyPositionExported = $data->registry_position_exported;
    
        if($this->_qtyExported == 0)
            $this->_isDrop = true;
        else
            $this->_isDrop = false;

    }

    /**
     * Inicia a exportação
     */
    public function exportInit(){
        $response = array();

        //Obtem os registros do banco
        $this->_data = $this->getData();
        //Verifica se foi exportados os dados
        $isExported = $this->process();

        if($isExported){
            $this->_qtyExported += count($this->_data); 
            $this->_qtyPositionExported += (int) $this->_helper->getSettingsData('qty_registers_cicle');
            
            $response = array(
                'qty_exported' => $this->_qtyExported, 
                'registry_position_exported' => $this->_qtyPositionExported,
                'link_file' => $this->_linkFile
            );
        }

        return $response;
    }

    /**
     * Obtem os registros do banco
     */
    public function getData(){
        $columnsOrderBy = $this->_getTableIndicator();
        $limit = (int) $this->_helper->getSettingsData('qty_registers_cicle');
        $offset = (int) $this->_qtyPositionExported;
        $select = $this->_connection->select()
            ->from(
                ['tb' => $this->_table],
                $this->_attributes
            )
            ->group($columnsOrderBy,'ASC')
            ->limit($limit, $offset);
        
        $data = $this->_connection->fetchAll($select);

        return $data;
    }

    /**
     * Processa os dados para exportação
     */
    public function process(){
        $isExported = false;
        //Faz a envio direto para a API
        if($this->_helper->getSettingsExport('type_export') == 0 && $this->_connectedApi === true){
            $isExported = $this->send();
        }
        //Faz a exportação em arquivo no formato JSON
        else{
            $isExported = $this->write();
        }
        return $isExported;
    }
    /**
     * Envia os dados para a API
     */
    public function send(){
        $isExported = false;
        try{
            $this->_logger->info('Exportação dos registros iniciada.');

            $route = 'sysimport';

            $params = array();
            $params['drop'] = $this->_isDrop;

            $body = array(
                'sale_group' => (int) $this->_helper->getSettingsExport('sale_group'),
                'params' => $params,
                'data' => array(
                    $this->_table => $this->_data
                )
            );
            $response = $this->post($route, $body);

            if($response['httpCode'] == 200){
                $this->_logger->info('Registros exportados com sucesso!');
                $isExported = true;
            }
            else{
                $this->_logger->critical('Falha ao gravar os registros no arquivo de exportação.');
            }
        }
        catch(\Exception $e){
            $this->_logger->critical('Falha ao tentar gravar no arquivo de exportação: ' . $e->getMessage());
        }
        return $isExported;
    }
    /**
     * Grava os dados em um arquivo JSON
     */
    public function write(){
        $isExported = false;
        try{
            $this->_logger->info('Exportação dos registros iniciada.');
            $this->_writer->init($this->_table, $this->_data, $this->_qtyExported);
            //Função que grava os registros
            $isExported = $this->_writer->saveData();
            if($isExported){
                $this->_linkFile = $this->_writer->getFileUrl();
                $this->_logger->info('Registros exportados com sucesso!');
            }
            else{
                $this->_logger->critical('Falha ao gravar os registros no arquivo de exportação.');
            }
        }
        catch(\Exception $e){
            $this->_logger->critical('Falha ao tentar gravar no arquivo de exportação: ' . $e->getMessage());
        }
        return $isExported;
    }

    /**
     * Obtem a quantidade total de registros das tabelas
     */
    public function getQtyTotal($tableName){

        $select = "SELECT COUNT(*) FROM $tableName";
        $qtyTotal = (int) $this->_connection->fetchOne($select);
        
        return $qtyTotal;
    }

    /**
     * Obtem a chave de ordenação para a paginação
     */
    private function _getTableIndicator(){
        $result = array();
        $tableIndicators = explode(PHP_EOL, $this->_helper->getSettingsData('table_incator_order_by'));
        //Percorre as chaves primárias das tabelas
        foreach($tableIndicators as $tableIndicator){
            $tableIndicatorArray = explode('=', $tableIndicator);
            if($tableIndicatorArray[0] === $this->_table){
                //Remove caracteres de quebra de linha e espaço
                $tableIndicatorArray[1] = $this->_formatStr($tableIndicatorArray[1]);
                $result = explode(',',$tableIndicatorArray[1]);
            }
        }
        return $result;
    }

    /**
     * Remove caracteres indesejados
     */
    private function _formatStr($string){
        $string = trim($string);
        $string = str_replace(PHP_EOL, '', $string);
        return $string;
    }
}