<?php

namespace Pablobae\SimpleDeliveryDate\Block\Product;

use DateInterval;
use DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\BlockInterface;
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
    private $excludedDates;
    /**
     * @var int|mixed
     */
    private $deliveryDays;
    /**
     * @var mixed|string
     */
    private $workingDays;
    private bool $sameDayProcessing;

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
        $this->excludedDates = $this->helperConfiguration->getExcludedDates();
        $this->workingDays = $this->helperConfiguration->getWorkingDays();
        $this->deliveryDays = $this->helperConfiguration->getDeliveryDays();
        $this->sameDayProcessing = false;

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

    public function getDeliveryDates(): array
    {
        $processOrderDateTime = $this->getEffectiveProcessOrderDate();
        $daysNeededForDelivery = $this->helperConfiguration->getDaysNeededForDelivery();
        $deliveryDateRange = $this->getDayDeliveryDateRange($processOrderDateTime, $daysNeededForDelivery);
        $dates[] = $this->timezone->date(new DateTime($deliveryDateRange[0]));
        $dates[] = $this->timezone->date(new DateTime($deliveryDateRange[1]));

        return $dates;
    }

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
//        if (!$this->isWorkingDay($processOrderDateTime) || $processOrderDateTime > $orderTimeLimitDateTime) {
//            $processOrderDateTime = $this->getNextWorkingDay($processOrderDateTime);
//        }
        if ($this->isWorkingDay($processOrderDateTime) && $processOrderDateTime < $orderTimeLimitDateTime) {
            $this->sameDayProcessing = true;
            return $processOrderDateTime;
        }

        do {
            $processOrderDateTime = $this->getNextWorkingDay($processOrderDateTime);
        } while (!$this->isWorkingDay($processOrderDateTime));

        return $processOrderDateTime;

    }

    private function getDayDeliveryDateRange(Datetime $orderDate, int $daysNeededForDelivery): array
    {
        $dates = [];

        $dates[] = $this->getNextDeliveryDay($orderDate)->format('Y-m-d H:i:s');
        $dates[] = $this->getNextDeliveryDay($orderDate, $daysNeededForDelivery)->format('Y-m-d H:i:s');
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

    private function getNextDeliveryDay(DateTime $dateTime, int $numberOfDays = 1): DateTime
    {
        $daysInterval = 'P' . $numberOfDays . 'D';
        do {
            $dateTime = $dateTime->add(new DateInterval($daysInterval));
        } while (!$this->isDeliveryDay($dateTime) || $this->isExcludedDate($dateTime));
        return $dateTime;
    }

    private function getNextWorkingDay(DateTime $dateTime, int $numberOfDays = 1): DateTime
    {
        $daysInterval = 'P' . $numberOfDays . 'D';
        do {
            $dateTime = $dateTime->add(new DateInterval($daysInterval));
        } while (!$this->isWorkingDay($dateTime) || $this->isExcludedDate($dateTime));
        return $dateTime;
    }

    private function isExcludedDate($dateTime)
    {
        $date = $dateTime->format("d/m/Y");

        foreach ($this->excludedDates as $excludedDate) {
            if ($date == $excludedDate->format("d/m/Y")) {
                return true;
            }
        }
        return false;
    }
}
