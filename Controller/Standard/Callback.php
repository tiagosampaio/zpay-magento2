<?php
/**
 * ZPay Payment Gateway
 *
 * @category ZPay
 * @package ZPay\Standard
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 * @link https://github.com/tiagosampaio
 * @link https://tiagosampaio.com
 *
 * Copyright (c) 2019.
 */

namespace ZPay\Standard\Controller\Standard;

use ZPay\Standard\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Callback
 *
 * @package ZPay\Standard\Controller\Standard
 */
class Callback extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \ZPay\Standard\Model\Logger\LoggerInterface
     */
    private $logger;
    
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \ZPay\Standard\Api\CallbackProcessorInterface
     */
    private $callbackProcessor;

    /**
     * Callback constructor.
     *
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \ZPay\Standard\Model\Logger\LoggerInterface      $logger
     * @param \ZPay\Standard\Api\CallbackProcessorInterface    $callbackProcessor
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \ZPay\Standard\Model\Logger\LoggerInterface $logger,
        \ZPay\Standard\Api\CallbackProcessorInterface $callbackProcessor
    ) {
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->callbackProcessor = $callbackProcessor;
        
        parent::__construct($context);
    }
    
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->prepareRequestLogging();
        
        $zPayOrderId = $this->getOrderId();

        try {
            $this->callbackProcessor->processCallback($zPayOrderId);
        } catch (LocalizedException $e) {
            return $this->createResult($e->getHttpCode(), __($e->getMessage()));
        } catch (\Exception $e) {
            return $this->createResult(
                \ZPay\Standard\Api\CallbackProcessorInterface::RESULT_CODE_ERROR,
                __('Some problem has occurred when trying to register a new invoice.')
            );
        }

        return $this->createResult(\ZPay\Standard\Api\CallbackProcessorInterface::RESULT_CODE_SUCCESS);
    }
    
    /**
     * @param int    $code
     * @param string $message
     * @param string $typeClass
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function createResult($code, $message = null, $typeClass = ResultFactory::TYPE_RAW)
    {
        /** @var \Magento\Framework\Controller\ResultInterface $result */
        $result = $this->resultFactory->create($typeClass);
        $result->setHttpResponseCode((int) $code);
        $result->setContents($message);
        
        return $result;
    }
    
    /**
     * Retrieve the order ID from request object.
     *
     * @return string|null
     */
    private function getOrderId()
    {
        /** @var string $orderId */
        $orderId = $this->getRequest()->getParam('order_id');
        
        if (!empty($orderId)) {
            return $orderId;
        }
        
        /** @var string $content */
        $content = $this->getRequest()->getContent();
        
        if (empty($content)) {
            return null;
        }
        
        try {
            $data = $this->serializer->unserialize($content);
        } catch (\Exception $e) {
            return null;
        }
        
        if (!isset($data['order_id'])) {
            return null;
        }
        
        return $data['order_id'];
    }
    
    /**
     * @return $this
     */
    private function prepareRequestLogging()
    {
        $this->logger->info($this->getRequest()->getContent());
        return $this;
    }
}
