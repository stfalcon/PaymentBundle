<?php

namespace Stfalcon\Bundle\PaymentBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

use Stfalcon\Bundle\PaymentBundle\Tests\Entity\InterkassaPaymentEntityTest;
use Stfalcon\Bundle\PaymentBundle\Tests\Entity\PaymentEntityTest;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;
use Stfalcon\Bundle\PaymentBundle\Entity\InterkassaPayment;

class PaymentControllerTest extends WebTestCase
{

    const ID_FORM_PAYMENT_NEW = 'interkassa_payment_new';
    const ID_FORM_INTERKASSA = 'interkassa';

    public function testInterkassaPaymentStatusAction()
    {
        $client = self::createClient();
        $container = $client->getKernel()->getContainer();

        // создаем тестовый платеж и сохраняем его в бд
        $payment = new InterkassaPayment(PaymentEntityTest::PAYMENT_AMOUNT, PaymentEntityTest::PAYMENT_DESCRIPTION);
        $container->get('doctrine')->getEntityManager()->persist($payment);
        $container->get('doctrine')->getEntityManager()->flush();

        // генерируем валидные данные ответа шлюза
        $statusData = InterkassaPaymentEntityTest::$statusData;
        $statusData['ik_payment_id'] = $payment->getId();
        $statusData['ik_payment_amount'] = $payment->getAmount();
        $statusData['ik_payment_desc'] = $payment->getDescription();
        $settings = $container->getParameter('stfalcon_payment');
        $statusData['ik_sign_hash'] = InterkassaPayment::getSignHash($statusData, $settings['interkassa']['secret_key']);

        $client->request('POST', $container->get('router')->generate('interkassa_payment_status'), $statusData);

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testCheckNewPaymentPageIsAvailable()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $client->getKernel()->getContainer()->get('router')->generate('interkassa_payment_new'));
        $this->assertTrue($client->getResponse()->isSuccessful(),
                'Произошла ошибка при открытии страницы создания платежа (роутер "interkassa_payment_new")');

        $this->assertEquals(1, $crawler->filter('form[id="' . self::ID_FORM_PAYMENT_NEW . '"]')->count(),
                'Что-то не так с формой создания платежа (вероятно форма не найдена или она не одна)');
    }

    public function testSubmitInvalidDataOnNewPaymentPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $client->getKernel()->getContainer()->get('router')->generate('interkassa_payment_new'));

        // заполняем и сабмитим форму
        $paymentForm = $crawler->filter('form[id="interkassa_payment_new"]')->form();
        $paymentForm['form[amount]'] = 'string';
        $paymentForm['form[description]'] = '';
        $paymentFormCrawler = $client->submit($paymentForm)->filter('form[id="' . self::ID_FORM_PAYMENT_NEW . '"]');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $paymentFormCrawler->count(),
                'Что-то не так с формой создания платежа (должна отображаться форма и ошибки валидации)');
        // @todo: refact тест зависит от текста ошибки
        $this->assertEquals(1, $paymentFormCrawler->filter('li:contains("This value should be of type numeric")')->count());
    }

    public function testCheckInterkassaPayPageIsAvailable()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $client->getKernel()->getContainer()->get('router')->generate('interkassa_payment_new'));

        $paymentForm = $crawler->filter('form[id="interkassa_payment_new"]')->form();
        $paymentForm['form[amount]'] = PaymentEntityTest::PAYMENT_AMOUNT;
        $paymentForm['form[description]'] = PaymentEntityTest::PAYMENT_DESCRIPTION;
        $interkassaPageCrawler = $client->submit($paymentForm);

        $this->assertTrue($client->getResponse()->isSuccessful(),
                'Произошла ошибка при открытии страницы подтверждения платежа (роутер "/pay/interkassa")');
        $this->assertFalse($client->getResponse()->isRedirect());

        return $interkassaPageCrawler;
    }

    /**
     * @depends testCheckInterkassaPayPageIsAvailable
     * @param Symfony\Component\DomCrawler\Crawler $interkassaFormCrawler
     */
    public function testCheckInterkassaPayFormIsAvailable($interkassaPageCrawler)
    {
        // проверяем или на странице отображается назначение платежа и сумма
        $this->assertEquals(1, $interkassaPageCrawler->filter('p:contains("' . PaymentEntityTest::PAYMENT_AMOUNT . '")')->count(),
                'На странице подтверждения оплаты не отображается сумма платежа');
        $this->assertEquals(1, $interkassaPageCrawler->filter('p:contains("' . PaymentEntityTest::PAYMENT_DESCRIPTION . '")')->count(),
                'На странице подтверждения оплаты не отображается комментарий к платежу');

        // проверяем наличие формы запроса платежа для Интеркассы
        $interkassaFormCrawler = $interkassaPageCrawler->filter('form[id="' . self::ID_FORM_INTERKASSA . '"]');
        $this->assertEquals(1, $interkassaFormCrawler->count(),
                'Что-то не так с формой запроса платежа для Интеркассы (вероятно форма не найдена или она не одна)');

        return $interkassaFormCrawler;
    }

    /**
     * @depends testCheckInterkassaPayFormIsAvailable
     * @param Symfony\Component\DomCrawler\Crawler $interkassaFormCrawler
     */
    public function testInterkassaPayFormCheckFieldsExists(Crawler $interkassaFormCrawler)
    {
        // проверяем наличие обязательных полей платежа
        $this->assertEquals(1, $interkassaFormCrawler->filter('input[name="ik_shop_id"]')->count());
        $this->assertEquals(1, $interkassaFormCrawler->filter('input[name="ik_payment_amount"]')->count());
        $this->assertEquals(1, $interkassaFormCrawler->filter('input[name="ik_payment_id"]')->count());
        $this->assertEquals(1, $interkassaFormCrawler->filter('input[name="ik_payment_desc"]')->count());
        $this->assertEquals(1, $interkassaFormCrawler->filter('input[name="ik_paysystem_alias"]')->count());
        $this->assertEquals(1, $interkassaFormCrawler->filter('input[type="submit"]')->count());
    }

    /**
     * @depends testCheckInterkassaPayFormIsAvailable
     * @param Symfony\Component\DomCrawler\Crawler $interkassaFormCrawler
     */
    public function testInterkassaPayFormCheckFieldsValues(Crawler $interkassaFormCrawler)
    {
        // проверяем ссылку в action формы
        $interkassaForm = $interkassaFormCrawler->form();
        $this->assertEquals('http://www.interkassa.com/lib/payment.php', $interkassaForm->getUri());

        // проверяем значения полей формы
        $interkassaFormData = $interkassaForm->getValues();
//        $settings = self::$kernel->getContainer()->getParameter('stfalcon_payment');
//        $this->assertEquals($settings['interkassa']['shop_id'], $interkassaFormData['ik_shop_id'],
//                'Значение ik_shop_id (id магазина) отличается от значения заданного в настройках сайта (в форме запроса платежа для Интеркассы)');
        $this->assertEquals(PaymentEntityTest::PAYMENT_AMOUNT, $interkassaFormData['ik_payment_amount']);
        $this->assertInternalType('numeric', $interkassaFormData['ik_payment_id']);
        $this->assertEquals(PaymentEntityTest::PAYMENT_DESCRIPTION, $interkassaFormData['ik_payment_desc']);
        $this->assertTrue(isset($interkassaFormData['ik_paysystem_alias']));
    }

}
