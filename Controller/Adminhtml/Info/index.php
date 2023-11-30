<?php
namespace Altmedia\Sms\Controller\Adminhtml\Info;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Altmedia_Sms::info');
        $resultPage->getConfig()->getTitle()->prepend(__('Info'));
        $resultPage->addBreadcrumb(__('Altmedia'), __('Altmedia'));
        $resultPage->addBreadcrumb(__('Sms'), __('Info'));

        return $resultPage;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return true;
    }
}
