<?php

class CdekService
{
    private $clientId;
    private $clientSecret;
    private $apiUrl = 'https://api.cdek.ru/v2';
    private $token;

    public function __construct($clientId, $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    private function getAccessToken()
    {
        if ($this->token) return $this->token;

        $url = $this->apiUrl . '/oauth/token';
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $result = json_decode($response, true);

        if (is_resource($ch) || $ch instanceof \CurlHandle) {
            curl_close($ch);
        }

        if (isset($result['access_token'])) {
            $this->token = $result['access_token'];
            return $this->token;
        }

        return null;
    }

    public function createOrder($orderData)
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return ['requests' => [['errors' => [['message' => 'Auth failed: Could not get token']]]]];
        }

        $url = $this->apiUrl . '/orders';
        $jsonData = json_encode($orderData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (is_resource($ch) || $ch instanceof \CurlHandle) {
            curl_close($ch);
        }

        return json_decode($response, true);
    }
}
