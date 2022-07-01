<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Model;

use DateTime;

interface CompanyProcessInfoServiceInterface
{

    /**
     * Check if a given date is a working day
     *
     * @param DateTime $dateTime
     * @return bool
     */
    public function isCompanyWorkDay(DateTime $dateTime): bool;

    /**
     * Check for a given date if that date is excluded for processing
     *
     * @param DateTime $dateTime
     * @return bool
     */
    public function isExcludedCompanyProcessingDate(DateTime $dateTime): bool;



    /**
     * Retrieves the next working day for a given date
     *
     * @param DateTime $dateTime
     * @param bool $includeGivenDateTime
     * @return DateTime
     */
    public function getFirstCompanyWorkDateSinceDate(DateTime $dateTime, bool $includeGivenDateTime = true): DateTime;



    /**
     * Retrieves the date when the order processing finishes for a given date
     *
     * @param DateTime|null $orderDate
     * @return DateTime
     */
    public function getOrderProcessedDate(DateTime $orderDate): DateTime;

}
