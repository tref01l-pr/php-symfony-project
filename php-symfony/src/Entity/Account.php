<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Account
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $passwordHash;

    /**
     * @ORM\Column(type="boolean")
     */
    public $acceptTerms;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $role;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $verificationToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $verified;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $resetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $resetTokenExpires;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $passwordReset;

    /**
     * @ORM\Column(type="datetime")
     */
    public $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $updated;

    /**
     * @ORM\OneToMany(targetEntity="RefreshToken", mappedBy="account")
     */
    public $refreshTokens;

    public function __construct()
    {
        $this->refreshTokens = new ArrayCollection();
    }

    public function ownsToken(string $token): bool
    {
        foreach ($this->refreshTokens as $refreshToken) {
            if ($refreshToken->token === $token) {
                return true;
            }
        }

        return false;
    }
}
