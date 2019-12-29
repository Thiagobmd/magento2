<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Model\PassthroughDataObject;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Adds the meta transaction information to the request
 */
class AuthorizeDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    private static $requestAuthOnly = 'authOnlyTransaction';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var PassthroughDataObject
     */
    private $passthroughData;

    /**
     * @param SubjectReader $subjectReader
     * @param PassthroughDataObject $passthroughData
     */
    public function __construct(
        SubjectReader $subjectReader,
        PassthroughDataObject $passthroughData
    ) {
        $this->subjectReader = $subjectReader;
        $this->passthroughData = $passthroughData;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $data = [];

        if ($payment instanceof Payment) {
            $data = [
                'transactionRequest' => [
                    'transactionType' => self::$requestAuthOnly,
                ],
            ];

            $this->passthroughData->setData(
                'transactionType',
                $data['transactionRequest']['transactionType']
            );
        }

        return $data;
    }
}
