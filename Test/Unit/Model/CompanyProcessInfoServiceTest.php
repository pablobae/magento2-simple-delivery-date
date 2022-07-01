<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Test\Unit\Model;

use DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Pablobae\SimpleDeliveryDate\Helper\Configuration;
use Pablobae\SimpleDeliveryDate\Model\CompanyProcessInfoService;
use Pablobae\SimpleDeliveryDate\Model\CompanyProcessInfoServiceInterface;
use PHPUnit\Framework\TestCase;


class CompanyProcessInfoServiceTest extends TestCase
{
    private const SHORT_DATETIME_FORMAT = 'Y-m-d';
    private const LARGE_DATETIME_FORMAT = 'Y-m-d H:i:s';

    private TimezoneInterface $timezoneMock;
    private CompanyProcessInfoServiceInterface $companyProcessInfoService;
    private Configuration $configurationMock;

    public function setUp(): void
    {
        parent::setUp();

        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurationMock = $configurationMock;

        $this->timezoneMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->companyProcessInfoService =  new CompanyProcessInfoService($configurationMock);
    }

    /**
     * Test object instance
     * @return void
     */
    public function testDeliveryDateServiceObjectIsDeliveryDateServiceInterfaceInstance()
    {
        $this->assertInstanceOf(CompanyProcessInfoServiceInterface::class, $this->companyProcessInfoService);
    }

    /**
     * Test if IsCompanyWorkDay() retrieves true or false if the given date is a working day according to the configuration
     *
     * @dataProvider dataProviderIsWorkingDay
     * @return void
     */
    public function testIsCompanyWorkDay(array $workDays, DateTime $datetime, bool $assertResult)
    {
        $this->configurationMock->method('getCompanyWorkDays')
            ->willReturn($workDays);
        $this->configurationMock->method('getCompanyWorkDays')
            ->willReturn($workDays);

        $this->assertEquals($assertResult, $this->companyProcessInfoService->isCompanyWorkDay($datetime));
    }

    /**
     * Test if IsExcludedProcessingDate() retrieves true or false depending on if the given date is date excluded according to the configuration
     *
     * @dataProvider dataProviderIsExcludedDate
     * @return void
     */
    public function testIsExcludedProcessingDate(array $configExcludedDates, DateTime $datetime, bool $assertResult)
    {
        $this->configurationMock->method('getExcludedProcessingDates')
            ->willReturn($configExcludedDates);

        $this->assertEquals($assertResult, $this->companyProcessInfoService->isExcludedCompanyProcessingDate($datetime));
    }

    /**
     * Test if getFirstCompanyWorkDateSinceDate() retrieves the correct first company work day
     *
     * @dataProvider dataProviderGetFirstCompanyDay
     * @return void
     */
    public function testGetFirstCompanyWorkDateSinceDate(array $configCompanyWorkDays, array $configExcludedProcessingDates, DateTime $datetime, bool $includeGivenDate, DateTime $datetimeAssertResult)
    {
        $this->configurationMock->method('getCompanyWorkDays')
            ->willReturn($configCompanyWorkDays);
        $this->configurationMock->method('getExcludedProcessingDates')
            ->willReturn($configExcludedProcessingDates);

        $this->assertEquals($datetimeAssertResult, $this->companyProcessInfoService->getFirstCompanyWorkDateSinceDate($datetime, $includeGivenDate));
    }


    /**
     * Test if testGetEffectiveOrderDate() retrieves the correct effective order date
     *
     * @dataProvider dataProviderTestGetOrderProcessedDate
     * @return void
     * @throws \Exception
     */
    public function testGetOrderProcessedDate(array $configCompanyWorkDays, array $configExcludedProcessingDates, DateTime $datetime, string $processOrderTodayTimeLimit, int $daysNeededForProcessing, DateTime $datetimeAssertResult)
    {
        $this->configurationMock->method('getProcessOrderTodayTimeLimit')
            ->willReturn($processOrderTodayTimeLimit);
        $this->configurationMock->method('getDaysNeededForProcessing')
            ->willReturn($daysNeededForProcessing);
        $this->configurationMock->method('getCompanyWorkDays')
            ->willReturn($configCompanyWorkDays);
        $this->configurationMock->method('getExcludedProcessingDates')
            ->willReturn($configExcludedProcessingDates);
        $year = $datetime->format('Y');
        $month = $datetime->format('m');
        $day = $datetime->format('d');
        $processLimitDateTime = new DateTime($year . '-' . $month . '-' . $day . ' ' . $processOrderTodayTimeLimit);
        $this->timezoneMock->method('date')->willReturn($processLimitDateTime);

        $this->assertEquals($datetimeAssertResult, $this->companyProcessInfoService->getOrderProcessedDate($datetime));
    }

    /***  DATA PROVIDERS ***/

    /**
     * @return array
     *  array $workDays
     *  DateTime $datetime
     *  bool $assertResult
     */
    public function dataProviderIsWorkingDay(): array
    {
        $data = [];

        // Case 0
        $data[] = [
            [],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2009-02-15'),
            false
        ];

        // Case 1
        $data[] = [
            [0],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            false
        ];

        // Case 2
        $data[] = [
            [1],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            false
        ];

        // Case 3
        $data[] = [
            [2],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            false
        ];

        // Case 4
        $data[] = [
            [3],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            false
        ];

        // Case 5
        $data[] = [
            [4],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            false
        ];

        // Case 6
        $data[] = [
            [5],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            true
        ];

        // Case 7
        $data[] = [
            [6],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            false
        ];

        // Case 8
        $data[] = [
            [0, 1, 2, 3, 4, 6],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            false
        ];

        // Case 9
        $data[] = [
            [0, 1, 2, 3, 4, 5, 6],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            true
        ];

        return $data;
    }

    /**
     * @return array
     *  DateTime[] $configExcludedDates,
     *  DateTime $dateTime
     *  bool $assertResult
     */
    public function dataProviderIsExcludedDate(): array
    {
        $data = [];

        // case 0
        $data[] = [
            [],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2009-02-15'),
            false
        ];

        // case 1
        $data[] = [
            [
                DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15')
            ],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            true
        ];

        // case 2
        $data[] = [
            [
                DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
                DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-02-15')
            ],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            true
        ];

        // case 3
        $data[] = [
            [
                DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-01-15'),
                DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-02-15')
            ],
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            false
        ];

        return $data;
    }

    /**
     * @return array
     *  array $configCompanyWorkDays,
     *  DateTime[] $configExcludedProcessingDates,
     *  DateTime $datetime,
     *  bool $includeGivenDate,
     *  DateTime $datetimeAssertResult
     */
    public function dataProviderGetFirstCompanyDay(): array
    {
        $data = [];

        // case 1
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        $datetime = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-17');
        $includeDatetime = true;
        $datetimeAssertResult = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $includeDatetime,
            $datetimeAssertResult
        ];

        // case 2
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        $datetime = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18');
        $includeDatetime = true;
        $datetimeAssertResult = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $includeDatetime,
            $datetimeAssertResult
        ];

        // case 3
        $configCompanyWorkDays = [2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        $datetime = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18');
        $includeDatetime = true;
        $datetimeAssertResult = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-19');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $includeDatetime,
            $datetimeAssertResult
        ];

        // case 4
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        $datetime = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-22');
        $includeDatetime = true;
        $datetimeAssertResult = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-22');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $includeDatetime,
            $datetimeAssertResult
        ];

        //case 5
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        $datetime = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-22');
        $includeDatetime = false;
        $datetimeAssertResult = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-25');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $includeDatetime,
            $datetimeAssertResult
        ];

        // cases with excluded dates
        //case 6
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-17')
        ];
        // sunday
        $datetime = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-17');
        $includeDatetime = true;
        $datetimeAssertResult = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $includeDatetime,
            $datetimeAssertResult
        ];

        //case 7
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18')
        ];
        // sunday
        $datetime = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-17');
        $includeDatetime = true;
        $datetimeAssertResult = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-19');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $includeDatetime,
            $datetimeAssertResult
        ];

        // case 8
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18'),
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-19')
        ];
        $datetime = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-14');
        $includeDatetime = true;
        $datetimeAssertResult = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-14');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $includeDatetime,
            $datetimeAssertResult
        ];

        // case 9
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-15'),
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18'),
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-19')
        ];
        $datetime = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-14');
        $includeDatetime = false;
        $datetimeAssertResult = DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-20');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $includeDatetime,
            $datetimeAssertResult
        ];

        return $data;
    }

    /**
     * @return array
     *  array $configCompanyWorkDays,
     *  DateTime[] $configExcludedProcessingDates,
     *  DateTime $datetime,
     *  string $processOrderTodayTimeLimit,
     *  int $daysNeededForProcessing,
     *  DateTime $datetimeAssertResult
     */
    public function dataProviderTestGetOrderProcessedDate(): array
    {
        $data = [];

        // case 0
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        // sunday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-17 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 0;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 1
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        // Monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 0;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 2
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        //monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '18:00:00';
        $daysNeedForProcessing = 0;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 3
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        // monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '17:59:59';
        $daysNeedForProcessing = 0;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-19 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 4
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-17'),
        ];
        // sunday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-17 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 0;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 5
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18'),
        ];
        // sunday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-17 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 0;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-19 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 6
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18'),
        ];
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '18:00:00';
        $daysNeedForProcessing = 0;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-19 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 7
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18'),
        ];
        // monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '17:59:59';
        $daysNeedForProcessing = 0;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-19 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // cases with days needed for processing
        // case 8
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        // sunday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-17 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 1;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 9
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        // Monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 1;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 10
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '18:00:00';
        $daysNeedForProcessing = 1;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 11
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        // monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '17:59:59';
        $daysNeedForProcessing = 1;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-19 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // cases with days needed for processing and excluded processing dates
        // case 12
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-17'),
        ];
        // sunday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-17 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 1;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 13
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18'),
        ];
        // sunday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-17 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 1;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-19 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 14
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18'),
        ];
        // Monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 1;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-19 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 15
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18'),
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-19'),
        ];
        // Monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 1;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-20 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 16
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18'),
        ];
        // Monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '17:59:59';
        $daysNeedForProcessing = 1;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-19 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 17
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-19'),
        ];
        // Monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '17:59:59';
        $daysNeedForProcessing = 1;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-20 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 18
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        // sunday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-17 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 2;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-19 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 19
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [];
        // sunday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 2;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-19 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 20
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-18'),
        ];
        // monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 2;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-20 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 21
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-19'),
        ];
        // monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '22:00:00';
        $daysNeedForProcessing = 2;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-20 18:00:00');

        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 22
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
        ];
        // monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '17:59:59';
        $daysNeedForProcessing = 2;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-20 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 23
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-19'),
        ];
        // Monday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-18 18:00:00');
        $processTimeLimit = '17:59:59';
        $daysNeedForProcessing = 2;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-21 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 24
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-25'),
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-26'),
        ];
        // friday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-22 18:00:00');
        $processTimeLimit = '18:00:00';
        $daysNeedForProcessing = 2;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-27 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        // case 25
        $configCompanyWorkDays = [1, 2, 3, 4, 5];
        $configExcludedProcessingDates = [
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-25'),
            DateTime::createFromFormat(self::SHORT_DATETIME_FORMAT, '2022-04-26'),
        ];
        // Friday
        $datetime = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-22 18:00:00');
        $processTimeLimit = '17:59:59';
        $daysNeedForProcessing = 2;
        $datetimeAssertResult = DateTime::createFromFormat(self::LARGE_DATETIME_FORMAT, '2022-04-28 18:00:00');
        $data[] = [
            $configCompanyWorkDays,
            $configExcludedProcessingDates,
            $datetime,
            $processTimeLimit,
            $daysNeedForProcessing,
            $datetimeAssertResult
        ];

        return $data;
    }
}
