<?php

namespace DMore\ChromeDriver;

use Behat\Mink\Exception\DriverException;
use WebSocket\ConnectionException;

class ChromeBrowser extends DevToolsConnection
{
    /**
     * @var string
     */
    private $context_id;

    /**
     * @var bool
     */
    private $headless = true;

    /**
     * @var HttpClient
     */
    private $http_client;

    /**
     * @var string
     */
    private $http_uri;

    /**
     * @var string
     */
    private $version;

    /**
     * Set the HTTP client.
     *
     * @param HttpClient $client
     */
    public function setHttpClient(HttpClient $client): void
    {
        $this->http_client = $client;
    }

    /**
     * Set the HTTP URI.
     *
     * @param string $http_uri
     */
    public function setHttpUri($http_uri): void
    {
        $this->http_uri = $http_uri;
    }

    /**
     * Start.
     *
     * @throws DriverException
     */
    public function start(): string
    {
        $response = $this->http_client->get($this->http_uri . '/json/version');
        $versionInfo = json_decode($response);

        // Detect if Chrome is running
        if (null === $versionInfo) {
            $jsonError = json_last_error_msg();

            throw new \RuntimeException(
                sprintf(
                    "Could not fetch version information from %s. Check if Chrome is running. " .
                    "See docs/troubleshooting.md if Chrome crashed unexpectedly." . PHP_EOL .
                    "Json Error: %s." . PHP_EOL .
                    "Response was: %s",
                    $this->http_uri . '/json/version',
                    $jsonError,
                    $response
                )
            );
        }

        // Detect Browser version
        if (property_exists($versionInfo, 'Browser')) {
            $start = strpos($versionInfo->Browser, '/') + 1;
            $this->version = (int) substr($versionInfo->Browser, $start, strpos($versionInfo->Browser, '.') - $start);
        }

        // Detect if Chrome has been started in Headless Mode
        if (property_exists($versionInfo, 'Browser') && strpos($versionInfo->Browser, 'Headless') === false) {
            $this->headless = false;
        }

        if ($this->headless) {
            try {
                $versionInfo = json_decode($this->http_client->get($this->http_uri . '/json/version'));
                // handling chrome versions 62+ where Target.createBrowserContext moved to new location
                if (property_exists($versionInfo, 'webSocketDebuggerUrl')) {
                    $debugUrl = $versionInfo->webSocketDebuggerUrl;
                    $this->connect($debugUrl);
                }

                $this->context_id = $this->send('Target.createBrowserContext')['browserContextId'];
                $data = $this->send(
                    'Target.createTarget',
                    ['url' => 'about:blank', 'browserContextId' => $this->context_id]
                );
                return $data['targetId'];
            } catch (DriverException $exception) {
                if ($exception->getCode() == '-32601' || $exception->getCode() == '-32000') {
                    $this->headless = false;
                } else {
                    throw $exception;
                }
            }
        }

        $json = $this->http_client->put($this->http_uri . '/json/new');
        $response = json_decode($json, true);
        return $response['id'];
    }

    /**
     * Close the session.
     *
     * @throws ConnectionException
     */
    public function close(): void
    {
        if ($this->headless) {
            if (!$this->send('Target.disposeBrowserContext', ['browserContextId' => $this->context_id])) {
                throw new ConnectionException('Unable to close browser context');
            }
        }
        parent::close();
    }

    /**
     * {inheritDoc}
     */
    protected function processResponse(array $data): bool
    {
        return false;
    }


    /**
     * @return bool
     */
    public function isHeadless()
    {
        return $this->headless;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}
