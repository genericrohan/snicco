<?php

declare(strict_types=1);

namespace Tests\Core\unit\Middleware;

use Snicco\Routing\UrlGenerator;
use Tests\Core\MiddlewareTestCase;
use Snicco\Http\BaseResponseFactory;
use Snicco\Middleware\TrailingSlash;
use Snicco\Http\StatelessRedirector;
use Snicco\Testing\TestDoubles\TestMagicLink;

class TrailingSlashTest extends MiddlewareTestCase
{
    
    public function testRedirectNoSlashToTrailingSlash()
    {
        $this->response_factory = new BaseResponseFactory(
            $this->psrResponseFactory(),
            $this->psrStreamFactory(),
            new StatelessRedirector(
                $url = new UrlGenerator($this->routeUrlGenerator(), new TestMagicLink(), true),
                $this->psrResponseFactory()
            )
        );
        
        $request = $this->frontendRequest('GET', 'https://foo.com/bar');
        
        $url->setRequestResolver(function () use ($request) {
            return $request;
        });
        
        $response = $this->runMiddleware(new TrailingSlash(true), $request);
        
        $response->assertNextMiddlewareNotCalled();
        $response->assertRedirect();
        $response->assertStatus(301);
        
        $response->assertRedirectPath('/bar/');
    }
    
    /** @test */
    public function testRedirectSlashToNoSlash()
    {
        $request = $this->frontendRequest('GET', 'https://foo.com/bar/');
        
        $response = $this->runMiddleware(new TrailingSlash(false), $request);
        
        $response->assertNextMiddlewareNotCalled();
        $response->assertRedirect('/bar', 301);
    }
    
    public function testNoRedirectIfSlashesAreCorrect()
    {
        $request = $this->frontendRequest('GET', 'https://foo.com/bar');
        
        $response = $this->runMiddleware(new TrailingSlash(false), $request);
        
        $response->assertNextMiddlewareCalled();
        $response->assertOk();
    }
    
}