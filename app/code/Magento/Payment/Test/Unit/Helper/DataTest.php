<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use Magento\Payment\Helper\Data;
use Magento\Store\Model\ScopeInterface;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Payment\Helper\Data */
    private $helper;

    /**  @var \PHPUnit_Framework_MockObject_MockObject */
    private $scopeConfig;

    /**  @var \PHPUnit_Framework_MockObject_MockObject */
    private $paymentConfig;

    /**  @var \PHPUnit_Framework_MockObject_MockObject */
    private $initialConfig;

    /**  @var \PHPUnit_Framework_MockObject_MockObject */
    private $methodFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $appEmulation;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Magento\Payment\Helper\Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->scopeConfig = $context->getScopeConfig();
        $this->layoutMock = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $layoutFactoryMock = $arguments['layoutFactory'];
        $layoutFactoryMock->expects($this->once())->method('create')->willReturn($this->layoutMock);

        $this->methodFactory = $arguments['paymentMethodFactory'];
        $this->appEmulation = $arguments['appEmulation'];
        $this->paymentConfig = $arguments['paymentConfig'];
        $this->initialConfig = $arguments['initialConfig'];

        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @return void
     */
    public function testGetMethodInstance()
    {
        list($code, $class, $methodInstance) = ['method_code', 'method_class', 'method_instance'];

        $this->scopeConfig->expects(
            $this->once()
        )->method(
            'getValue'
        )->will(
            $this->returnValue(
                $class
            )
        );
        $this->methodFactory->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $class
        )->will(
            $this->returnValue(
                $methodInstance
            )
        );

        $this->assertEquals($methodInstance, $this->helper->getMethodInstance($code));
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testGetMethodInstanceWithException()
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn(null);

        $this->helper->getMethodInstance('code');
    }

    /**
     * @param array $methodA
     * @param array $methodB
     *
     * @dataProvider getSortMethodsDataProvider
     */
    public function testSortMethods(array $methodA, array $methodB)
    {
        $this->initialConfig->expects($this->once())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        \Magento\Payment\Helper\Data::XML_PATH_PAYMENT_METHODS => [
                            $methodA['code'] => $methodA['data'],
                            $methodB['code'] => $methodB['data'],
                            'empty' => [],

                        ]
                    ]
                )
            );

        $this->scopeConfig->expects(new MethodInvokedAtIndex(0))
            ->method('getValue')
            ->with(sprintf('%s/%s/model', Data::XML_PATH_PAYMENT_METHODS, $methodA['code']))
            ->will($this->returnValue(\Magento\Payment\Model\Method\AbstractMethod::class));
        $this->scopeConfig->expects(new MethodInvokedAtIndex(1))
            ->method('getValue')
            ->with(
                sprintf('%s/%s/model', Data::XML_PATH_PAYMENT_METHODS, $methodB['code'])
            )
            ->will($this->returnValue(\Magento\Payment\Model\Method\AbstractMethod::class));
        $this->scopeConfig->expects(new MethodInvokedAtIndex(2))
            ->method('getValue')
            ->with(sprintf('%s/%s/model', Data::XML_PATH_PAYMENT_METHODS, 'empty'))
            ->will($this->returnValue(null));

        $methodInstanceMockA = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $methodInstanceMockA->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $methodInstanceMockA->expects($this->any())
            ->method('getConfigData')
            ->with('sort_order', null)
            ->will($this->returnValue($methodA['data']['sort_order']));

        $methodInstanceMockB = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $methodInstanceMockB->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $methodInstanceMockB->expects($this->any())
            ->method('getConfigData')
            ->with('sort_order', null)
            ->will($this->returnValue($methodB['data']['sort_order']));

        $this->methodFactory->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($methodInstanceMockA));

        $this->methodFactory->expects($this->at(1))
            ->method('create')
            ->will($this->returnValue($methodInstanceMockB));

        $sortedMethods = $this->helper->getStoreMethods();
        $this->assertTrue(
            array_shift($sortedMethods)->getConfigData('sort_order')
            < array_shift($sortedMethods)->getConfigData('sort_order')
        );
    }

    /**
     * @return void
     */
    public function testGetMethodFormBlock()
    {
        list($blockType, $methodCode) = ['method_block_type', 'method_code'];

        $methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setMethod', 'toHtml'])
            ->getMock();

        $methodMock->expects($this->once())->method('getFormBlockType')->willReturn($blockType);
        $methodMock->expects($this->once())->method('getCode')->willReturn($methodCode);
        $layoutMock->expects($this->once())->method('createBlock')
            ->with($blockType, $methodCode)
            ->willReturn($blockMock);
        $blockMock->expects($this->once())->method('setMethod')->with($methodMock);

        $this->assertSame($blockMock, $this->helper->getMethodFormBlock($methodMock, $layoutMock));
    }

    /**
     * @return void`
     */
    public function testGetInfoBlock()
    {
        $blockType = 'method_block_type';

        $methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $infoMock = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setInfo', 'toHtml'])
            ->getMock();

        $infoMock->expects($this->once())->method('getMethodInstance')->willReturn($methodMock);
        $methodMock->expects($this->once())->method('getInfoBlockType')->willReturn($blockType);
        $this->layoutMock->expects($this->once())->method('createBlock')
            ->with($blockType)
            ->willReturn($blockMock);
        $blockMock->expects($this->once())->method('setInfo')->with($infoMock);

        $this->assertSame($blockMock, $this->helper->getInfoBlock($infoMock));
    }

    /**
     * @return void
     */
    public function testGetInfoBlockHtml()
    {
        list($storeId, $blockHtml, $secureMode, $blockType) = [1, 'HTML MARKUP', true, 'method_block_type'];

        $methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $infoMock = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $paymentBlockMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setArea', 'setIsSecureMode', 'getMethod', 'setStore', 'toHtml', 'setInfo'])
            ->getMock();

        $this->appEmulation->expects($this->once())->method('startEnvironmentEmulation')->with($storeId);
        $infoMock->expects($this->once())->method('getMethodInstance')->willReturn($methodMock);
        $methodMock->expects($this->once())->method('getInfoBlockType')->willReturn($blockType);
        $this->layoutMock->expects($this->once())->method('createBlock')
            ->with($blockType)
            ->willReturn($paymentBlockMock);
        $paymentBlockMock->expects($this->once())->method('setInfo')->with($infoMock);
        $paymentBlockMock->expects($this->once())->method('setArea')
            ->with(\Magento\Framework\App\Area::AREA_FRONTEND)
            ->willReturnSelf();
        $paymentBlockMock->expects($this->once())->method('setIsSecureMode')
            ->with($secureMode);
        $paymentBlockMock->expects($this->once())->method('getMethod')
            ->willReturn($methodMock);
        $methodMock->expects($this->once())->method('setStore')->with($storeId);
        $paymentBlockMock->expects($this->once())->method('toHtml')
            ->willReturn($blockHtml);
        $this->appEmulation->expects($this->once())->method('stopEnvironmentEmulation');

        $this->assertEquals($blockHtml, $this->helper->getInfoBlockHtml($infoMock, $storeId));
    }

    /**
     * @return array
     */
    public function getSortMethodsDataProvider()
    {
        return [
            [
                ['code' => 'methodA', 'data' => ['sort_order' => 0]],
                ['code' => 'methodB', 'data' => ['sort_order' => 1]]
            ],
            [
                ['code' => 'methodA', 'data' => ['sort_order' => 2]],
                ['code' => 'methodB', 'data' => ['sort_order' => 1]],
            ]
        ];
    }

    /**
     * @param bool $sorted
     * @param bool $asLabelValue
     * @param bool $withGroups
     * @param string|null $configTitle
     * @param array $paymentMethod
     * @param array $expectedPaymentMethodList
     * @return void
     *
     * @dataProvider paymentMethodListDataProvider
     */
    public function testGetPaymentMethodList(
        bool $sorted,
        bool $asLabelValue,
        bool $withGroups,
        $configTitle,
        array $paymentMethod,
        array $expectedPaymentMethodList
    ) {
        $groups = ['group' => 'Group Title'];

        $this->initialConfig->method('getData')
            ->with('default')
            ->willReturn(
                [
                    Data::XML_PATH_PAYMENT_METHODS => [
                        $paymentMethod['code'] => $paymentMethod['data'],
                    ],
                ]
            );

        $titlePath = sprintf('%s/%s/title', Data::XML_PATH_PAYMENT_METHODS, $paymentMethod['code']);
        $this->scopeConfig->method('getValue')
            ->with($titlePath, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($configTitle);

        $this->paymentConfig->method('getGroups')
            ->willReturn($groups);

        $paymentMethodList = $this->helper->getPaymentMethodList($sorted, $asLabelValue, $withGroups);
        $this->assertEquals($expectedPaymentMethodList, $paymentMethodList);
    }

    /**
     * @return array
     */
    public function paymentMethodListDataProvider(): array
    {
        return [
            'Payment method with changed title' =>
                [
                    true,
                    false,
                    false,
                    'Config Payment Title',
                    [
                        'code' => 'payment_method',
                        'data' => [
                            'active' => 1,
                            'title' => 'Payment Title',
                        ],
                    ],
                    ['payment_method' => 'Config Payment Title'],
                ],
            'Payment method as value => label' =>
                [
                    true,
                    true,
                    false,
                    'Payment Title',
                    [
                        'code' => 'payment_method',
                        'data' => [
                            'active' => 1,
                            'title' => 'Payment Title',
                        ],
                    ],
                    [
                        'payment_method' => [
                            'value' => 'payment_method',
                            'label' => 'Payment Title',
                        ],
                    ],
                ],
            'Payment method with group' =>
                [
                    true,
                    true,
                    true,
                    'Payment Title',
                    [
                        'code' => 'payment_method',
                        'data' => [
                            'active' => 1,
                            'title' => 'Payment Title',
                            'group' => 'group',
                        ],
                    ],
                    [
                        'group' => [
                            'label' => 'Group Title',
                            'value' => [
                                'payment_method' => [
                                    'value' => 'payment_method',
                                    'label' => 'Payment Title',
                                ],
                            ],
                        ],
                    ],
                ],
        ];
    }
}
