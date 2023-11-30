<?php

namespace Altmedia\Sms\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\App\Helper\AbstractHelper;

class Sms extends AbstractHelper
{
    protected $scopeConfig;
    protected $storeDate;
    protected $history;
    protected $resourceConfig;
    protected $collection;
    protected $storeManager;
    protected $filesystem;
    protected $directory;
    protected $curl;
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Altmedia\Sms\Model\HistoryFactory $history,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeDate = $date;
        $this->history = $history;
        $this->resourceConfig = $resourceConfig;
        $this->collection = $collectionFactory->create();
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->curl = $curl;
        $this->logger = $logger;
    }

    /**
     * @param string $phone
     * @param string $message
     * @param string $type
     */
    public function sendSMS(string $phone, string $message, string $type = 'order')
    {
        $username = $this->scopeConfig->getValue(
            'altmedia/sms/altmedia_username',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $password = $this->scopeConfig->getValue(
            'altmedia/sms/altmedia_password',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $from = $this->scopeConfig->getValue(
            'altmedia/sms/altmedia_from',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        ) ?: "1762";
        $simulation = $this->scopeConfig->getValue(
            'altmedia/sms/altmedia_simulation',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        if ($simulation && $type !== 'test') {
            $phone = $this->scopeConfig->getValue(
                'altmedia/sms/altmedia_simulation_number',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
        }
        $phone = $this->validatePhone($phone);

        if (!empty($phone) && !empty($username) && !empty($password)) {
            $url = 'https://sms-c00-gate.altmedia.ro:12020/api?'
                . 'command=submit'
                . '&username=' . urlencode($username)
                . '&password=' . urlencode(trim($password))
                . '&ani=' . urlencode($from)
                . '&dnis=' . urlencode($phone)
                . '&message=' . urlencode($message);

            $this->curl->setOption(CURLOPT_HEADER, 0);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, '1');
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->get($url);

            $success = $this->curl->getStatus() === 200;
            $history = $this->history->create();
            $history->setStatus($success ? 1 : 0);
            $history->setMessage($success ? "Sent" : $this->curl->getBody());
            $history->setDetails($success ? $this->getMessageId() : null);
            $history->setContent($message);
            $history->setType($type);
            $history->setSentOn($this->storeDate->date());
            $history->setPhone($phone);
            $history->save();
        }
    }

    /**
     * @param $phone
     * @return string
     */
    public function validatePhone($phone_number)
    {
        if (empty($phone_number)) {
            return '';
        }

        $phone_number = $this->clearPhoneNumber($phone_number);

        $cc = $this->scopeConfig->getValue(
            'altmedia/sms/altmedia_prefix',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        if ($cc === "INT") {
            return $phone_number;
        }

        $phone_number = ltrim($phone_number, '0');

        if (!preg_match('/^' . $cc . '/', $phone_number)) {
            $phone_number = $cc . $phone_number;
        }

        return $phone_number;
    }

    public function clearPhoneNumber($phone_number)
    {
        $phone_number = str_replace(['+', '-'], '', filter_var($phone_number, FILTER_SANITIZE_NUMBER_INT));
        //Strip spaces and non-numeric characters:
        $phone_number = preg_replace("/[^0-9]/", "", $phone_number);
        return $phone_number;
    }

    private function getMessageId()
    {
        $response = json_decode($this->curl->getBody(), true);
        return $response["message_id"];
    }
}
