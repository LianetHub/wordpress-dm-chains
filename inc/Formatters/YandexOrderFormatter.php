<?php

class YandexOrderFormatter
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getOrderData()
    {
        $cartItems = $this->getCartItems();
        $isBusiness = (($this->data['payment_type'] ?? '') === 'business_invoice');

        $items = [];
        foreach ($cartItems as $item) {
            $items[] = [
                'name' => mb_substr($item['name'] ?? 'Товар', 0, 128),
                'count' => (int)($item['quantity'] ?? 1),
                'price' => (float)($item['cost_per_unit'] ?? 0),
                'weight' => (float)(($item['weight'] ?? 1000) / 1000),
                'dimensions' => [
                    'length' => (int)($item['length'] ?? 20),
                    'width' => (int)($item['width'] ?? 15),
                    'height' => (int)($item['height'] ?? 10),
                ]
            ];
        }

        $recipientName = $isBusiness ? ($this->data['first_name_business'] ?? '') : ($this->data['first_name_individual'] ?? '');
        $recipientPhone = $isBusiness ? ($this->data['phone_business'] ?? '') : ($this->data['phone_individual'] ?? '');

        return [
            'info' => [
                'comment' => 'Заказ с сайта ' . ($this->data['order_datetime'] ?? ''),
            ],
            'source' => [
                'platform_station_id' => $_ENV['YANDEX_DELIVERY_PLATFORM_STATION_ID'] ?? '',
            ],
            'destination' => [
                'type' => 'platform_station',
                'platform_station_id' => $this->data['yandex_pvz_id'] ?? '',
            ],
            'recipient_info' => [
                'first_name' => $recipientName,
                'phone' => $recipientPhone,
            ],
            'items' => $items,
            'last_mile_policy' => 'self_delivery'
        ];
    }

    public function getCartItems()
    {
        $cart_items = [];
        if (!empty($this->data['cart_items'])) {
            $cart_items = json_decode(stripslashes($this->data['cart_items']), true);
        }
        return is_array($cart_items) ? $cart_items : [];
    }
}
