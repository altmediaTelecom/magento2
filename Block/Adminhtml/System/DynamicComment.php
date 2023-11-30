<?php


namespace Altmedia\Sms\Block\Adminhtml\System;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Config\Model\Config\CommentInterface;

class DynamicComment extends AbstractBlock implements CommentInterface
{
    /**
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        return "If you don't have a sender ID, contact our team <a href=\"https://www.altmedia-telecom.ro/contacts/\" target='_blank'>here</a>";
    }
}
