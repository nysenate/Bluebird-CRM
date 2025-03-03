<?php

namespace DMore\ChromeDriver;

use Behat\Mink\Exception\DriverException;
use WebSocket\Client;
use WebSocket\ConnectionException;

abstract class DevToolsConnection
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $command_id = 1;

    /**
     * @var string
     */
    private $url;

    /**
     * @var int|null
     */
    private $socket_timeout;

    /**
     * @param $url
     * @param null $socket_timeout
     */
    public function __construct($url, $socket_timeout = null)
    {
        $this->url = $url;
        $this->socket_timeout = $socket_timeout;
    }

    /**
     * Check DevTools connection.
     *
     * @return bool
     * @deprecated since 2.8.0 this method has always returned false and is not considered useful
     */
    public function canDevToolsConnectionBeEstablished()
    {
        // Since 2.8.0 this has always returned false, as the `url` injected into `ChromePage` is the *websocket url*
        // and not the base url of the JSON API - so adding `/json/version` to the URL has always resulted in a 404.
        //
        // The method was only added as an attempt to detect whether Chrome itself was healthy if the driver received
        // a timeout when reading from the websocket, in order to decide whether to retry the read. However, timeouts
        // are much more commonly just because Chrome has nothing to report (e.g. a pageload or client-side event is
        // taking longer than the socket read timeout to complete), or a tab has crashed. In both these cases, we have
        // observed that the JSON API of the main browser process is always available, so this method of check never
        // identifies new genuine failure cases but historically was producing false positive reports that the browser
        // had crashed.
        return false;
    }

    /**
     * Get the current URL.
     *
     * @return string
     */
    protected function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Connect to the client.
     *
     * @param null $url
     */
    public function connect($url = null): void
    {
        $url = $url == null ? $this->url : $url;
        $options = ['fragment_size' => 2000000]; // Chrome closes the connection if a message is sent in fragments
        if (is_numeric($this->socket_timeout) && $this->socket_timeout > 0) {
            $options['timeout'] = (int)$this->socket_timeout;
        }
        $this->client = new Client($url, $options);
    }

    /**
     * Close the client connection.
     */
    public function close(): void
    {
        $this->client->close();
    }

    /**
     * Send a command to the client.
     *
     * @param string $command
     * @param array $parameters
     * @return null|string|string[][]
     * @throws \Exception
     */
    public function send($command, array $parameters = []): array
    {
        $payload['id'] = $this->command_id++;
        $payload['method'] = $command;
        if (!empty($parameters)) {
            $payload['params'] = $parameters;
        }

        $this->client->send(json_encode($payload));

        $data = $this->waitFor(
            function ($data) use ($payload) {
                return array_key_exists('id', $data) && $data['id'] == $payload['id'];
            }
        );

        if (isset($data['result'])) {
            return $data['result'];
        }

        return ['result' => ['type' => 'undefined']];
    }

    /**
     * Wait on response from client.
     *
     * @param callable $is_ready
     * @return mixed|null
     * @throws ConnectionException
     * @throws DriverException
     */
    protected function waitFor(callable $is_ready)
    {
        $data = [];
        while (true) {
            try {
                $response = $this->client->receive();
            } catch (ConnectionException $exception) {
                // NB - this may throw a TimeoutException if the socket read times out simply because Chrome has nothing
                // to report within the specified socket_timeout - e.g. initial server-side document request takes
                // longer than the timeout, or Chrome is stalled on a window.alert|confirm|prompt or other client-side
                // operation. This catch is retained temporarily as this will be the correct place to handle a Timeout
                // in future.
                throw $exception;
            }

            if (is_null($response)) {
                return null;
            }

            if ($data = json_decode($response, true)) {
                if (array_key_exists('error', $data)) {
                    $message = isset($data['error']['data']) ?
                        $data['error']['message'] . '. ' . $data['error']['data'] : $data['error']['message'];
                    throw new DriverException($message, $data['error']['code']);
                }

                // What's this doing?
                if ($this->processResponse($data)) {
                    break;
                }

                if ($is_ready($data)) {
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Process a client response.
     *
     * @param  array $data
     * @return bool
     * @throws DriverException
     */
    abstract protected function processResponse(array $data): bool;
}
