<?php

class DeliveryServiceFactory
{
    public static function make(string $type)
    {
        switch ($type) {
            case 'cdek':
                return new CdekService(
                    $_ENV['CDEK_ID'] ?? '',
                    $_ENV['CDEK_PASSWORD'] ?? ''
                );
            case 'yandex_pickup':
                return new YandexService(
                    $_ENV['YANDEX_DELIVERY_API_TOKEN'] ?? ''
                );
            default:
                return null;
        }
    }
}
