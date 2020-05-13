<?php

declare(strict_types=1);

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TwoFactorJWTToken extends UsernamePasswordToken
{
    private string $twoFactorChallenge;

    /**
     * TwoFactorToken constructor.
     */
    public function __construct(string $user, string $credentials, string $twoFactorChallenge, string $providerKey, array $roles = [])
    {
        parent::__construct($user, $credentials, $providerKey, $roles);

        $this->twoFactorChallenge = $twoFactorChallenge;
    }

    public function getTwoFactorChallenge(): string
    {
        return $this->twoFactorChallenge;
    }
}
