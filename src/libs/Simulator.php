<?php

namespace Prinx\Simulator\Libs;

use Prinx\Notify\Log;
use Prinx\Utils\HTTP;

class Simulator
{
    protected $request = null;
    protected $endpoint = '';
    protected $payload = [];

    /**
     * Capture the request coming from the simulator interface, send the
     * request to the application and return the response.
     *
     * @return Response
     */
    public function simulate()
    {
        $this->captureIncomingRequest();
        $response = $this->callUssd();
        $this->log($response);

        return $response;
    }

    /**
     * Capture request from the simulator interface.
     *
     * @return void
     */
    public function captureIncomingRequest()
    {
        try {
            $this->request = new Request;
        } catch (\Throwable $th) {
            exit(json_encode([
                'error'   => $th->getMessage(),
                'SUCCESS' => false,
            ]));
        }

        $this->payload = $this->request->data();
        $this->endpoint = urldecode($this->payload['endpoint']);
        unset($this->payload['endpoint']);
    }

    /**
     * Send the HTTP request to the USSD application and return the response.
     *
     * @return Response
     */
    public function callUssd()
    {
        return new Response(HTTP::post($this->payload, $this->endpoint));
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
    }

    public function setEndpoint(string $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Log the response if response cannot be parse to JSON.
     *
     * @param Response $response
     *
     * @return void
     */
    public function log(Response $response)
    {
        if (!json_decode($response->data()['data'])) {
            $dir = realpath(__DIR__.'/../');
            $file = $dir.'/storage/logs/simulator.log';
            $cache = $dir.'/storage/cache/request-count.cache';
            $logger = new Log($file, $cache);
            $logger->warning($response->data());
        }
    }
}
