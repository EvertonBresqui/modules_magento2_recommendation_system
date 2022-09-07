<?php

namespace Recommendation\Als\Model\Config\Source;

use Magento\Framework\Registry;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;

class Templates implements \Magento\Framework\Option\ArrayInterface
{ 
    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;
    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $_templatesFactory;

    public function __construct(
        Registry $coreRegistry,
        CollectionFactory $templatesFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_templatesFactory = $templatesFactory;
    }

    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!($collection = $this->_coreRegistry->registry('config_system_email_template'))) {
            $collection = $this->_templatesFactory->create();
            $collection->load();
            $this->_coreRegistry->register('config_system_email_template', $collection);
        }
        $options = $collection->toOptionArray();
        return $options;
    }
}