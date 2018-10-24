<?php

namespace ZPay\Standard\Block\System\Config\Credentials;

use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;

class ValidateCredentials extends Field
{

    protected $_template = 'ZPay_Standard::system/config/credentials/validate.phtml';

    /**
     * @return string
     */
    public function getAjaxValidateUrl()
    {
        return $this->getUrl('zpay_standard/system_config_credentials/validate');
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * 
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate the button HTML.
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        /** @var Button $button */
        $button = $this->getLayout()->createBlock(Button::class);
        $button->setData([
                'id'    => 'validate_credentials',
                'class' => 'primary',
                'label' => __('Validate Credentials'),
            ]);

        return $button->toHtml();
    }
}
