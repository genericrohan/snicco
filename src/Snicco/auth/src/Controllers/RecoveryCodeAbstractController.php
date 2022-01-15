<?php

declare(strict_types=1);

namespace Snicco\Auth\Controllers;

use Snicco\Auth\Traits\ResolvesUser;
use Snicco\Component\Core\Shared\Encryptor;
use Snicco\Component\HttpRouting\Http\Psr7\Request;
use Snicco\Auth\Traits\InteractsWithTwoFactorCodes;
use Snicco\Component\HttpRouting\Http\Psr7\Response;
use Snicco\Auth\Traits\InteractsWithTwoFactorSecrets;
use Snicco\Component\HttpRouting\Http\AbstractController;

class RecoveryCodeAbstractController extends AbstractController
{
    
    use InteractsWithTwoFactorCodes;
    use InteractsWithTwoFactorSecrets;
    use ResolvesUser;
    
    private Encryptor $encryptor;
    
    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }
    
    public function index(Request $request) :Response
    {
        return $this->response_factory->json($this->recoveryCodes($request->userId()));
    }
    
    public function update(Request $request)
    {
        $codes = $this->generateNewRecoveryCodes();
        $this->saveCodes($request->userId(), $codes);
        
        return $this->response_factory->json($codes);
    }
    
}