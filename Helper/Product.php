<?php

namespace Doofinder\Feed\Helper;

/**
 * Product class
 *
 * @package Doofinder\Feed\Helper
 */
class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory = null;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_imageHelper = $imageHelper;
        $this->_storeManager = $storeManager;
        $this->_stockRegistry = $stockRegistry;
        parent::__construct($context);
    }

    /**
     * Get product id
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function getProductId(\Magento\Catalog\Model\Product $product)
    {
        return $product->getId();
    }

    /**
     * Get product url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductUrl(\Magento\Catalog\Model\Product $product)
    {
        return $product->getProductUrl(false);
    }

    /**
     * Get product categories
     *
     * @todo This might need some optimalization
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Category[][]
     */
    public function getProductCategoriesWithParents(\Magento\Catalog\Model\Product $product)
    {
        $categoryIds = $product->getResource()->getCategoryIds($product);

        $categoryCollection = $this->_categoryCollectionFactory->create();
        $categoryCollection
            ->addIdFilter($categoryIds)
            ->addAttributeToSelect('name')
            ->load();

        $categories = array();

        foreach ($categoryCollection as $category) {
            $parents = $category->getParentCategories();
            $parents[$category->getId()] = $category;

            $categories[] = $parents;
        }

        return $categories;
    }

    /**
     * Get product image url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $size
     * @return string|null
     */
    public function getProductImageUrl(\Magento\Catalog\Model\Product $product, $size = null)
    {
        if ($product->hasImage()) {
            return $this->_imageHelper
                ->init($product, 'doofinder_image')
                ->resize($size)
                ->getUrl();
        }
    }

    /**
     * Get product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return round($product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(), 2);
    }

    /**
     * Get product availability
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductAvailability(\Magento\Catalog\Model\Product $product)
    {
        if ($this->getStockItem($product->getId())->getIsInStock()) {
            return 'in stock';
        }

        return 'out of stock';
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Get attribute text
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeName
     * @return string
     */
    public function getAttributeText(\Magento\Catalog\Model\Product $product, $attributeName)
    {
        return $product->getAttributeText($attributeName);
    }

    /**
     * Get quantity and stock status
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getQuantityAndStockStatus(\Magento\Catalog\Model\Product $product)
    {
        $qty = $this->getStockItem($product->getId())->getQty();
        $availability = $this->getProductAvailability($product);

        return implode(' - ', array_filter([$qty, $availability], function ($item) {
            return $item !== null;
        }));
    }

    /**
     * Get stock item
     *
     * @param int $productId
     * @return \Magento\CatalogInventory\Model\Stock\Item
     */
    protected function getStockItem($productId)
    {
        return $this->_stockRegistry->getStockItem($productId);
    }
}
