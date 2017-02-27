<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * Class StartTime
 * @package Doofinder\Feed\Model\Config\Backend
 */
class StartTime extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     */
    protected $_timezone;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        array $data = []
    ) {
        $this->_timezone = $timezone;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        list($hours, $minutes, $seconds) = $this->getValue();
        $date = new \DateTime(null, new \DateTimeZone($this->_timezone->getConfigTimezone()));
        $date->setTime($hours, $minutes, $seconds);
        $date->setTimezone(new \DateTimeZone($this->_timezone->getDefaultTimezone()));

        $this->setValue(explode(',', $date->format('H,i,s')));

        return parent::beforeSave();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterLoad()
    {
        list($hours, $minutes, $seconds) = explode(',', $this->getValue());
        $date = new \DateTime(null, new \DateTimeZone($this->_timezone->getDefaultTimezone()));
        $date->setTime($hours, $minutes, $seconds);
        $date->setTimezone(new \DateTimeZone($this->_timezone->getConfigTimezone()));

        $this->setValue($date->format('H,i,s'));

        return parent::afterLoad();
    }
}