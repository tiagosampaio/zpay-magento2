<?php

namespace ZPay\Standard\Controller\Adminhtml\System\Config\Credentials;

use ZPay\Standard\Model\Service\Api;
use ZPay\Standard\Model\Config\Source\Environment;
use Magento\Backend\App\Action;

class Validate extends Action
{

    /** @var Api */
    protected $api;

    public function __construct(Action\Context $context, Api $api)
    {
        parent::__construct($context);
        $this->api = $api;
    }

    /**
     * Execute the configuration validation.
     */
    public function execute()
    {
        $environment = $this->getRequest()->getPost('environment');
        $username = $this->getRequest()->getPost('username');
        $password = $this->getRequest()->getPost('password');
        $contractId = $this->getRequest()->getPost('contract_id');

        if (!$this->validateParameters($environment, $username, $password, $contractId)) {
            return $this->sendJsonResponse(false, __('All the parameters are required.'));
        }

        try {
            $token = $this->api
                ->setContractId($contractId)
                ->setUsername($username)
                ->setPassword($password)
                ->setEnvironment($environment)
                ->getToken();

            if (!$token) {
                return $this->sendJsonResponse(false, __('Invalid credentials.'));
            }
        } catch (\Exception $e) {
            return $this->sendJsonResponse(false, __($e->getMessage()));
        }

        return $this->sendJsonResponse(true, __('Your credentials are valid!'));
    }

    /**
     * @param bool        $success
     * @param null|string $message
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function sendJsonResponse($success = true, $message = null)
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $resultJson->setData([
            'success' => (bool) $success,
            'message' => $message
        ]);

        return $resultJson;
    }

    /**
     * @param null|string $environment
     * @param null|string $username
     * @param null|string $password
     * @param null|string $contractId
     *
     * @return bool
     */
    protected function validateParameters($environment = null, $username = null, $password = null, $contractId = null)
    {
        if (empty($environment)) {
            return false;
        }

        $allowedEnvironments = [
            Environment::PRODUCTION,
            Environment::SANDBOX
        ];

        if (!in_array($environment, $allowedEnvironments)) {
            return false;
        }

        if (empty($username)) {
            return false;
        }

        if (empty($password)) {
            return false;
        }

        if (empty($contractId)) {
            return false;
        }

        return true;
    }
}
