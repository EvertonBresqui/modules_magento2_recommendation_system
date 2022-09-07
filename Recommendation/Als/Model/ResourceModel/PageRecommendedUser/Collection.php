<?php


namespace Recommendation\Als\Model\ResourceModel\PageRecommendedUser;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Recommendation\Als\Model\PageRecommendedUser',
            'Recommendation\Als\Model\ResourceModel\PageRecommendedUser'
        );
    }
}
