<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\Event;

use ConnectHolland\SecureJWT\Entity\TwoFactorUserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore Ignore for coverage because this is just a trivial data container
 */
class SetupTwoFactorAuthenticationEvent
{
    public const NAME = 'blauwhoed.setup_two_factor_authentication';

    private TwoFactorUserInterface $user;

    private Response $response;

    public function __construct(TwoFactorUserInterface $user)
    {
        $this->user     = $user;
        $this->response = new JsonResponse(['result' => 'failed', 'message' => 'failed to create QR code to set up two factor authentication'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function getUser(): TwoFactorUserInterface
    {
        return $this->user;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
