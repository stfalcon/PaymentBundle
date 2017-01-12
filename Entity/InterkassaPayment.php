<?php

namespace Stfalcon\Bundle\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="payments")
 * @ORM\Entity
 */
class InterkassaPayment extends Payment
{
    /**
     * Get hash string for payment
     *
     * @param array $statusData
     * @param string $secretKey
     * @return string
     */
    public static function getSignHash(array $statusData, $secretKey)
    {
        if (!array_key_exists('ik_shop_id', $statusData) ||
            !array_key_exists('ik_payment_amount', $statusData) ||
            !array_key_exists('ik_payment_id', $statusData) ||
            !array_key_exists('ik_paysystem_alias', $statusData) ||
            !array_key_exists('ik_baggage_fields', $statusData) ||
            !array_key_exists('ik_payment_state', $statusData) ||
            !array_key_exists('ik_trans_id', $statusData) ||
            !array_key_exists('ik_currency_exch', $statusData) ||
            !array_key_exists('ik_fees_payer', $statusData)
        ) {
            throw new \InvalidArgumentException('Missing one or more required parameters to generate the hash.');
        }

        if (!$secretKey) {
            throw new \InvalidArgumentException('There is no secret key.');
        }

        return md5(
            $statusData['ik_shop_id'] .':'.
            sprintf("%.2f", $statusData['ik_payment_amount']) .':'.
            $statusData['ik_payment_id'] .':'.
            $statusData['ik_paysystem_alias'] .':'.
            $statusData['ik_baggage_fields'] .':'.
            $statusData['ik_payment_state'] .':'.
            $statusData['ik_trans_id'] .':'.
            $statusData['ik_currency_exch'] .':'.
            $statusData['ik_fees_payer'] .':'.
            $secretKey
        );
    }

    public function markAsPaid(array $statusData, $secretKey)
    {
        $data = $statusData;
        $data['ik_payment_amount'] = $this->getAmount();
        $data['ik_payment_id'] = $this->getId();

        if ($statusData['ik_sign_hash'] != InterkassaPayment::getSignHash($data, $secretKey)) {
            throw new \InvalidArgumentException('Verifying the signature information about the payment failed.');
        }

        $this->setStatus(parent::STATUS_PAID);
    }

}
