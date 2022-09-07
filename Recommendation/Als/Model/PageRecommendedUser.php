<?php


namespace Recommendation\Als\Model;

use Magento\Framework\Model\AbstractModel;

class PageRecommendedUser extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Recommendation\Als\Model\ResourceModel\PageRecommendedUser');
    }

}
