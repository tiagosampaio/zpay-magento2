<?php

namespace ZPay\Standard\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Pay extends Action
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }

}
