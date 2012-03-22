<?php

namespace Stfalcon\Bundle\PaymentBundle\Tests\Entity;

use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

class PaymentEntityTest extends \PHPUnit_Framework_TestCase
{

    const PAYMENT_AMOUNT = 525.25;
    const PAYMENT_DESCRIPTION = 'Zend Framework Day';

    public function testIdIsInitiallyNull()
    {
        $payment = new Payment();
        $this->assertNull($payment->getId());
    }

    public function testAmountIsInitiallyZero()
    {
        $payment = new Payment();
        $this->assertEquals(0, $payment->getAmount());
    }

    public function testSetAmountInPaymentConstructor()
    {
        $amount = 100.54;
        $payment = new Payment($amount);
        $this->assertSame($amount, $payment->getAmount());
    }

    public function testSetFloatDataToAmount()
    {
        $amount = 547987.342;
        $payment = new Payment($amount);
        $this->assertSame($amount, $payment->getAmount());
    }

    public function testSetIntDataToAmount()
    {
        $amount = 547987;
        $payment = new Payment($amount);
        $this->assertFalse($payment->getAmount() === $amount);
        $this->assertSame((float) $amount, $payment->getAmount());
    }

    public function testSetNumericStringToAmount()
    {
        $amount = '547987.43';
        $payment = new Payment($amount);
        $this->assertFalse($payment->getAmount() === $amount);
        $this->assertSame($amount, $payment->getAmount());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCatchExceptionIfSetInvalidDataToAmount()
    {
        $payment = new Payment(new \DateTime("now"));
    }

    public function testDescriptionIsInitiallyEmptyString()
    {
        $payment = new Payment();
        $this->assertSame('', $payment->getDescription());
        $this->assertFalse(is_null($payment->getDescription()));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCatchExceptionIfSetInvalidDataToDescription()
    {
        $payment = new Payment(0, new \DateTime("now"));
    }

    public function testStatusIsInitiallyAsPending()
    {
        $payment = new Payment();
        $this->assertEquals($payment->getStatus(), Payment::STATUS_PENDING);
        $this->assertTrue($payment->isPending());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetNewId()
    {
        $payment = new Payment();
        $payment->setId('1');
        $this->assertNull($payment->getId());
    }

    /**
     * @expectedException Exception
     */
    public function testTryChangeExistsId()
    {
        $payment = new Payment();
        $payment->setId(1);
        $payment->setId(2);
    }

}
