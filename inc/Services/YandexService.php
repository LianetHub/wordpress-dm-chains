<?php

class YandexService
{
    private $token;
    private $apiUrl = 'https://b2b.taxi.yandex.net';

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function createOrder($orderData)
    {
        $requestId = uniqid('req_');
        $url = $this->apiUrl . '/b2b/cargo/integration/v2/claims/create?request_id=' . $requestId;

        $args = [
            'body'        => json_encode($orderData),
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Accept-Language' => 'ru'
            ],
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return [
                'code'    => 'wp_remote_error',
                'message' => $response->get_error_message()
            ];
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if ($responseCode >= 400) {
            return [
                'code'    => $result['code'] ?? $responseCode,
                'message' => $result['message'] ?? 'Ошибка API Яндекса'
            ];
        }

        return $result;
    }
}
