<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\Tests\Handler;

use ConnectHolland\SecureJWT\Entity\InvalidToken;
use ConnectHolland\SecureJWT\Handler\LogoutHandler;
use ConnectHolland\SecureJWT\Message\Logout;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LogoutHandlerTest extends TestCase
{
    private LogoutHandler $handler;

    private TokenStorageInterface $tokenStorage;

    private ManagerRegistry $doctrine;

    public function setUp(): void
    {
        $this->doctrine     = $this->createMock(ManagerRegistry::class);
        $this->tokenStorage = new TokenStorage();
        $this->handler      = new LogoutHandler($this->tokenStorage, $this->doctrine);
    }

    public function testOnlyHandleJWTTokens(): void
    {
        $this->tokenStorage->setToken(new AnonymousToken('secret', $this->createMock(UserInterface::class)));

        $this->doctrine
            ->expects($this->never())
            ->method('getManagerForClass');

        $this->handler->__invoke(new Logout());
    }

    public function testPersistsInvalidToken(): void
    {
        $this->tokenStorage->setToken(new JWTUserToken([], null, 'unit-test-token'));
        $manager     = $this->createMock(EntityManager::class);
        $invalidated = null;

        $manager
            ->expects($this->once())
            ->method('flush');

        $manager
            ->expects($this->once())
            ->method('persist')
            ->willReturnCallback(static function (InvalidToken $invalidToken) use (&$invalidated): void {
                $invalidated = $invalidToken->getToken();
            });

        $this->doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($manager);

        $this->handler->__invoke(new Logout());

        $this->assertNotNull($invalidated);
        $this->assertSame('unit-test-token', $invalidated);
    }

    public function testExceptionOnDoctrineIssue(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->tokenStorage->setToken(new JWTUserToken([], null, 'unit-test-token'));

        $this->doctrine
            ->expects($this->once())
            ->method('getManagerForClass');

        $this->handler->__invoke(new Logout());
    }
}