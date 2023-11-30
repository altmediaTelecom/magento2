<?php
namespace Altmedia\Sms\Controller\Adminhtml\Test;

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
        $resultPage->setActiveMenu('Altmedia_Sms::test');
        $resultPage->getConfig()->getTitle()->prepend(__('Test'));
        $resultPage->addBreadcrumb(__('Altmedia'), __('Altmedia'));
        $resultPage->addBreadcrumb(__('Sms'), __('Send Test SMS'));

        $phone = $this->getRequest()->getParam('phone');
        $message = $this->getRequest()->getParam('message');

        if (!empty($phone) && !empty($message)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->get('Altmedia\Sms\Helper\Sms');
            $helper->sendSMS($phone, $message, 'test');

            $messageBlock = $resultPage->getLayout()->createBlock(
                'Magento\Framework\View\Element\Messages',
                'answer'
            );
            $messageBlock->addSuccess('The message was sent.');
            $resultPage->getLayout()->setChild(
                'sms_messages',
                $messageBlock->getNameInLayout(),
                'answer_alias'
            );
        }

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
