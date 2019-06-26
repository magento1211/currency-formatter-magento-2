<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_CurrencyFormatter
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\CurrencyFormatter\Plugin\Sale\Component;

use Magento\Directory\Model\Currency\DefaultLocator;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Sales\Ui\Component\Listing\Column\PurchasedPrice as ListingPurchasedPrice;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\CurrencyFormatter\Helper\Data as HelperData;
use Mageplaza\CurrencyFormatter\Model\Locale\DefaultFormat;
use Mageplaza\CurrencyFormatter\Plugin\AbstractFormat;
use Magento\Sales\Model\OrderFactory;

/**
 * Class Price
 * @package Mageplaza\CurrencyFormatter\Plugin\Sale\Component
 */
class PurchasedPrice extends AbstractFormat
{
    /**
     * @var OrderFactory
     */
    protected $_orderFactory;
    
    /**
     * PurchasedPrice constructor.
     * @param StoreManagerInterface $storeManager
     * @param HelperData $helperData
     * @param ResolverInterface $localeResolver
     * @param CurrencyInterface $localeCurrency
     * @param FormatInterface $localeFormat
     * @param DefaultFormat $defaultFormat
     * @param DefaultLocator $currencyLocator
     * @param RequestInterface $request
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        HelperData $helperData,
        ResolverInterface $localeResolver,
        CurrencyInterface $localeCurrency,
        FormatInterface $localeFormat,
        DefaultFormat $defaultFormat,
        DefaultLocator $currencyLocator,
        RequestInterface $request,
        OrderFactory $orderFactory
    ) {
        $this->_orderFactory = $orderFactory;
    
        parent::__construct(
            $storeManager,
            $helperData,
            $localeResolver,
            $localeCurrency,
            $localeFormat,
            $defaultFormat,
            $currencyLocator,
            $request
        );
    }
    
    /**
     * @param ListingPurchasedPrice $subject
     * @param callable $proceed
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function aroundPrepareDataSource(ListingPurchasedPrice $subject, callable $proceed, array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order = $this->_orderFactory->create()->load($item['entity_id']);
                $storeId = $order->getStoreId();
                
                if (!$this->_helperData->isEnabled($storeId)) {
                    return $proceed($dataSource);
                }

                $currencyCode = isset($item['order_currency_code'])
                    ? $item['order_currency_code']
                    : $item['base_currency_code'];
                $itemName = $subject->getData('name');

                $item[$itemName] = $this->formatCurrencyText($currencyCode, $item[$itemName], $storeId);
            }
        }
    
        return $dataSource;
    }
}
