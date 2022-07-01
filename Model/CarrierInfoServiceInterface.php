<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Model;

use DateTime;

interface CarrierInfoServiceInterface
{

    /**
     * Check for a given date if the that day there is carrier service usually
     *
     * @param DateTime $dateTime
     * @return bool
     */
    public function isCarrierWorkDay(DateTime $dateTime): bool;


    /**
     * Retrieve list of dates which there are no carrier service
     *
     * @return array
     */
    public function getExcludedDatesForCarrier(): array;

    /**
     * Check for a given date if that date is excluded for carrier service
     *
     * @param DateTime $dateTime
     * @return bool
     */
    public function isExcludedCarrierDate(DateTime $dateTime): bool;

    /**
     * Calculate the shipping date  for a given order processed date
     * @param DateTime $orderProcessedDate
     * @return DateTime
     */
    public function getShippingDate(DateTime $orderProcessedDate): DateTime;

    /**
     * Retrieves the delivery day with delivery service for a given shipping date
     *
     * @param DateTime $shippingDate
     * @return DateTime
     */
    public function getDeliveryDate(DateTime $shippingDate): DateTime;
}
