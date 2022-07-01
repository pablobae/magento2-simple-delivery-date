<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Model;

use DateTime;

class DeliveryDateService implements DeliveryDateServiceInterface
{

    private CarrierInfoServiceInterface $carrierInfoService;
    private CompanyProcessInfoServiceInterface $companyProcessInfoService;

    public function __construct(
        CarrierInfoServiceInterface        $carrierInfoService,
        CompanyProcessInfoServiceInterface $companyProcessInfoService
    )
    {
        $this->carrierInfoService = $carrierInfoService;
        $this->companyProcessInfoService = $companyProcessInfoService;
    }


    /**
     * @inheirtDoc
     */
    public function getDeliveryDates(DateTime $orderDateTime): array
    {
        $orderProcessedDate = $this->companyProcessInfoService->getOrderProcessedDate($orderDateTime);
        $deliveryInformation = $this->carrierInfoService->getDeliveryEstimation($orderProcessedDate);

        return $deliveryInformation[CarrierInfoService::DELIVERY_DATES_KEY];
    }

}
