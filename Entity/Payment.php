<?php

namespace Stfalcon\Bundle\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stfalcon\Bundle\PaymentBundle\Entity\Payment
 *
 * @ORM\MappedSuperclass
 */
class Payment
{

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    private $id;

    /**
     * Payment amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     * @var float
     */
    private $amount;

    /**
     * Payment description
     *
     * @ORM\Column(name="description")
     * @var string
     */
    private $description;

    /**
     * Payment status
     *
     * @ORM\Column(name="status")
     * @var string
     */
    private $status;

    /**
     * Initialize payment
     *
     * @param float $amount
     * @return float
     */
    public function __construct($amount = 0, $description = '')
    {
        $this->setAmount($amount);
        $this->setDescription($description);
        $this->setStatus(self::STATUS_PENDING);
    }

    public function setId($id)
    {
        if (!is_int($id)) {
            throw new \InvalidArgumentException('Payment id should be a integer');
        }

        if ($this->id) {
            throw new \Exception('Id this payment has already been set.');
        }

        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get payment amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get payment description
     *
     * @return string
     */
    public function getDescription()
    {
        return (string) $this->description;
    }

    /**
     * Get payment status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Is this payment is pending
     *
     * @return boolean
     */
    public function isPending()
    {
        return ($this->getStatus() == self::STATUS_PENDING);
    }

    /**
     * Set payment status
     *
     * @param string $status
     */
    protected function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Set payment amount
     *
     * @param float $amount
     * @return void
     */
    private function setAmount($amount)
    {
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('Payment amount should be a number');
        }
        $this->amount = $amount;
    }

    /**
     * Set payment description
     *
     * @param string $description
     */
    private function setDescription($description)
    {
        if (!is_string($description)) {
            throw new \InvalidArgumentException('Payment sedcription should be a string');
        }
        $this->description = $description;
    }
}
