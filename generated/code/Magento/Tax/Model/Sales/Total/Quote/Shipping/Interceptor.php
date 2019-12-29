<?php
namespace Magento\Tax\Model\Sales\Total\Quote\Shipping;

/**
 * Interceptor class for @see \Magento\Tax\Model\Sales\Total\Quote\Shipping
 */
class Interceptor extends \Magento\Tax\Model\Sales\Total\Quote\Shipping implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Tax\Model\Config $taxConfig, \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService, \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory, \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory, \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory, \Magento\Customer\Api\Data\AddressInterfaceFactory $customerAddressFactory, \Magento\Customer\Api\Data\RegionInterfaceFactory $customerAddressRegionFactory, ?\Magento\Tax\Helper\Data $taxHelper = null, ?\Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterfaceFactory $quoteDetailsItemExtensionInterfaceFactory = null)
    {
        $this->___init();
        parent::__construct($taxConfig, $taxCalculationService, $quoteDetailsDataObjectFactory, $quoteDetailsItemDataObjectFactory, $taxClassKeyDataObjectFactory, $customerAddressFactory, $customerAddressRegionFactory, $taxHelper, $quoteDetailsItemExtensionInterfaceFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function mapItem(\Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory, \Magento\Quote\Model\Quote\Item\AbstractItem $item, $priceIncludesTax, $useBaseCurrency, $parentCode = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'mapItem');
        if (!$pluginInfo) {
            return parent::mapItem($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency, $parentCode);
        } else {
            return $this->___callPlugins('mapItem', func_get_args(), $pluginInfo);
        }
    }
}
