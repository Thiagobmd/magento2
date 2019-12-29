<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\Request\AuthenticationDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Request\AuthenticationDataBuilder
 */
class AuthenticationDataBuilderTest extends TestCase
{
    /**
     * @var AuthenticationDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentDOMock;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->configMock = $this->createMock(Config::class);
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        /** @var MockObject|SubjectReader subjectReaderMock */
        $this->subjectReaderMock = $this->createMock(SubjectReader::class);

        $this->builder = $objectManagerHelper->getObject(
            AuthenticationDataBuilder::class,
            [
                'subjectReader' => $this->subjectReaderMock,
                'config' => $this->configMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testBuild()
    {
        $this->configMock->method('getLoginId')
            ->willReturn('myloginid');
        $this->configMock->method('getTransactionKey')
            ->willReturn('mytransactionkey');

        $expected = [
            'merchantAuthentication' => [
                'name' => 'myloginid',
                'transactionKey' => 'mytransactionkey',
            ],
        ];

        $buildSubject = [];

        $this->subjectReaderMock->method('readStoreId')
            ->with($buildSubject)
            ->willReturn(123);

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
