<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class RefreshToken
{
    /**
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="Account")
     */
    public $account;

    /**
     * @ORM\Column(type="string")
     */
    public $token;

    /**
     * @ORM\Column(type="datetime")
     */
    public $expires;

    /**
     * @ORM\Column(type="datetime")
     */
    public $created;

    /**
     * @ORM\Column(type="string")
     */
    public $createdByIp;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $revoked;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $revokedByIp;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $replacedByToken;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $reasonRevoked;

    public function isExpired(): bool
    {
        return new \DateTime() >= $this->expires;
    }

    public function isRevoked(): bool
    {
        return $this->revoked !== null;
    }

    public function isActive(): bool
    {
        return !$this->isRevoked() && !$this->isExpired();
    }
}
