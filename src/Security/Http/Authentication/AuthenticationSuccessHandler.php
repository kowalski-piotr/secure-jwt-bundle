<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * @deprecated Use lexik default instead
 **/
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private AuthenticationSuccessHandlerInterface $successHandler;

    private JWTEncoderInterface $jwtEncoder;

    private array $responsePayload = [];

    private string $sameSite;

    public function __construct(AuthenticationSuccessHandlerInterface $successHandler, JWTEncoderInterface $jwtEncoder, string $sameSite)
    {
        $this->successHandler = $successHandler;
        $this->jwtEncoder     = $jwtEncoder;
        $this->sameSite       = $sameSite;
    }

    /**
     * Set the JWT token as a Cookie.
     */
    public function handleAuthenticationSuccess(UserInterface $user, $jwt = null): JsonResponse
    {
        $jsonWithToken         = $this->successHandler->handleAuthenticationSuccess($user, $jwt);
        $data                  = json_decode($jsonWithToken->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $decoded               = $this->jwtEncoder->decode($data['token']);
        $this->responsePayload = array_merge($this->responsePayload, $decoded);
        $response              = new JsonResponse(['result' => 'ok', 'payload' => $this->responsePayload]);
        $response->headers->setCookie(new Cookie('BEARER', $data['token'], $decoded['exp'], '/', null, true, true, false, $this->sameSite));

        return  $response;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        return $this->handleAuthenticationSuccess($token->getUser());
    }

    /**
     * Add fields to the response payload.
     */
    public function addResponsePayload(string $key, $value): void
    {
        $this->responsePayload[$key] = $value;
    }
}
