<?php

namespace ZPay\Standard\Model\Service;

use ZPay\Standard\Exception\ServiceApiResponseException;
use ZPay\Standard\Model\Config\Source\Environment;
use ZPay\Standard\Exception\InvalidObjectException;

/**
 * Class Api
 *
 * @package ZPay\Standard\Model\Service
 */
class Api implements \ZPay\Standard\Api\ServiceApiInterface
{

    /** @var string */
    const TYPE_APPLICATION_JSON            = 'application/json';
    
    /** @var string */
    const TYPE_APPLICATION_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    /** @var string */
    private $baseUrl;
    
    /** @todo Removed it further.  */
    private $api;
    
    /** @var string */
    private $token;
    
    /** @var string */
    private $username;
    
    /** @var string */
    private $password;
    
    /** @var string */
    private $contractId;
    
    /** @var string */
    private $environment;
    
    /** @var \ZPay\Standard\Helper\Config */
    private $configHelper;
    
    /** @var \ZPay\Standard\Model\Service\Request\Body\OrderFactory */
    private $orderBodyFactory;
    
    /** @var \Zend\Http\Client */
    private $client;
    
    public function __construct(
        \Zend\Http\Client $client,
        \ZPay\Standard\Model\Service\Request\Body\OrderFactory $orderBodyFactory,
        \ZPay\Standard\Helper\Config $helperConfig
    ) {
        $this->client = $client;
        $this->configHelper = $helperConfig;
        $this->orderBodyFactory = $orderBodyFactory;
        $this->contractId = $helperConfig->getContractId();
        $this->username = $helperConfig->getUsername();
        $this->password = $helperConfig->getPassword();
    }

    /**
     * @return $this
     * @throws ServiceApiResponseException
     */
    public function init()
    {
        $this->prepareBaseUrl($this->configHelper->getEnvironment());
        
        if (!$this->token) {
            $this->prepareToken();
        }

        return $this;
    }

    /**
     * @param string $contractId
     *
     * @return $this
     */
    public function setContractId($contractId)
    {
        $this->contractId = (string) $contractId;
        return $this;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param string $environment
     *
     * @return $this
     */
    public function setEnvironment($environment = Environment::PRODUCTION)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @return mixed
     * @throws ServiceApiResponseException
     */
    public function getToken()
    {
        $this->init();
        return $this->token;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \stdClass
     *
     * @throws InvalidObjectException
     * @throws ServiceApiResponseException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->init();

        /** @var \ZPay\Standard\Model\Service\Request\Body\Order $orderBody */
        $orderBody = $this->orderBodyFactory->create();
        $orderBody->setOrder($order);

        $errors = $orderBody->validate();

        if (is_array($errors) && !empty($errors)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Order object is lacking some required information. Errors: %1.', implode(', ', $errors))
            );
        }

        /** @var \Zend\Http\Client $client */
        $client = $this->getHttpClient($this->getServiceUrl('order'))
            ->setRawBody((string) $orderBody)
            ->setMethod(\Zend\Http\Request::METHOD_POST);

        /** @var \Zend\Http\Response $response */
        $response = $client->send();

        /** @var \stdClass $result */
        $result = json_decode($response->getBody());

        if (!in_array($response->getStatusCode(), [200, 201])) {
            throw new ServiceApiResponseException(__('Api service responded an error: %s', $result['message']));
        }

        if (!isset($result->order_id, $result->address, $result->amount_to)) {
            throw new ServiceApiResponseException(__('Api service responded an error: %s', $result['message']));
        }

        return $result;
    }

    /**
     * @param string $orderId
     *
     * @return bool|\stdClass
     *
     * @throws ServiceApiResponseException
     */
    public function renewOrder($orderId)
    {
        $this->init();

        try {
            /** @var \Zend\Http\Client $client */
            $client = $this->getHttpClient($this->getServiceUrl("order/renew/{$orderId}"))
                ->setMethod(\Zend\Http\Request::METHOD_GET);

            /** @var \Zend\Http\Response $response */
            $response = $client->send();

            /** @var \stdClass $result */
            $result = json_decode($response->getBody());

            if (!in_array($response->getStatusCode(), [200, 201])) {
                throw new ServiceApiResponseException(__('Api service responded an error: %s', $result['message']));
            }

            return $result;
        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     * @param string $zpayOrderId
     *
     * @return \stdClass
     *
     * @throws ServiceApiResponseException
     */
    public function getOrderStatus($zpayOrderId)
    {
        $this->init();

        /** @var \Zend\Http\Client $client */
        $client = $this->getHttpClient($this->getServiceUrl("order/{$zpayOrderId}"));
        $client->setMethod(\Zend\Http\Request::METHOD_GET);

        /** @var \Zend\Http\Response $response */
        $response = $client->send();

        /** @var \stdClass $result */
        $result = json_decode($response->getBody());

        return $result;
    }

    /**
     * @param string $uri
     * @param string $contentType
     *
     * @return \Zend\Http\Client
     */
    private function getHttpClient($uri, $contentType = self::TYPE_APPLICATION_JSON)
    {
        $this->client->setUri($uri);
        $this->client->setOptions([
            'timeout' => 10
        ]);

        $headers = [
            'Content-Type' => $contentType,
            'Accept'       => 'application/json',
        ];

        if ($this->token) {
            $headers['X-Auth-Token'] = $this->token;
        }
    
        $this->client->setHeaders($headers);
        $this->client->setEncType('application/json');

        return $this->client;
    }

    /**
     * @return $this
     *
     * @throws ServiceApiResponseException
     */
    private function prepareToken()
    {
        $parameters = [
            'username' => $this->username,
            'password' => $this->password
        ];

        $this->prepareBaseUrl($this->environment);

        /** @var \Zend\Http\Client $client */
        $client = $this->getHttpClient($this->getServiceUrl('auth'), self::TYPE_APPLICATION_FORM_URLENCODED);
        $client->setRawBody(http_build_query($parameters));
        $client->setMethod(\Zend\Http\Request::METHOD_POST);

        /** @var \Zend\Http\Response $response */
        $response = $client->send();
        $result = json_decode($response->getBody(), true);

        if ($response->getStatusCode() != 200) {
            /** @todo Throw an exception here. */
        }

        if (!isset($result['token'])) {
            /** @todo Throw an exception here. */
            throw new ServiceApiResponseException(__('Invalid credentials.'));
        }

        $this->token = (string) $result['token'];

        return $this;
    }

    /**
     * @param null|string $urlPath
     *
     * @return string
     */
    private function getServiceUrl($urlPath = null)
    {
        return $this->baseUrl . $urlPath;
    }

    /**
     * @param $environment
     *
     * @return $this
     */
    private function prepareBaseUrl($environment = Environment::PRODUCTION)
    {
        $this->setEnvironment($environment);
        $this->baseUrl = $this->configHelper->getServiceUrl($environment);

        return $this;
    }
}
