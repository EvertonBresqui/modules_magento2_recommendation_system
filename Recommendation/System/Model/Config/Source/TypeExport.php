<?php

namespace Recommendation\System\Model\Config\Source;

class TypeExport implements \Magento\Framework\Option\ArrayInterface
{ 
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            '0' => 'Api',
            '1' => 'File'
        ];
    }
}