<?php

namespace App\Service;

class APIService
{
    protected string $baseURL;
    protected array $defaultHeaders;

    /**
     * Constructor function. Takes baseURL string and optional default headers array.
     * Expected format: defaultHeaders ==> [ 'header_key: ' . 'header_value' ]
     * @param string $baseURL
     * @param array $defaultHeaders
     */
    function __construct(string $baseURL, array $defaultHeaders = []) {
        $this->baseURL = $baseURL;
        $this->defaultHeaders = $defaultHeaders;
    }


    /**
     * Function for handling calls to $this->baseURL . $path
     * All relevant calls are GET calls so no need to
     * include other call methods.
     * Future updates: Include other methods besides GET, include
     * optional headers, params, body fields. Currently, params are
     * handled by calling function. No extra headers or body fields
     * were needed for this project.
     * @param $path
     * @return bool|string
     */
    public function CallAPI($path): bool|string
    {
        $curl = curl_init();
        $url = $this->baseURL . $path;
        $headers = $this->defaultHeaders;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

}