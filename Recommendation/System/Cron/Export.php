<?php
namespace Recommendation\System\Cron;

use Recommendation\System\Logger\Logger;
use Recommendation\System\Model\Export as ExportModel;
use Recommendation\System\Helper\Data;
use Recommendation\System\Block\Adminhtml\Export as ExportBlock;

class Export {
    protected $_logger;
    protected $_exportModel;
    protected $_helper;
    protected $_exportBlock;

    public function __construct(
        Logger $logger, 
        ExportModel $exportModel,
        Data $helper,
        ExportBlock $exportBlock
        ) 
    {
        $this->_logger = $logger;
        $this->_exportModel = $exportModel;
        $this->_helper = $helper;
        $this->_exportBlock = $exportBlock;
    }

   /**
    * Write to system.log
    *
    * @return void
    */

    public function execute() {
	    $this->_logger->info('[Recommendation_System] Run cron job recommendation_system_cronjob_export_sync');
        if($this->_helper->getSettingsCron('enable_cron')){
            $this->_logger->info('[Recommendation_System] Cron feed generation categories!');
            try{
                $tables = $this->_exportBlock->getTables();
                foreach($tables as $tableName => $values){
                    //Convert to object
                    $tableValues = json_decode(json_encode($values));
                    //Seta os dados para a exportação                    
                    $this->_exportModel->init($tableName, $tableValues);
                    //Loop para gerar exportação
                    $i = 0;
                    while($i < $tableValues->qty_total){
                        //Faz a exportação
                        $response = $this->_exportModel->exportInit();

                        $i += $response['qty_exported'];
                    }
                }
                $this->_logger->info('[Recommendation_System] Cron successfully executed!');
            }
            catch(\Exception $e){
                $this->_logger->critical('[Recommendation_System] Cron execution failed: ' . $e->getMessage());
            }
        }
    }

}
