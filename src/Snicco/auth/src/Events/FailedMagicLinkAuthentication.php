<?php

namespace Snicco\Auth\Events;

use Snicco\Component\HttpRouting\Http\Psr7\Request;

class FailedMagicLinkAuthentication extends BannableEvent
{
    
    private ?int $user_id;
    
    public function __construct(Request $request, ?int $user_id = null)
    {
        $this->request = $request;
        $this->user_id = $user_id;
    }
    
    public function fail2BanMessage() :string
    {
        return "Failed authentication with magic link for user [$this->user_id]";
    }
    
    public function userId() :?int
    {
        return $this->user_id;
    }
    
}