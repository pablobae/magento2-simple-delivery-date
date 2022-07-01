<?php
declare(strict_types=1);

namespace Pablobae\SimpleDeliveryDate\Test\Unit\Block\Product;

use DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pablobae\SimpleDeliveryDate\Block\Product\DeliveryDate;
use Pablobae\SimpleDeliveryDate\Helper\Configuration;
use Pablobae\SimpleDeliveryDate\Model\DeliveryDateServiceInterface;
use PHPUnit\Framework\TestCase;

class DeliveryDateTest extends TestCase
{

    private Configuration $configurationMock;
    private DeliveryDate $deliveryDateObject;

    public function setUp(): void
    {
        parent::setUp();

        $deliveryDateServiceMock = $this->getMockBuilder(DeliveryDateServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $timezoneMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurationMock = $configurationMock;

        $this->deliveryDateObject = new DeliveryDate($deliveryDateServiceMock, $timezoneMock, $configurationMock, $contextMock);
    }

    /**
     * Test object instance
     * @return void
     */
    public function testDeliveryDateObjectInstanceIsBlockProductDeliveryDate()
    {
        $this->assertInstanceOf(DeliveryDate::class, $this->deliveryDateObject);
    }

    /**
     * Test object instance
     * @return void
     */
    public function testDeliveryDateObjectInstanceIsTemplate()
    {
        $this->assertInstanceOf(Template::class, $this->deliveryDateObject);
    }


    public function testIsEnableDeliveryDateIsTrueWhenIsEnabledConfigurationIsTrue()
    {
        $this->configurationMock->method('isEnabled')
            ->willReturn(true);
        $expected = $this->configurationMock->isEnabled();

        $result = $this->deliveryDateObject->isEnableDeliveryDate();

        $this->assertEquals($expected, $result);
    }

    public function testIsEnableDeliveryDateIsFalseWhenIsEnabledConfigurationIsFalse()
    {
        $this->configurationMock->method('isEnabled')
            ->willReturn(false);

        $expected = $this->configurationMock->isEnabled();
        $result = $this->deliveryDateObject->isEnableDeliveryDate();

        $this->assertEquals($expected, $result);
    }

    public function testShowTimeLimitIsTrueWhenShowTimeLimitConfigurationIsTrue()
    {
        $this->configurationMock->method('showTimeLimit')
            ->willReturn(true);

        $expected = $this->configurationMock->showTimeLimit();
        $result = $this->deliveryDateObject->showTimeLimit();

        $this->assertEquals($expected, $result);
    }

    public function testShowTimeLimitIsFalseWhenShowTimeLimitConfigurationIsFalse()
    {
        $this->configurationMock->method('showTimeLimit')
            ->willReturn(false);

        $expected = $this->configurationMock->showTimeLimit();
        $result = $this->deliveryDateObject->showTimeLimit();

        $this->assertEquals($expected, $result);
    }

    public function testGetTimeLimitIsEqualToGetProcessOrderTodayLimitConfiguration()
    {
        $randDate = new DateTime();
        $randDate->setTime(mt_rand(0, 23), mt_rand(0, 59), mt_rand(0, 59));
        $timeLimit = $randDate->format('H:i:s');  //
        $this->configurationMock->method('getProcessOrderTodayTimeLimit')
            ->willReturn($timeLimit);

        $expected = $this->configurationMock->getProcessOrderTodayTimeLimit();
        $result = $this->deliveryDateObject->getTimeLimit();

        $this->assertEquals($expected, $result);
    }


    public function testIsOpenDeliveryDateEnabledIsTrueWhenIsOpenDeliveryDateEnabledConfigurationIsTrue()
    {
        $this->configurationMock->method('isEnabledOpenDeliveryDate')
            ->willReturn(true);

        $expected = $this->configurationMock->isEnabledOpenDeliveryDate();
        $result = $this->deliveryDateObject->isOpenDeliveryDateEnabled();

        $this->assertEquals($expected, $result);
    }

    public function testIsOpenDeliveryDateEnabledIsFalseWhenIsOpenDeliveryDateEnabledConfigurationIsFalse()
    {
        $this->configurationMock->method('isEnabledOpenDeliveryDate')
            ->willReturn(false);

        $expected = $this->configurationMock->isEnabledOpenDeliveryDate();
        $result = $this->deliveryDateObject->isOpenDeliveryDateEnabled();

        $this->assertEquals($expected, $result);
    }
}
