<?php

namespace ZPay\Standard\Model\Service\Request\Body;

use Magento\Customer\Model\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order as SalesOrder;
use ZPay\Standard\Helper\Config;

class Order
{

    /** @var SalesOrder */
    protected $order;

    /** @var Config */
    protected $configHelper;

    /** @var ObjectManagerInterface */
    protected $objectManager;


    public function __construct(Config $helperConfig, ObjectManagerInterface $objectManager)
    {
        $this->configHelper = $helperConfig;
        $this->objectManager = $objectManager;
    }


    /**
     * @param SalesOrder $order
     *
     * @return $this
     */
    public function setOrder(SalesOrder $order)
    {
        $this->order = $order;
        return $this;
    }


    /**
     * @return SalesOrder
     */
    public function getOrder()
    {
        return $this->order;
    }


    /**
     * @return bool
     */
    public function validate()
    {
        if (!$this->getOrder()) {
            return false;
        }

        if (!$this->getOrder()->getShippingAddress()) {
            return false;
        }

        if (!$this->getOrder()->getShippingAddress()->getCountryId()) {
            return false;
        }

        if (!$this->extractCustomerTaxvat()) {
            return false;
        }

        if (!$this->getOrder()->getCustomerFirstname()) {
            return false;
        }

        if (!$this->getOrder()->getCustomerLastname()) {
            return false;
        }

        if (!$this->getOrder()->getCustomerEmail()) {
            return false;
        }

        if (!$this->getOrder()->getRealOrderId()) {
            return false;
        }

        if (!$this->configHelper->getContractId()) {
            return false;
        }

        return true;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        $orderData = [
            "assetTo"          => "BTC",
            "asset"            => "BRL",
            "contract_id"      => $this->configHelper->getContractId(),
            "contract"         => $this->configHelper->getContractId(),
            "notification_url" => $this->configHelper->getCallbackUrl(),
            "person_taxid"     => $this->extractCustomerTaxvat(),
            "person_firstname" => $this->getOrder()->getCustomerFirstname(),
            "person_surname"   => $this->getOrder()->getCustomerLastname(),
            "person_email"     => $this->getOrder()->getCustomerEmail(),
            "reference_id"     => $this->getOrder()->getRealOrderId(),
            "price"            => $this->getOrder()->getGrandTotal(),
            "address_country"  => $this->getOrder()->getShippingAddress()->getCountryId(),
        ];

        return $orderData;
    }


    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }


    /**
     * @return mixed|null|string
     */
    protected function extractCustomerTaxvat()
    {
        $taxvat = $this->getOrder()->getCustomerTaxvat();

        if (!$taxvat) {
            /** @var Customer $customer */
            $customer = $this->objectManager->create(Customer::class);
            $customer->load($this->getOrder()->getCustomerId());

            $taxvat = $customer->getTaxvat();
        }

        return $taxvat;
    }

}
