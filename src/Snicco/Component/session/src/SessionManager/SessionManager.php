<?php

declare(strict_types=1);

namespace Snicco\Component\Session\SessionManager;

use Snicco\Component\Session\Exception\CouldNotDestroySessions;
use Snicco\Component\Session\Exception\CouldNotReadSessionContent;
use Snicco\Component\Session\Exception\CouldNotWriteSessionContent;
use Snicco\Component\Session\ImmutableSession;
use Snicco\Component\Session\Session;
use Snicco\Component\Session\ValueObject\CookiePool;
use Snicco\Component\Session\ValueObject\SessionCookie;

interface SessionManager
{
    /**
     * @throws CouldNotReadSessionContent
     */
    public function start(CookiePool $cookie_pool): Session;

    /**
     * @return SessionCookie A value object that provides valid parameters to use in {@see setcookie()}
     */
    public function toCookie(ImmutableSession $session): SessionCookie;

    /**
     * @throws CouldNotWriteSessionContent If the session can't be saved.
     */
    public function save(Session $session): void;

    /**
     * @throws CouldNotDestroySessions
     */
    public function gc(): void;
}
