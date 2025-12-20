<?php

class YandexService
{
    private $token;
    private $apiUrl = 'https://b2b.delivery.yandex.net/api/v2';

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function createOrder($orderData)
    {
        $url = $this->apiUrl . '/offers/create';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: OAuth ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $result = json_decode($response, true);

        if (is_resource($ch) || $ch instanceof \CurlHandle) {
            curl_close($ch);
        }

        return $result;
    }
}
