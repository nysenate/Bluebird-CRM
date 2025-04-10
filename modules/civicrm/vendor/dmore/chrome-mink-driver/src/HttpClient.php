<?php

namespace DMore\ChromeDriver;

class HttpClient
{
    public function get($url)
    {
        return $this->request($url, 'GET');
    }

    public function put($url)
    {
        return $this->request($url, 'PUT');
    }

    protected function request($url, $verb)
    {
        $curl = curl_init();
        if ($curl === false) {
            throw new \RuntimeException('Error while initializing curl');
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ('GET' !== $verb) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $verb);
        }
        // To prevent DNS rebinding attacks, Chrome 66 and later only accepts
        // Host header with IP address or localhost.
        $parsed_url = parse_url($url);
        if ($parsed_url['host'] != 'localhost') {
            $host = gethostbyname($parsed_url['host']);
            if ($host != $parsed_url['host']) {
                if (!empty($parsed_url['port'])) {
                    $host .= ':' . $parsed_url['port'];
                }
                curl_setopt($curl, CURLOPT_HTTPHEADER, ["Host: $host"]);
            }
        }
        return curl_exec($curl);
    }
}
