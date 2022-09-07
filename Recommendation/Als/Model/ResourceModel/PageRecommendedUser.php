<?php


namespace Recommendation\Als\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PageRecommendedUser extends AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('recommendation_als_page_recommended_user', 'id');
    }

    
}
