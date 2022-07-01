<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Test\Unit\Model;

use DateTime;
use Pablobae\SimpleDeliveryDate\Helper\Configuration;
use Pablobae\SimpleDeliveryDate\Model\CarrierInfoService;
use Pablobae\SimpleDeliveryDate\Model\CarrierInfoServiceInterface;
use PHPUnit\Framework\TestCase;


class CarrierInfoServiceTest extends TestCase
{
    private const SHORT_DATEFORMAT = 'Y-m-d';

    private CarrierInfoServiceInterface $carrierInfoService;
    private Configuration $configurationMock;


    public function setUp(): void
    {
        parent::setUp();

        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurationMock = $configurationMock;

        $this->carrierInfoService = new CarrierInfoService($configurationMock);
    }

    /**
     * Test object instance
     * @return void
     */
    public function testDeliveryDateServiceObjectIsDeliveryDateServiceInterfaceInstance()
    {
        $this->assertInstanceOf(CarrierInfoServiceInterface::class, $this->carrierInfoService);
    }

    /**
     * Test if isCarrierWorkDay() retrieves true or false if the given date is a working day according to the configuration
     *
     * @dataProvider dataProviderIsCarrierWorkDay
     * @return void
     */
    public function testIsCarrierWorkDay(array $carrierWorkDays, DateTime $datetime, bool $expectedResult)
    {
        $this->configurationMock->method('getDeliveryWorkDays')
            ->willReturn($carrierWorkDays);

        $this->assertEquals($expectedResult, $this->carrierInfoService->isCarrierWorkDay($datetime));
    }

    /**
     * Test if getExcludedDatesForCarrier() retrieves the correct excluded days
     *
     * @dataProvider dataProviderGetExcludedDatesForCarrier
     * @return void
     */
    public function testGetExcludedDatesForCarrier(array $excludedDeliveryDates, array $excludedCompanyProcessingDates, bool $useExcludedProcessingDates, array $expectedResult)
    {
        $this->configurationMock->method('getExcludedProcessingDates')
            ->willReturn($excludedCompanyProcessingDates);
        $this->configurationMock->method('getExcludedDeliveryDates')
            ->willReturn($excludedDeliveryDates);
        $this->configurationMock->method('useExcludedProcessingDatesAsExcludedDeliveryDates')
            ->willReturn($useExcludedProcessingDates);

        $result = $this->carrierInfoService->getExcludedDatesForCarrier();

        $this->assertEquals($expectedResult, $result);
    }


    /**
     * Test if isExcludedCarrierDate() retrieves true or false depending on if the given date is date excluded according to the configuration
     *
     * @dataProvider dataProviderIsExcludedCarrierDate
     * @return void
     */
    public function testIsExcludedCarrierDate(array $excludedCarrierDates, array $excludedCompanyProcessingDates, bool $useCompanyProcessingDates, DateTime $datetime, bool $assertResult)
    {
        $this->configurationMock->method('getExcludedProcessingDates')
            ->willReturn($excludedCompanyProcessingDates);
        $this->configurationMock->method('getExcludedDeliveryDates')
            ->willReturn($excludedCarrierDates);
        $this->configurationMock->method('useExcludedProcessingDatesAsExcludedDeliveryDates')
            ->willReturn($useCompanyProcessingDates);

        $this->assertEquals($assertResult, $this->carrierInfoService->isExcludedCarrierDate($datetime));
    }

    /**
     * Test if getShippingDate() retrieves the correct shipping day for the given processed order day
     *
     * @dataProvider dataProviderGetShippingDate
     * @return void
     */
    public function testGetShippingDate(array $workDays, array $excludedCarrierDates, array $excludedCompanyProcessingDates, bool $useCompanyProcessingDates, DateTime $orderProcessedDate, DateTime $assertResult)
    {
        $this->configurationMock->method('getDeliveryWorkDays')
            ->willReturn($workDays);
        $this->configurationMock->method('getExcludedProcessingDates')
            ->willReturn($excludedCompanyProcessingDates);
        $this->configurationMock->method('getExcludedDeliveryDates')
            ->willReturn($excludedCarrierDates);
        $this->configurationMock->method('useExcludedProcessingDatesAsExcludedDeliveryDates')
            ->willReturn($useCompanyProcessingDates);

        $this->assertEquals($assertResult, $this->carrierInfoService->getShippingDate($orderProcessedDate));
    }

    /**
     * Test if getDeliveryDate() retrieves the correct next company work day
     *
     * @dataProvider dataProviderGetDeliveryDate
     * @return void
     */
    public function testGetDeliveryDate(DateTime $shippingDate, int $daysNeededForDelivery, array $deliveryWorkDays, array $excludedDeliveryDates, array $excludeCompanyProcessingDates, bool $useProcessingDates, DateTime $datetimeAssertResult)
    {
        $this->configurationMock->method('getDeliveryWorkDays')
            ->willReturn($deliveryWorkDays);
        $this->configurationMock->method('getExcludedProcessingDates')
            ->willReturn($excludeCompanyProcessingDates);
        $this->configurationMock->method('getExcludedDeliveryDates')
            ->willReturn($excludedDeliveryDates);
        $this->configurationMock->method('useExcludedProcessingDatesAsExcludedDeliveryDates')
            ->willReturn($useProcessingDates);
        $this->configurationMock->method('getDaysNeededForDelivery')
            ->willReturn($daysNeededForDelivery);

        $this->assertEquals($datetimeAssertResult, $this->carrierInfoService->getDeliveryDate($shippingDate));
    }

    /**
     * Test if getDeliveryEstimation() retrieves the correct next company work day
     *
     * @dataProvider dataProviderGetDeliveryEstimation
     * @return void
     */
    public function testGetDeliveryEstimation(DateTime $orderProcessedDate, int $daysNeededForDelivery, array $carrierWorkDays, array $excludedDeliveryDates, array $excludedCompanyProcessingDates, bool $useExcludedProcessingDates, bool $isEnabledOpenDelivery, int $openDeliveryRangeDays, array $expectedResult)
    {
        $this->configurationMock->method('getDeliveryWorkDays')
            ->willReturn($carrierWorkDays);
        $this->configurationMock->method('getExcludedProcessingDates')
            ->willReturn($excludedCompanyProcessingDates);
        $this->configurationMock->method('getExcludedDeliveryDates')
            ->willReturn($excludedDeliveryDates);
        $this->configurationMock->method('useExcludedProcessingDatesAsExcludedDeliveryDates')
            ->willReturn($useExcludedProcessingDates);
        $this->configurationMock->method('getDaysNeededForDelivery')
            ->willReturn($daysNeededForDelivery);
        $this->configurationMock->method('isEnabledOpenDeliveryDate')
            ->willReturn($isEnabledOpenDelivery);
        $this->configurationMock->method('getOpenDeliveryRangeDays')
            ->willReturn($openDeliveryRangeDays);

        $result = $this->carrierInfoService->getDeliveryEstimation($orderProcessedDate);

        $this->assertIsArray($result);
        $this->assertArrayHasKey(CarrierInfoService::DELIVERY_DATES_KEY, $result);
        $this->assertArrayHasKey(CarrierInfoService::OPEN_DELIVERY_KEY, $result);
        $this->assertArrayHasKey(CarrierInfoService::DELIVERY_DATES_KEY, $result);
        $this->assertIsArray($result[CarrierInfoService::DELIVERY_DATES_KEY]);
        $this->assertEquals($isEnabledOpenDelivery, $expectedResult[CarrierInfoService::OPEN_DELIVERY_KEY]);
        $this->assertEquals($result[CarrierInfoService::SHIPPING_DATE_KEY], $expectedResult[CarrierInfoService::SHIPPING_DATE_KEY]);
        $this->assertEquals($result[CarrierInfoService::OPEN_DELIVERY_KEY], $expectedResult[CarrierInfoService::OPEN_DELIVERY_KEY]);
        $this->assertEquals($result[CarrierInfoService::DELIVERY_DATES_KEY], $expectedResult[CarrierInfoService::DELIVERY_DATES_KEY]);
    }


    /***  DATA PROVIDERS ***/

    /**
     * @return array
     *  array $carrierWorkDays
     *  DateTime $dateTime
     *  bool expectedResult
     */
    public function dataProviderIsCarrierWorkDay(): array
    {
        $data = [];

        // case 0
        // allowed values: 0 => sunday, 1=>Monday, 2=>Tuesday, 3=>Wednesday, 4=>Thursday, 5=>Friday, 6=>Saturday
        $carrierWorkDays = [];
        $data[] = [
            $carrierWorkDays,
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2009-02-15'),
            false
        ];

        // case 1
        $carrierWorkDays = [0];
        $data[] = [
            $carrierWorkDays,
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            false
        ];

        // case 2
        $carrierWorkDays = [1];
        $data[] = [
            $carrierWorkDays,
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            false
        ];

        // case 3
        $carrierWorkDays = [2];
        $data[] = [
            $carrierWorkDays,
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            false
        ];

        // case 4
        $carrierWorkDays = [3];
        $data[] = [
            $carrierWorkDays,
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            false
        ];

        // case 5
        $carrierWorkDays = [4];
        $data[] = [
            $carrierWorkDays,
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            false
        ];

        // case 6
        $carrierWorkDays = [5];
        $data[] = [
            $carrierWorkDays,
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            true
        ];

        // case 7
        $carrierWorkDays = [6];
        $data[] = [
            $carrierWorkDays,
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            false
        ];

        // case 8
        $carrierWorkDays = [0, 1, 2, 3, 4, 6];
        $data[] = [
            $carrierWorkDays,
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            false
        ];

        // case 9
        $carrierWorkDays = [0, 1, 2, 3, 4, 5, 6];
        $data[] = [
            $carrierWorkDays,
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            true
        ];

        return $data;
    }


    /**
     * @return array
     *  Datetime[] $excludedDeliveryDates
     *  Datetime[] $excludedCompanyProcessingDates
     *  bool $useExcludedProcessingDates
     *  array $expectedResult
     */
    public function dataProviderGetExcludedDatesForCarrier(): array
    {
        $data = [];

        // case 0
        $useExcludedProcessingDates = false;
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $expectedResult = $excludedDeliveryDates;
        $data[] = [
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $expectedResult
        ];

        // case 1
        $useExcludedProcessingDates = true;
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $expectedResult = $excludedCompanyProcessingDates;
        $data[] = [
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $expectedResult
        ];

        return $data;
    }


    /**
     * @return array
     *  DateTime[] $excludedDeliveryDates,
     *  DateTime[] $excludedProcessingDates,
     *  bool useExcludedProcessingDaysInsteadExcluded,
     *  Datetime,
     *  bool $assertResult
     */
    public function dataProviderIsExcludedCarrierDate(): array
    {
        $data = [];
        // case 0
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $dateTime = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-03-10');
        $useProcessingDates = false;
        $expectedResult = false;
        $data[] = [
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $dateTime,
            $expectedResult
        ];

        // case 1
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $dateTime = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15');
        $useProcessingDates = false;
        $expectedResult = true;
        $data[] = [
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $dateTime,
            $expectedResult
        ];

        // case 2
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $dateTime = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15');
        $useProcessingDates = true;
        $expectedResult = false;
        $data[] = [
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $dateTime,
            $expectedResult
        ];

        // case 3
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $dateTime = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10');
        $useProcessingDates = false;
        $expectedResult = false;
        $data[] = [
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $dateTime,
            $expectedResult
        ];

        // case 4
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $dateTime = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10');
        $useProcessingDates = true;
        $expectedResult = true;
        $data[] = [
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $dateTime,
            $expectedResult
        ];

        return $data;
    }


    /**
     * @return array
     *  array $carrierWorkDays,
     *  Datetime[] $excludedDeliveryDates,
     *  DateTime[] $excludedProcessingDates,
     *  bool useExcludedProcessingDaysInsteadExcluded,
     *  Datetime $orderProcessedAt,
     *  DateTime $assertResult
     */
    public function dataProviderGetShippingDate(): array
    {
        $data = [];

        // case 0
        $carrierWorkDays = [1];
        // Monday
        $orderProcessedAt = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $useProcessingDates = false;
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $expectedResult = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');;
        $data[] = [
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $orderProcessedAt,
            $expectedResult
        ];

        // case 1
        $carrierWorkDays = [2];
        // Monday
        $orderProcessedAt = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $useProcessingDates = false;
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
        ];
        $expectedResult = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-19');;
        $data[] = [
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $orderProcessedAt,
            $expectedResult
        ];

        // case 2
        $carrierWorkDays = [2];
        // Monday
        $orderProcessedAt = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $useProcessingDates = false;
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-19'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $expectedResult = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-26');;
        $data[] = [
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $orderProcessedAt,
            $expectedResult
        ];

        // case 3
        $carrierWorkDays = [1];
        // Monday
        $orderProcessedAt = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $useProcessingDates = true;
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $expectedResult = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');;
        $data[] = [
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $orderProcessedAt,
            $expectedResult
        ];

        // case 4
        $carrierWorkDays = [1];
        // Monday
        $orderProcessedAt = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $useProcessingDates = true;
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-19'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
        ];
        $expectedResult = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25');;
        $data[] = [
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $orderProcessedAt,
            $expectedResult
        ];

        return $data;
    }

    /**
     * @return array
     *  Datetime $orderShippingDate,
     *  int $daysNeededForDelivery,
     *  array $carrierWorkDays,
     *  Datetime[] $excludedDeliveryDates,
     *  Datetime[] $excludedProcessingDates,
     *  bool useExcludedProcessingDaysInsteadExcluded,
     *  DateTime $assertResult
     */
    public function dataProviderGetDeliveryDate(): array
    {
        $data = [];

        // case 0
        // Monday
        $orderShippingDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $carrierWorkDays = [1];
        $daysNeededForDelivery = 0;
        $useProcessingDates = false;
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $expectedResult = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $data[] = [
            $orderShippingDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $expectedResult
        ];

        // case 1
        // Monday
        $orderShippingDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $carrierWorkDays = [1];
        $daysNeededForDelivery = 1;
        $useProcessingDates = false;
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $expectedResult = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25');
        $data[] = [
            $orderShippingDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $expectedResult
        ];

        // case 2
        // Monday
        $orderShippingDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $carrierWorkDays = [1];
        $daysNeededForDelivery = 1;
        $useProcessingDates = false;
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $expectedResult = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-02');
        $data[] = [
            $orderShippingDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $expectedResult
        ];

        // case 3
        // Monday
        $orderShippingDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $carrierWorkDays = [1];
        $daysNeededForDelivery = 1;
        $useProcessingDates = false;
        $excludedDeliveryDates = [];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25'),
        ];
        $expectedResult = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25');
        $data[] = [
            $orderShippingDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $expectedResult
        ];

        // case 4
        // Monday
        $orderShippingDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $carrierWorkDays = [1];
        $daysNeededForDelivery = 1;
        $useProcessingDates = true;
        $excludedDeliveryDates = [];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25'),
        ];
        $expectedResult = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-02');
        $data[] = [
            $orderShippingDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useProcessingDates,
            $expectedResult
        ];

        return $data;
    }


    /**
     * @return array
     *  Datetime $orderProcessedDate,
     *  int $daysNeededForDelivery,
     *  array $carrierWorkDays,
     *  Datetime[] $excludedDeliveryDates,
     *  Datetime[] $excludedCompanyProcessingDates,
     *  bool useExcludedProcessingDaysInsteadExcluded,
     *  bool isEnabledOpenDelivery
     *  int $openRangeDays
     *  array $expectedResult
     */
    public function dataProviderGetDeliveryEstimation(): array
    {
        // case 0
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 0;
        $carrierWorkDays = [1];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = false;
        $isEnabledOpenDelivery = false;
        $openRangeDays = 0;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
            CarrierInfoService::OPEN_DELIVERY_KEY => false,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 1
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 0;
        $carrierWorkDays = [2];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = false;
        $isEnabledOpenDelivery = false;
        $openRangeDays = 0;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-19'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-19')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 2
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 1;
        $carrierWorkDays = [2];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = false;
        $isEnabledOpenDelivery = false;
        $openRangeDays = 0;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-19'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-26')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 3
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 1;
        $carrierWorkDays = [2];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-19'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = false;
        $isEnabledOpenDelivery = false;
        $openRangeDays = 0;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-26'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-03')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 4
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 1;
        $carrierWorkDays = [2];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-19'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = true;
        $isEnabledOpenDelivery = false;
        $openRangeDays = 0;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-19'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-26')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 5
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 1;
        $carrierWorkDays = [2];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-19'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = true;
        $isEnabledOpenDelivery = false;
        $openRangeDays = 0;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-26'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-03')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // open delivery date
        // case 6
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 0;
        $carrierWorkDays = [1];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = false;
        $isEnabledOpenDelivery = true;
        $openRangeDays = 0;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 7
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 0;
        $carrierWorkDays = [1];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = false;
        $isEnabledOpenDelivery = true;
        $openRangeDays = 1;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 8
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 1;
        $carrierWorkDays = [1];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = false;
        $isEnabledOpenDelivery = true;
        $openRangeDays = 1;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25'),
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-02')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 9
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 1;
        $carrierWorkDays = [1];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-02'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = false;
        $isEnabledOpenDelivery = true;
        $openRangeDays = 1;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25'),
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-09')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 10
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 1;
        $carrierWorkDays = [1];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-02'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-10'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = true;
        $isEnabledOpenDelivery = true;
        $openRangeDays = 1;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25'),
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-02')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 11
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 1;
        $carrierWorkDays = [1];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-02'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = true;
        $isEnabledOpenDelivery = true;
        $openRangeDays = 1;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25'),
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-09')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        // case 11
        // Monday
        $orderProcessedDate = DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18');
        $daysNeededForDelivery = 1;
        $carrierWorkDays = [1];
        $excludedDeliveryDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-16'),
        ];
        $excludedCompanyProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-02'),
            DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-11'),
        ];
        $useExcludedProcessingDates = true;
        $isEnabledOpenDelivery = true;
        $openRangeDays = 2;
        $expectedResult = [
            CarrierInfoService::SHIPPING_DATE_KEY => DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-18'),
            CarrierInfoService::OPEN_DELIVERY_KEY => $isEnabledOpenDelivery,
            CarrierInfoService::DELIVERY_DATES_KEY => [
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-04-25'),
                DateTime::createFromFormat(self::SHORT_DATEFORMAT, '2022-05-16')
            ]
        ];
        $data[] = [
            $orderProcessedDate,
            $daysNeededForDelivery,
            $carrierWorkDays,
            $excludedDeliveryDates,
            $excludedCompanyProcessingDates,
            $useExcludedProcessingDates,
            $isEnabledOpenDelivery,
            $openRangeDays,
            $expectedResult
        ];

        return $data;
    }
}

