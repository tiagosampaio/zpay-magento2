<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Model\Service\Request\Body;

/**
 * Class Order
 *
 * @package ZPay\Standard\Model\Service\Request\Body
 */
class Order
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * @var \ZPay\Standard\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * Order constructor.
     *
     * @param \ZPay\Standard\Helper\Config                      $helperConfig
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \ZPay\Standard\Helper\Config $helperConfig,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->configHelper = $helperConfig;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return $this
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return bool|array
     */
    public function validate()
    {
        $this->errors = [];

        $this->validateOrder();
        $this->validateShippingAddress();
        $this->validateCustomer();

        if (!empty($this->errors)) {
            return $this->errors;
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
    private function extractCustomerTaxvat()
    {
        $taxvat = $this->getOrder()->getCustomerTaxvat();

        if (!$taxvat && ($customer = $this->getCustomer())) {
            $taxvat = $customer->getTaxvat();
        }

        return $taxvat;
    }

    /**
     * @return $this
     */
    private function validateOrder()
    {
        if (!$this->getOrder()) {
            $this->errors[] = __('There is no order available');
        }

        if (!$this->getOrder()->getRealOrderId()) {
            $this->errors[] = __('The order does not have an Increment ID');
        }

        if (!$this->configHelper->getContractId()) {
            $this->errors[] = __('The contract ID is missing for this order');
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function validateShippingAddress()
    {
        if (!$this->getOrder()->getShippingAddress()) {
            $this->errors[] = __('Shipping address is not available');
        }

        if (!$this->getOrder()->getShippingAddress()->getCountryId()) {
            $this->errors[] = __('Country ID is not set to shipping address');
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function validateCustomer()
    {
        if (!$this->extractCustomerTaxvat()) {
            $this->errors[] = __('Customer taxvat is not set in order neither in the customer data');
        }

        if (!$this->getOrder()->getCustomerFirstname()) {
            $this->errors[] = __("Customer's first name is not set");
        }

        if (!$this->getOrder()->getCustomerLastname()) {
            $this->errors[] = __("Customer's lastname is not set");
        }

        if (!$this->getOrder()->getCustomerEmail()) {
            $this->errors[] = __("Customer's e-mail is not set");
        }

        return $this;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function getCustomer()
    {
        try {
            $customer = $this->customerRepository->getById((int) $this->getOrder()->getCustomerId());

            return $customer;
        } catch (\Exception $e) {
            /** @todo Log the error at this point. */
        }

        return null;
    }
}
