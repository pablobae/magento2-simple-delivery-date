<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Block\Product;

use DateInterval;
use DateTime;
use Exception;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Pablobae\SimpleDeliveryDate\Helper\Configuration;
use Magento\Framework\View\Element\Template;

class DeliveryDate extends Template
{

    /**
     * @var Configuration
     */
    private $helperConfiguration;
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    /**
     * @var string
     */
    private $excludedProcessingDates;
    /**
     * @var int|mixed
     */
    private $deliveryDays;
    /**
     * @var mixed|string
     */
    private $workingDays;
    /**
     * @var array|string
     */
    private $excludedDeliveryDates;


    public function __construct(
        Configuration     $configuration,
        TimezoneInterface $timezone,
        Template\Context  $context,
        array             $data = []
    )
    {
        $this->helperConfiguration = $configuration;
        $this->timezone = $timezone;
        parent::__construct($context, $data);
        $this->excludedProcessingDates = $this->helperConfiguration->getExcludedProcessingDates();
        if ($this->helperConfiguration->useExcludedProcessingDatesAsExcludedDeliveryDates()) {
            $this->excludedDeliveryDates = $this->excludedProcessingDates;
        } else {
            $this->excludedDeliveryDates = $this->helperConfiguration->getExcludedDeliveryDates();
        }
        $this->workingDays = $this->helperConfiguration->getWorkingDays();
        $this->deliveryDays = $this->helperConfiguration->getDeliveryDays();
    }

    public function isEnableDeliveryDate(): bool
    {
        return $this->helperConfiguration->isEnabled();
    }

    public function showTimeLimit(): bool
    {
        return $this->helperConfiguration->showTimeLimit();
    }

    public function getTimeLimit(): string
    {
        return $this->helperConfiguration->getProcessOrderTodayTimeLimit();
    }

    public function isOpenDeliveryDateEnabled(): bool
    {
        return $this->helperConfiguration->isOpenDeliveryDateEnabled();
    }

    /**
     * @throws Exception
     */
    public function getDeliveryInformation(): array
    {
        $processOrderDateTime = $this->getEffectiveProcessOrderDate();
        $deliveryDates = $this->getDeliveryDates($processOrderDateTime);
        $dates = [];
        foreach ($deliveryDates as $deliveryDate) {
            $dates[] = $this->timezone->date(new DateTime($deliveryDate));
        }
        return $dates;
    }

    /**
     * @throws Exception
     */
    private function getEffectiveProcessOrderDate(): DateTime
    {
        $processOrderDateTime = $this->timezone->date();
        $processOrderTodayLimit = $this->helperConfiguration->getProcessOrderTodayTimeLimit();
        $year = $processOrderDateTime->format('Y');
        $month = $processOrderDateTime->format('m');
        $day = $processOrderDateTime->format('d');
        $orderTimeLimitDateTime = $this->timezone->date(new DateTime($year . '-' . $month . '-' . $day . ' ' . $processOrderTodayLimit));//->format('Y-m-d H:i:s');
        $timeOffSet = $orderTimeLimitDateTime->getOffset();
        $orderTimeLimitDateTime->sub(new DateInterval('PT' . $timeOffSet . 'S'));

        $daysNeededForProcessing = $this->helperConfiguration->getDaysNeededForProcessing();
        if ($processOrderDateTime < $orderTimeLimitDateTime && $daysNeededForProcessing > 0) {
            $daysNeededForProcessing--;
        }
        if (!$processOrderDateTime < $orderTimeLimitDateTime) {
            $processOrderDateTime->add(new DateInterval('P1D'));
        }
        if ($daysNeededForProcessing > 0) {
            $processOrderDateTime->add(new DateInterval('P' . $daysNeededForProcessing . 'D'));
        }
        while (!$this->isWorkingDay($processOrderDateTime)) {
            $processOrderDateTime = $this->getNextWorkingDay($processOrderDateTime);
        }

        return $processOrderDateTime;

    }

    /**
     * @throws Exception
     */
    private function getDeliveryDates(Datetime $orderDate): array
    {
        $dates = [];
        $daysNeededForDelivery = $this->helperConfiguration->getDaysNeededForDelivery();
        $dates[] = $this->getNextDeliveryDay($orderDate, $daysNeededForDelivery)->format('Y-m-d H:i:s');
        if ($this->helperConfiguration->isOpenDeliveryDateEnabled()) {
            $rangeDays = $this->helperConfiguration->getOpenDeliveryRangeDays();
//            $rangeDays--;
            $orderDate->add(new DateInterval('P' . $rangeDays . 'D'));
            $dates[] = $this->getNextDeliveryDay($orderDate)->format('Y-m-d H:i:s');
        }
        return $dates;
    }

    private function isWorkingDay(DateTime $dateTime): bool
    {
        $day = $dateTime->format('w');
        if (in_array($day, $this->workingDays)) {
            return true;
        }
        return false;
    }

    private function isDeliveryDay(DateTime $dateTime): bool
    {
        $day = $dateTime->format('w');
        if (in_array($day, $this->deliveryDays)) {
            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    private function getNextDeliveryDay(DateTime $dateTime, int $daysInterval = 0): DateTime
    {
        if ($daysInterval > 0) {
            $dateInterval = 'P' . $daysInterval . 'D';
            $dateTime = $dateTime->add(new DateInterval($dateInterval));
        }
        while (!$this->isDeliveryDay($dateTime) || $this->isExcludedDeliveryDate($dateTime)) {
            $dateTime = $dateTime->add(new DateInterval('P1D'));
        }
        return $dateTime;
    }

    /**
     * @throws Exception
     */
    private function getNextWorkingDay(DateTime $dateTime): DateTime
    {
        while (!$this->isWorkingDay($dateTime) || $this->isExcludedProcessingDate($dateTime)) {
            $dateTime = $dateTime->add(new DateInterval('P1D'));
        }
        return $dateTime;
    }

    private function isExcludedProcessingDate($dateTime): bool
    {
        $date = $dateTime->format("d/m/Y");

        foreach ($this->excludedProcessingDates as $excludedDate) {
            if ($date == $excludedDate->format("d/m/Y")) {
                return true;
            }
        }
        return false;
    }

    private function isExcludedDeliveryDate($dateTime): bool
    {
        $date = $dateTime->format("d/m/Y");

        foreach ($this->excludedDeliveryDates as $excludedDate) {
            if ($date == $excludedDate->format("d/m/Y")) {
                return true;
            }
        }
        return false;
    }
}
