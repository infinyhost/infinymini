<?php

namespace InfinyHost\InfinyMini\Services;

use InfinyHost\InfinyMini\App;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitterTrait;
use Psr\Http\Message\ResponseInterface;

class SapiEmitter implements EmitterInterface
{
    use SapiEmitterTrait;

    /**
     * Emits a response for a PHP SAPI environment.
     *
     * Emits the status line and headers via the header() function, and the
     * body content via the output buffer.
     */
    public function emit(ResponseInterface $response): bool
    {
        $this->assertNoPreviousOutput();

        $this->emitHeaders($response);
        $this->emitStatusLine($response);
        $this->emitBody($response);

        return true;
    }
    /**
     * Emit the message body.
     */
    private function emitBody(ResponseInterface $response): void
    {
        $app = App::getInstance();
        $cpanel = $app->services()->get('cpanel');
        if (isset($response->cpanelHeader)) {
            echo $cpanel->header($response->cpanelHeader);
        } else {
            echo $cpanel->header('');
        }
        echo $response->getBody();
        echo $cpanel->footer();
        $cpanel->end();
    }
}
