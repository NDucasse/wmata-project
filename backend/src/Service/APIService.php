<?php

namespace App\Service;


class APIService
{
    protected string $baseURL;
    protected array $defaultHeaders;
    function __construct(string $baseURL, array $defaultHeaders) {
        $this->baseURL = $baseURL;
        $this->defaultHeaders = $defaultHeaders;
    }

    // Base code from https://stackoverflow.com/a/9802854
    // Method: POST, PUT, GET etc
    // Path: string '/path/to/resource'
    // Headers: array("header_key" => "header_value")
    // Data: array("param" => "value") ==> index.php?param=value
    public function CallAPI($method, $path, $headers = [], $data = false): bool|string
    {
        $curl = curl_init();
        $url = $this->baseURL . $path;
        $headers = $this->defaultHeaders;

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }


        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

}