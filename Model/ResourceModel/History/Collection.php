<?php

namespace Altmedia\Sms\Model\ResourceModel\History;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'history_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Altmedia\Sms\Model\History::class,
            \Altmedia\Sms\Model\ResourceModel\History::class
        );
    }
}
