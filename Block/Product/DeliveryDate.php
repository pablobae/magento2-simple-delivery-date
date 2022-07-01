<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Block\Product;

use DateTime;
use Exception;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Pablobae\SimpleDeliveryDate\Helper\Configuration;
use Magento\Framework\View\Element\Template;
use Pablobae\SimpleDeliveryDate\Model\DeliveryDateServiceInterface;

class DeliveryDate extends Template
{

    /**
     * @var Configuration
     */
    private Configuration $helperConfiguration;
    private DeliveryDateServiceInterface $deliveryDateService;
    private TimezoneInterface $timezone;


    /**
     * Constructor
     *
     * @param DeliveryDateServiceInterface $deliveryDateService
     * @param TimezoneInterface $timezone
     * @param Configuration $configuration
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DeliveryDateServiceInterface $deliveryDateService,
        TimezoneInterface $timezone,
        Configuration     $configuration,
        Template\Context  $context,
        array             $data = []
    )
    {
        $this->deliveryDateService = $deliveryDateService;
        $this->timezone = $timezone;
        $this->helperConfiguration = $configuration;
        parent::__construct($context, $data);

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
        return $this->helperConfiguration->isEnabledOpenDeliveryDate();
    }

    /**
     * @throws Exception
     */
    public function getDeliveryInformation(): array
    {
        $estimatedOrderDate = $this->timezone->date();
        $deliveryDates = $this->deliveryDateService->getDeliveryDates($estimatedOrderDate);

        $dates = [];
        foreach ($deliveryDates as $deliveryDate) {
            $dates[] = $this->timezone->date(new DateTime($deliveryDate->format('Y-m-d H:i:s')));
        }
        return $dates;
    }

}
