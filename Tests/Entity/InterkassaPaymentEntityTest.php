<?php

namespace Stfalcon\Bundle\PaymentBundle\Tests\Entity;

use Stfalcon\Bundle\PaymentBundle\Entity\InterkassaPayment;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

class InterkassaPaymentEntityTest extends \PHPUnit_Framework_TestCase
{
    static $statusData = array(
            'ik_shop_id' => '64C18529-4B94-0B5D-7405-F2752F2B716C',
            'ik_payment_amount' => '1.00',
            'ik_payment_id' => '1234',
            'ik_payment_desc' => ' iPod 80Gb черный ',
            'ik_paysystem_alias' => 'webmoneyz',
            'ik_baggage_fields' => 'tel: 80441234567',
            'ik_payment_timestamp' => '1196087212',
            'ik_payment_state' => 'success',
            'ik_trans_id' => 'IK_68',
            'ik_currency_exch' => '1',
            'ik_fees_payer' => '1',
            'ik_sign_hash' => '51ee92491491be6c1d30ee8605f77be0',
        );

    static $secretKey = 'RhAAaJ2AwydMbKzN';

    public function testGetHashIfStatusDataIsValid()
    {
        $hash = InterkassaPayment::getSignHash(self::$statusData, self::$secretKey);
        $this->assertEquals(self::$statusData['ik_sign_hash'], $hash);
    }

    public function testGetHashIfPaymentIdIsInvalid()
    {
        $statusData = self::$statusData;
        $statusData['ik_payment_id'] = ++$statusData['ik_payment_id'];
        $this->assertNotEquals(self::$statusData['ik_sign_hash'], InterkassaPayment::getSignHash($statusData, self::$secretKey));
    }

    public function testGetHashIfShopIdIsInvalid()
    {
        $statusData = self::$statusData;
        $statusData['ik_shop_id'] = str_shuffle($statusData['ik_shop_id']);
        $this->assertNotEquals(self::$statusData['ik_sign_hash'], InterkassaPayment::getSignHash($statusData, self::$secretKey));
    }

    public function testGetHashIfPaymentAmountIsInvalid()
    {
        $statusData = self::$statusData;
        $statusData['ik_payment_amount'] = ++$statusData['ik_payment_amount'];
        $this->assertNotEquals(self::$statusData['ik_sign_hash'], InterkassaPayment::getSignHash($statusData, self::$secretKey));
    }

    public function testGetHashIfPaysystemAliasIsInvalid()
    {
        $statusData = self::$statusData;
        $statusData['ik_paysystem_alias'] = str_shuffle($statusData['ik_paysystem_alias']);
        $this->assertNotEquals(self::$statusData['ik_sign_hash'], InterkassaPayment::getSignHash($statusData, self::$secretKey));
    }

    public function testGetHashIfBaggageFeldsIsInvalid()
    {
        $statusData = self::$statusData;
        $statusData['ik_baggage_fields'] = str_shuffle($statusData['ik_baggage_fields']);
        $this->assertNotEquals(self::$statusData['ik_sign_hash'], InterkassaPayment::getSignHash($statusData, self::$secretKey));
    }

    public function testGetHashIfPaymentStateIsInvalid()
    {
        $statusData = self::$statusData;
        $statusData['ik_payment_state'] = str_shuffle($statusData['ik_payment_state']);
        $this->assertNotEquals(self::$statusData['ik_sign_hash'], InterkassaPayment::getSignHash($statusData, self::$secretKey));
    }

    public function testGetHashIfTransIdIsInvalid()
    {
        $statusData = self::$statusData;
        $statusData['ik_trans_id'] = str_shuffle($statusData['ik_trans_id']);
        $this->assertNotEquals(self::$statusData['ik_sign_hash'], InterkassaPayment::getSignHash($statusData, self::$secretKey));
    }

    public function testGetHashIfCurrencyExchIsInvalid()
    {
        $statusData = self::$statusData;
        $statusData['ik_currency_exch'] = ++$statusData['ik_currency_exch'];
        $this->assertNotEquals(self::$statusData['ik_sign_hash'], InterkassaPayment::getSignHash($statusData, self::$secretKey));
    }

    public function testGetHashIfFeesPayerIsInvalid()
    {
        $statusData = self::$statusData;
        $statusData['ik_fees_payer'] = ++$statusData['ik_fees_payer'];
        $this->assertNotEquals(self::$statusData['ik_sign_hash'], InterkassaPayment::getSignHash($statusData, self::$secretKey));
    }

    public function testGetHashIfSecretKeyIsInvalid()
    {
        $secretKey = str_shuffle(self::$secretKey);
        $this->assertNotEquals(self::$statusData['ik_sign_hash'], InterkassaPayment::getSignHash(self::$statusData, $secretKey));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetHashThrowExeptionIfStatusDataIsNotSet()
    {
        InterkassaPayment::getSignHash(array(), self::$secretKey);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetHashThrowExeptionIfSecretKeyIsNotSet()
    {
        InterkassaPayment::getSignHash(self::$statusData, '');
    }

    public function testPaymentMarkAsPaid()
    {
        $payment = new InterkassaPayment(self::$statusData['ik_payment_amount'], self::$statusData['ik_payment_desc']);
        $payment->setId((int) self::$statusData['ik_payment_id']);

        $payment->markAsPaid(self::$statusData, self::$secretKey);

        $this->assertEquals(Payment::STATUS_PAID, $payment->getStatus());
    }

}