<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Model;

use DateInterval;
use DateTime;
use Pablobae\SimpleDeliveryDate\Helper\Configuration;

class CarrierInfoService implements CarrierInfoServiceInterface
{
    const SHIPPING_DATE_KEY = 'shippingDate';
    const OPEN_DELIVERY_KEY = 'openDelivery';
    const DELIVERY_DATES_KEY = 'deliveryDates';

    private Configuration $helperConfiguration;

    public function __construct(Configuration $configuration)
    {
        $this->helperConfiguration = $configuration;
    }


    /**
     * @param DateTime $dateTime
     * @return bool
     */
    public function isCarrierWorkDay(DateTime $dateTime): bool
    {
        $day = $dateTime->format('w');
        if (in_array($day, $this->helperConfiguration->getDeliveryWorkDays())) {
            return true;
        }
        return false;
    }


    /**
     * @inheirtDoc
     */
    public function getExcludedDatesForCarrier(): array
    {
        if ($this->helperConfiguration->useExcludedProcessingDatesAsExcludedDeliveryDates()) {
            return $this->helperConfiguration->getExcludedProcessingDates();
        } else {
            return $this->helperConfiguration->getExcludedDeliveryDates();
        }
    }


    /**
     * @inheirtDoc
     */
    public function isExcludedCarrierDate(DateTime $dateTime): bool
    {
        $date = $dateTime->format("d/m/Y");

        $excludedDeliveryDates = $this->getExcludedDatesForCarrier();
        foreach ($excludedDeliveryDates as $excludedDate) {
            if ($date == $excludedDate->format("d/m/Y")) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param DateTime $orderProcessedDate
     * @return DateTime
     */
    public function getShippingDate(DateTime $orderProcessedDate): DateTime
    {
        return $this->calculateFutureCarrierWorkDate($orderProcessedDate, 0);
    }

    /**
     * @inheirtDoc
     */
    public function getDeliveryDate(DateTime $shippingDate): DateTime
    {
        $daysNeededForDelivery = $this->helperConfiguration->getDaysNeededForDelivery();
        return $this->calculateFutureCarrierWorkDate($shippingDate, $daysNeededForDelivery);
    }


    private function calculateFutureCarrierWorkDate(Datetime $datetime, int $numCarrierWorkDaysFromGivenDate): DateTime
    {
        $carrierWorkDate = clone $datetime;

        if ($numCarrierWorkDaysFromGivenDate == 0) {
            while (!($this->isCarrierWorkDay($carrierWorkDate) && !$this->isExcludedCarrierDate($carrierWorkDate))) {
                $carrierWorkDate->add(new DateInterval('P1D'));
            }
            return $carrierWorkDate;
        }

        while ($numCarrierWorkDaysFromGivenDate > 0) {
            $carrierWorkDate->add(new DateInterval('P1D'));
            if ($this->isCarrierWorkDay($carrierWorkDate) && !$this->isExcludedCarrierDate($carrierWorkDate)) {
                $numCarrierWorkDaysFromGivenDate--;
            }
        }

        return $carrierWorkDate;
    }


    /**
     * Retrieve delivery information for the order date given
     * @param DateTime $orderProcessedDate
     * @return array
     */
    public function getDeliveryEstimation(DateTime $orderProcessedDate): array
    {
        $orderShippingDate = $this->getShippingDate($orderProcessedDate);
        $openDelivery = $this->helperConfiguration->isEnabledOpenDeliveryDate();

        $deliveryDates = [];
        $deliveryDate = $this->getDeliveryDate($orderShippingDate);
        $deliveryDates[] = $deliveryDate;
        if ($openDelivery) {
            $openRangeDays = $this->helperConfiguration->getOpenDeliveryRangeDays();
            $deliveryDates[] = $this->calculateFutureCarrierWorkDate($deliveryDate, $openRangeDays);
        }

        return [
            self::SHIPPING_DATE_KEY => $orderShippingDate,
            self::OPEN_DELIVERY_KEY => $openDelivery,
            self::DELIVERY_DATES_KEY => $deliveryDates
        ];
    }
}
