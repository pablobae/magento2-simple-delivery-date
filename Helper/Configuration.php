<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Helper;

use DateTime;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Configuration provided  methods to retrieve the configuration of the extension
 */
class Configuration extends AbstractHelper
{
    const XML_PATH_SECTION = 'simpledeliverydate/';
    const XML_PATH_GROUP_GENERAL = 'pablobae_deliverydate_general/';
    const XML_PATH_FIELD_STATUS = 'status';
    const XML_PATH_FIELD_PROCESS_ORDER_TODAY_TIME_LIMIT = 'process_order_today_time_limit';
    const XML_PATH_FIELD_DAYS_NEEDED_FOR_DELIVERY = 'days_needed_for_delivery';
    const XML_PATH_FIELD_SHOW_TIME_LIMIT = 'show_time_limit';
    const XML_PATH_FIELD_EXCLUDED_DATES = 'dynamic_field_excluded_dates';
    const XML_PATH_FIELD_WORKING_DAYS = 'working_days';
    const XML_PATH_FIELD_DAYS_WITH_DELIVERY = 'delivery_days';


    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Configuration constructor.
     * @param StoreManagerInterface $storeManager
     * @param SerializerInterface $serializer
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SerializerInterface   $serializer,
        Context               $context
    )
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
    }

    /**
     * Retrieve config value
     *
     * @param string $fieldPath
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfigValue(string $fieldPath, int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $fieldPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve if the extension is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $isEnabled = false;
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_GENERAL . self::XML_PATH_FIELD_STATUS;

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->getConfigValue($fieldPath, (int)$storeId);
            if ($value !== null) {
                $isEnabled = (bool)$value;
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $isEnabled;
    }


    /**
     * Retrieve Show time limit config value
     *
     * @return bool
     */
    public function showTimeLimit(): bool
    {
        $showTimeLimit = false;
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_GENERAL . self::XML_PATH_FIELD_SHOW_TIME_LIMIT;

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->getConfigValue($fieldPath, (int)$storeId);
            if ($value !== null) {
                $showTimeLimit = (bool)$value;
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $showTimeLimit;
    }

    /**
     * Retrieve process order today time limit value
     *
     * @return string
     */
    public function getProcessOrderTodayTimeLimit(): string
    {
        $timeLimit = '23:59:59';
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_GENERAL . self::XML_PATH_FIELD_PROCESS_ORDER_TODAY_TIME_LIMIT;

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->getConfigValue($fieldPath, (int)$storeId);
            if ($value !== null) {
                $timeLimit = str_replace(',', ':', $value);
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $timeLimit;
    }

    /**
     * Retrieve excluded dates
     *
     * @return array
     */
    public function getExcludedDates(): array
    {
        $excludedDates = [];
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_GENERAL . self::XML_PATH_FIELD_EXCLUDED_DATES;

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->getConfigValue($fieldPath, (int)$storeId);
            if ($value !== null) {
                $excludedDates = $this->getDatesFromSerializedDates($value);
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $excludedDates;
    }

    /**
     * Transforms serialized date string into Datetime objects
     *
     * @param $datesString
     * @return array
     */
    private function getDatesFromSerializedDates($datesString): array
    {
        $dates = [];
        if ($datesString !== null) {
            $decodedValue = $this->serializer->unserialize($datesString);

            foreach ((array)$decodedValue as $key => $data) {
                if ($key == '__empty') continue;
                if (!isset($data['date'])) continue;
                try {
                    $date = DateTime::createFromFormat('Y-m-d', $data['date']);
                    $dates[] = $date;
                } catch (\Exception $e) {
                    // Just skipping error values
                }
            }
        }
        return $dates;
    }

    /**
     * Retrieve number of days needed for delivery
     *
     * @return int
     */
    public function getDaysNeededForDelivery(): int
    {
        $days = 0;
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_GENERAL . self::XML_PATH_FIELD_DAYS_NEEDED_FOR_DELIVERY;

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->getConfigValue($fieldPath, (int)$storeId);
            if ($value !== null) {
                $days = (int)$value;
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $days;
    }


    /**
     * Days with Delivery
     *
     */
    public function getWorkingDays()
    {
        $workingDays = [];
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_GENERAL . self::XML_PATH_FIELD_WORKING_DAYS;

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->getConfigValue($fieldPath, (int)$storeId);
            if ($value !== null) {
                $workingDays = explode(',',$value);
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $workingDays;
    }

    /**
     * Days with Delivery
     *
     */
    public function getDeliveryDays()
    {
        $deliveryDays = [];
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_GENERAL . self::XML_PATH_FIELD_DAYS_WITH_DELIVERY;

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->getConfigValue($fieldPath, (int)$storeId);
            if ($value !== null) {
                $deliveryDays = explode(',',$value);
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $deliveryDays;
    }
}
