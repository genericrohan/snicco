<?php

declare(strict_types=1);

namespace Snicco\Core\Middleware;

use Snicco\Support\Str;
use Snicco\Core\Support\Url;
use Snicco\Core\Http\Delegate;
use Snicco\Core\Http\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Snicco\Core\Contracts\AbstractMiddleware;

class TrailingSlash extends AbstractMiddleware
{
    
    /**
     * @var bool
     */
    private $trailing_slash;
    
    public function __construct(bool $trailing_slash = true)
    {
        $this->trailing_slash = $trailing_slash;
    }
    
    public function handle(Request $request, Delegate $next) :ResponseInterface
    {
        if ($request->isWpAdmin() || $request->isWpAjax()) {
            return $next($request);
        }
        
        $path = $request->path();
        
        $accept_request = $this->trailing_slash
            ? Str::endsWith($path, '/')
            : Str::doesNotEndWith($path, '/');
        
        if ($accept_request || $path === '/') {
            return $next($request);
        }
        
        $redirect_to = $this->trailing_slash
            ? Url::addTrailing($path)
            : Url::removeTrailing($path);
        
        return $this->redirect()->to($redirect_to, 301);
    }
    
}