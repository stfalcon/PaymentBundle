<?php

namespace Stfalcon\Bundle\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\MaxLength;
use Symfony\Component\HttpFoundation\Response;

use Stfalcon\Bundle\PaymentBundle\Entity\Payment;
use Stfalcon\Bundle\PaymentBundle\Entity\InterkassaPayment;

class InterkassaPaymentController extends Controller
{

    /**
     * Создать новый платеж
     *
     * @Template()
     */
    public function newAction()
    {
        // create a collection of constraints
        $collectionConstraint = new Collection(array(
            'amount' => new Type(array('type' => 'numeric')),
            'description' => new Type(array('type' => 'string')),
            'description' => new MaxLength(array('limit' => 255)),
        ));

        $form = $this->createFormBuilder(null, array(
            'validation_constraint' => $collectionConstraint,
        ))
            ->add('amount', 'text')
            ->add('description', 'textarea')
            ->getForm();

        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());

            if ($form->isValid()) {
                $payment = new InterkassaPayment($form->get('amount')->getData(), $form->get('description')->getData());
                $this->getDoctrine()->getEntityManager()->persist($payment);
                $this->getDoctrine()->getEntityManager()->flush();

                return $this->forward('StfalconPaymentBundle:InterkassaPayment:pay', array('payment' => $payment));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * Форма отправки данных платежа к шлюзу
     *
     * @Template()
     */
    public function payAction(Payment $payment)
    {
        $settings = $this->container->getParameter('stfalcon_payment');

        return array(
            'payment' => $payment,
            'settings' => $settings['interkassa']
        );
    }

    /**
     * Принимает ответ от шлюза
     *
     * @Template()
     * @return array
     */
    public function statusAction()
    {
        $settings = $this->container->getParameter('stfalcon_payment');
        $statusData = $this->getRequest()->request->all();

        $payment = $this->getDoctrine()->getEntityManager()
                 ->getRepository('StfalconPaymentBundle:InterkassaPayment')
                 ->findOneBy(array('id' => $statusData['ik_payment_id']));

        if (!$payment) {
            $this->createNotFoundException('Payment #' . $statusData['ik_payment_id'] . ' not found in the database.');
        }

        $payment->markAsPaid($statusData, $settings['interkassa']['secret_key']);

        $this->getDoctrine()->getEntityManager()->persist($payment);
        $this->getDoctrine()->getEntityManager()->flush();

        return new Response('ok');
    }

}
