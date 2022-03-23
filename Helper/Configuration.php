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
    const XML_PATH_GROUP_GENERAL = 'general/';
    const XML_PATH_GROUP_PROCESSING = 'processing_settings/';
    const XML_PATH_GROUP_DELIVERY = 'delivery_settings/';
    const XML_PATH_GROUP_FRONTEND = 'frontend/';

    const XML_PATH_FIELD_STATUS = 'status';

    const XML_PATH_FIELD_WORKING_DAYS = 'working_days';
    const XML_PATH_FIELD_DAYS_NEEDED_FOR_PROCESSING = 'days_needed_for_processing_the_order';
    const XML_PATH_FIELD_PROCESS_ORDER_TODAY_TIME_LIMIT = 'process_order_today_time_limit';
    const XML_PATH_FIELD_EXCLUDED_PROCESSING_DATES = 'excluded_processing_dates';

    const XML_PATH_FIELD_DAYS_WITH_DELIVERY = 'delivery_days';
    const XML_PATH_FIELD_DAYS_NEEDED_FOR_DELIVERY = 'days_needed_for_delivery';
    const XML_PATH_FIELD_ENABLE_OPEN_DELIVERY_DATE = 'enable_open_delivery_date';
    const XML_PATH_FIELD_OPEN_DELIVERY_RANGE_DAYS = 'open_delivery_range_days';
    const XML_PATH_FIELD_USE_EXCLUDED_PROCESSING_DATES_AS_EXCLUDED_DELIVERY_DATES = 'use_excluded_processing_dates_as_excluded_delivery_dates';
    const XML_PATH_FIELD_EXCLUDED_DELIVERY_DATES = 'excluded_delivery_dates';


    const XML_PATH_FIELD_SHOW_TIME_LIMIT = 'show_time_limit';


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
    protected function getConfigValue(string $fieldPath, int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $fieldPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve a boolean system field config value
     *
     * @param string $fieldPath
     * @param bool $defaultValue
     * @return bool
     */
    protected function getBoolConfigValue(string $fieldPath, bool $defaultValue = false): bool
    {
        $fieldValue = $defaultValue;

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->getConfigValue($fieldPath, (int)$storeId);
            if ($value !== null) {
                $fieldValue = (bool)$value;
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $fieldValue;
    }


    /**
     * Retrieves a boolean system field config value
     *
     * @param string $fieldPath
     * @param int $defaultValue
     * @return int
     */
    protected function getIntConfigValue(string $fieldPath, int $defaultValue = 0): int
    {
        $intValue = $defaultValue;
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->getConfigValue($fieldPath, (int)$storeId);
            if ($value !== null) {
                $intValue = (int)$value;
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $intValue;
    }


    /**
     * Retrieves array from string config value (multiselect,...)
     *
     * @param string $fieldPath
     * @param string $separator
     * @return array
     */
    protected function getArrayFromStringConfigValue(string $fieldPath, string $separator = ','): array
    {
        $data = [];
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->getConfigValue($fieldPath, (int)$storeId);
            if ($value !== null) {
                $data = explode($separator, $value);
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $data;
    }


    /**
     * Retrieve excluded dates
     *
     * @param string $fieldPath
     * @return array
     */
    protected function getExcludedDates(string $fieldPath): array
    {
        $excludedDates = [];

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



    /** GENERAL GROUP   **/


    /**
     * Retrieve if the extension is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_GENERAL . self::XML_PATH_FIELD_STATUS;
        return $this->getBoolConfigValue($fieldPath);
    }


    /** GENERAL GROUP   **/


    /**
     * Retrieve the list of working days
     *
     * @return array
     */
    public function getWorkingDays(): array
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_PROCESSING . self::XML_PATH_FIELD_WORKING_DAYS;
        return $this->getArrayFromStringConfigValue($fieldPath);
    }


    /**
     * Retrieve number of days needed for delivery
     *
     * @return int
     */
    public function getDaysNeededForProcessing(): int
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_PROCESSING . self::XML_PATH_FIELD_DAYS_NEEDED_FOR_PROCESSING;
        return $this->getIntConfigValue($fieldPath);
    }


    /**
     * Retrieve process order today time limit value
     *
     * @return string
     */
    public function getProcessOrderTodayTimeLimit(): string
    {
        $timeLimit = '23:59:59';
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_PROCESSING . self::XML_PATH_FIELD_PROCESS_ORDER_TODAY_TIME_LIMIT;

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
    public function getExcludedProcessingDates(): array
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_PROCESSING . self::XML_PATH_FIELD_EXCLUDED_PROCESSING_DATES;
        return $this->getExcludedDates($fieldPath);
    }



    /** DELIVERY SETTINGS */

    /**
     * Days with Delivery
     */
    public function getDeliveryDays(): array
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_DELIVERY . self::XML_PATH_FIELD_DAYS_WITH_DELIVERY;
        return $this->getArrayFromStringConfigValue($fieldPath);
    }


    /**
     * Retrieve number of days needed for delivery
     *
     * @return int
     */
    public function getDaysNeededForDelivery(): int
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_DELIVERY . self::XML_PATH_FIELD_DAYS_NEEDED_FOR_DELIVERY;
        return $this->getIntConfigValue($fieldPath);
    }

    /**
     * Retrieves if the Open delivery date feature is enable
     *
     * @return bool
     */
    public function isOpenDeliveryDateEnabled(): bool
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_DELIVERY . self::XML_PATH_FIELD_ENABLE_OPEN_DELIVERY_DATE;
        return $this->getBoolConfigValue($fieldPath);
    }

    /**
     * Retrieves open delivery range days value
     *
     * @return int
     */
    public function getOpenDeliveryRangeDays(): int
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_DELIVERY . self::XML_PATH_FIELD_OPEN_DELIVERY_RANGE_DAYS;
        return $this->getIntConfigValue($fieldPath);
    }


    /**
     * Retrieves use excluded processing dates as excluded delivery dates config value
     *
     * @return bool
     */
    public function useExcludedProcessingDatesAsExcludedDeliveryDates(): bool
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_DELIVERY . self::XML_PATH_FIELD_USE_EXCLUDED_PROCESSING_DATES_AS_EXCLUDED_DELIVERY_DATES;
        return $this->getBoolConfigValue($fieldPath);
    }


    /**
     * Retrieves excluded delivery dates
     *
     * @return array
     */
    public function getExcludedDeliveryDates(): array
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_DELIVERY . self::XML_PATH_FIELD_EXCLUDED_DELIVERY_DATES;
        return $this->getExcludedDates($fieldPath);
    }


    /**** FRONTEND SETTINGS ****/

    /**
     * Retrieve Show time limit config value
     *
     * @return bool
     */
    public function showTimeLimit(): bool
    {
        $fieldPath = self::XML_PATH_SECTION . self::XML_PATH_GROUP_FRONTEND . self::XML_PATH_FIELD_SHOW_TIME_LIMIT;
        return $this->getBoolConfigValue($fieldPath);
    }


    /** Misc */

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
}
