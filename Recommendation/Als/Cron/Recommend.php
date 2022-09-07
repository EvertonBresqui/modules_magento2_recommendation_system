<?php
namespace Recommendation\Als\Cron;

use Recommendation\System\Logger\Logger;
use Recommendation\Als\Model\Recommend as RecommendModel;

class Recommend {

    protected $_logger;
    protected $_recommendModel;

    public function __construct(
        Logger $logger,
        RecommendModel $recommendModel
    ) 
    {
        $this->_logger = $logger;
        $this->_recommendModel = $recommendModel;
    }

    public function execute() {
	    $this->_logger->info('[Recommendation_Als] Run cron job recommendation_als_cronjob');
        $this->_recommendModel->processRecommendations();
        $this->_logger->info('[Recommendation_Als] Cron execution finished!');
    }

}
