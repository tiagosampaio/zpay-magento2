<?php

namespace ZPay\Standard\Model\Service;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Zend\Http\Request;
use Zend\Http\Response;
use ZPay\Standard\Exception\ServiceApiResponseException;
use ZPay\Standard\Helper\Config as ConfigHelper;
use ZPay\Standard\Model\Config\Source\Environment;
use Zend\Http\Client as HttpClient;
use Zend\Http\Request as HttpRequest;
use ZPay\Standard\Exception\InvalidObjectException;
use ZPay\Standard\Model\Service\Request\Body\Order as OrderRequestBody;

/**
 * Class Api
 *
 * @package ZPay\Standard\Model\Service
 */
class Api
{

    const TYPE_APPLICATION_JSON            = 'application/json';
    const TYPE_APPLICATION_FORM_URLENCODED = 'application/x-www-form-urlencoded';


    protected $baseUrl;
    protected $api;
    protected $token;
    protected $username;
    protected $password;
    protected $contractId;
    protected $environment;
    protected $configHelper;
    protected $objectManager;


    /**
     * Api constructor.
     *
     * @param ConfigHelper           $helperConfig
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ConfigHelper $helperConfig,
        ObjectManagerInterface $objectManager
    )
    {
        $this->configHelper = $helperConfig;
        $this->objectManager = $objectManager;

        $this->prepareBaseUrl($helperConfig->getEnvironment());

        $this->contractId = $helperConfig->getContractId();
        $this->username   = $helperConfig->getUsername();
        $this->password   = $helperConfig->getPassword();
    }


    /**
     * @return $this
     */
    public function init()
    {
        if (!$this->token) {
            $this->prepareToken();
        }

        return $this;
    }


    /**
     * @param Order $order
     *
     * @return \stdClass
     *
     * @throws InvalidObjectException
     * @throws ServiceApiResponseException
     * @throws \Exception
     */
    public function createOrder(Order $order)
    {
        $this->init();

        /** @var OrderRequestBody $orderBody */
        $orderBody = $this->objectManager->create(OrderRequestBody::class);
        $orderBody->setOrder($order);

        if (!$orderBody->validate()) {
            /** @todo Throw an exception */
            throw new InvalidObjectException(__('Order object is lacking some required information.'));
        }

        /** @var HttpClient $client */
        $client = $this->getHttpClient($this->getServiceUrl('order'))
            ->setRawBody((string) $orderBody)
            ->setMethod(Request::METHOD_POST);

        /** @var Response $response */
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
     */
    public function renewOrder($orderId)
    {
        $this->init();

        try {
            /** @var HttpClient $client */
            $client = $this->getHttpClient($this->getServiceUrl("order/renew/{$orderId}"))
                ->setMethod(Request::METHOD_GET);

            /** @var Response $response */
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
     * @param $zpayOrderId
     *
     * @return \stdClass
     */
    public function getOrderStatus($zpayOrderId)
    {
        $this->init();

        /** @var HttpClient $client */
        $client = $this->getHttpClient($this->getServiceUrl("order/{$zpayOrderId}"));
        $client->setMethod(Request::METHOD_GET);

        /** @var Response $response */
        $response = $client->send();

        /** @var \stdClass $result */
        $result = json_decode($response->getBody());

        return $result;
    }


    /**
     * @param string $uri
     * @param string $contentType
     *
     * @return HttpClient
     */
    protected function getHttpClient($uri, $contentType = self::TYPE_APPLICATION_JSON)
    {
        /** @var HttpClient $client */
        $client = new HttpClient($uri, [
            'timeout' => 10
        ]);

        $headers = [
            'Content-Type' => $contentType,
            'Accept'       => 'application/json',
        ];

        if ($this->token) {
            $headers['X-Auth-Token'] = $this->token;
        }

        $client->setHeaders($headers);
        $client->setEncType('application/json');

        return $client;
    }


    /**
     * @return $this
     */
    protected function prepareToken()
    {
        $parameters = [
            'username' => $this->username,
            'password' => $this->password
        ];

        /** @var HttpClient $client */
        $client = $this->getHttpClient($this->getServiceUrl('auth'), self::TYPE_APPLICATION_FORM_URLENCODED);
        $client->setRawBody(http_build_query($parameters));
        $client->setMethod(HttpRequest::METHOD_POST);

        /** @var \Zend\Http\Response $response */
        $response = $client->send();
        $result = json_decode($response->getBody(), true);

        if ($response->getStatusCode() != 200) {
            /** @todo Throw an exception here. */
        }

        if (!isset($result['token'])) {
            /** @todo Throw an exception here. */
        }

        $this->token = (string) $result['token'];

        return $this;
    }


    /**
     * @param string $environment
     *
     * @return $this
     */
    protected function setEnvironment($environment = Environment::PRODUCTION)
    {
        $this->environment = $environment;
        return $this;
    }


    /**
     * @param null|string $urlPath
     *
     * @return string
     */
    protected function getServiceUrl($urlPath = null)
    {
        return $this->baseUrl . $urlPath;
    }


    /**
     * @param $environment
     *
     * @return $this
     */
    protected function prepareBaseUrl($environment = Environment::PRODUCTION)
    {
        $this->setEnvironment($environment);
        $this->baseUrl = $this->configHelper->getServiceUrl($environment);

        return $this;
    }

}
