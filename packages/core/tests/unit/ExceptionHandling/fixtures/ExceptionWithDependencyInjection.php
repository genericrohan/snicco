<?php

declare(strict_types=1);

namespace Tests\Core\unit\ExceptionHandling\fixtures;

use Exception;
use Snicco\Http\Psr7\Request;
use Snicco\Http\BaseResponseFactory;

class ExceptionWithDependencyInjection extends Exception
{
    
    public function render(Request $request, BaseResponseFactory $response_factory)
    {
        return $response_factory
            ->html($request->getAttribute('foo'))
            ->withStatus(403);
    }
    
}