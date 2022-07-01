<?php

namespace Pablobae\SimpleDeliveryDate\Model;

use DateInterval;
use DateTime;
use Pablobae\SimpleDeliveryDate\Helper\Configuration;

class CompanyProcessInfoService implements CompanyProcessInfoServiceInterface
{
    private Configuration $helperConfiguration;

    public function __construct(Configuration $configuration)
    {
        $this->helperConfiguration = $configuration;
    }


    /**
     * @inheirtDoc
     */
    public function isCompanyWorkDay(DateTime $dateTime): bool
    {
        $day = $dateTime->format('w');
        if (in_array($day, $this->helperConfiguration->getCompanyWorkDays())) {
            return true;
        }
        return false;
    }

    /**
     * @inheirtDoc
     */
    public function isExcludedCompanyProcessingDate(DateTime $dateTime): bool
    {
        $date = $dateTime->format("d/m/Y");

        $excludedProcessingDates = $this->helperConfiguration->getExcludedProcessingDates();
        foreach ($excludedProcessingDates as $excludedDate) {
            if ($date == $excludedDate->format("d/m/Y")) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheirtDoc
     */
    public function getFirstCompanyWorkDateSinceDate(DateTime $dateTime, bool $includeGivenDateTime = true): DateTime
    {
        $firstCompanyWorkDate = clone $dateTime;
        if (!$includeGivenDateTime) {
            $firstCompanyWorkDate->add(new DateInterval('P1D'));
        }

        while (!$this->isCompanyWorkDay($firstCompanyWorkDate) || $this->isExcludedCompanyProcessingDate($firstCompanyWorkDate)) {
            $firstCompanyWorkDate->add(new DateInterval('P1D'));
        }
        return $firstCompanyWorkDate;
    }

    /**
     * @inheirtDoc
     */
    public function getOrderProcessedDate(DateTime $orderDate): DateTime
    {
        $orderProcessedDate = clone $orderDate;

        $daysNeededForProcessing = $this->helperConfiguration->getDaysNeededForProcessing();
        $processOrderTodayLimit = $this->helperConfiguration->getProcessOrderTodayTimeLimit();
        $year = $orderDate->format('Y');
        $month = $orderDate->format('m');
        $day = $orderDate->format('d');
        $processLimitDateTime = new DateTime($year . '-' . $month . '-' . $day . ' ' . $processOrderTodayLimit);

        if ($daysNeededForProcessing == 0) {
            if ($orderDate > $processLimitDateTime) {
                $orderProcessedDate->add(new DateInterval('P1D'));
            }
            return $this->getFirstCompanyWorkDateSinceDate($orderProcessedDate);
        }

        $firstCompanyWorkDate = $this->getFirstCompanyWorkDateSinceDate($orderProcessedDate);
        if ($orderDate == $firstCompanyWorkDate && $orderDate <= $processLimitDateTime) {
            $daysNeededForProcessing--;
        }

        if ($orderDate != $firstCompanyWorkDate) {
            $daysNeededForProcessing--;
            $orderProcessedDate = $this->getFirstCompanyWorkDateSinceDate($orderProcessedDate);
        }


        while ($daysNeededForProcessing > 0) {
            $orderProcessedDate->add(new DateInterval('P1D'));
            $orderProcessedDate = $this->getFirstCompanyWorkDateSinceDate($orderProcessedDate);
            $daysNeededForProcessing--;
        }





        return $this->getFirstCompanyWorkDateSinceDate($orderProcessedDate);
    }


}
