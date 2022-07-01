<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Model;

use DateTime;

interface DeliveryDateServiceInterface
{

    /**
     * Retrieve delivery dates for a given date (or for an order placed at this moment)
     *
     * @param DateTime|null $orderDateTime
     * @return array
     */
    public function getDeliveryDates(DateTime $orderDateTime): array;

}
