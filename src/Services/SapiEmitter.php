<?php

namespace InfinyHost\InfinyMini\Services;

use InfinyHost\InfinyMini\App;
use Psr\Http\Message\ResponseInterface;
use \Laminas\HttpHandlerRunner\Emitter\SapiEmitter as LaminasSapiEmitter;

class SapiEmitter extends LaminasSapiEmitter
{
    /**
     * Emit the message body.
     */
    private function emitBody(ResponseInterface $response): void
    {
        $app = App::getInstance();
        $cpanel = $app->services()->get('cpanel');
        $cpanel->header('');
        echo $response->getBody();
        $cpanel->footer();
        $cpanel->end();
    }
}
