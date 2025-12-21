<?php

class YandexOrderFormatter
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    private function formatPhone($phone)
    {
        $res = preg_replace('/[^\d]/', '', $phone);
        if (strlen($res) === 10) $res = '7' . $res;
        if (strpos($res, '8') === 0) $res = '7' . substr($res, 1);
        return '+' . $res;
    }

    public function getOrderData()
    {
        $cartItems = $this->getCartItems();
        $isBusiness = (($this->data['payment_type'] ?? '') === 'business_invoice');

        $items = [];
        foreach ($cartItems as $item) {
            $items[] = [
                'title' => mb_substr($item['name'] ?? 'Товар', 0, 128),
                'quantity' => (int)($item['quantity'] ?? 1),
                'cost_value' => (string)($item['cost_per_unit'] ?? 0),
                'cost_currency' => 'RUB',
                'weight' => (float)(($item['weight'] ?? 1000) / 1000),
                'pickup_point' => 1,
                'droppof_point' => 2,
                'dimensions' => [
                    'length' => (float)(($item['length'] ?? 20) / 100),
                    'width'  => (float)(($item['width'] ?? 15) / 100),
                    'height' => (float)(($item['height'] ?? 10) / 100),
                ]
            ];
        }

        $recipientName = $isBusiness ? ($this->data['first_name_business'] ?? '') : ($this->data['first_name_individual'] ?? '');
        $recipientPhoneRaw = $isBusiness ? ($this->data['phone_business'] ?? '') : ($this->data['phone_individual'] ?? '');

        $sourcePhone = $this->formatPhone($_ENV['YANDEX_SOURCE_PHONE'] ?? '+79990000000');
        $destPhone = $this->formatPhone($recipientPhoneRaw);

        return [
            'route_points' => [
                [
                    'point_id' => 1,
                    'visit_order' => 1,
                    'type' => 'source',
                    'contact' => [
                        'name' => 'Склад DM-Chains',
                        'phone' => $sourcePhone
                    ],
                    'address' => [
                        'fullname' => $_ENV['YANDEX_SOURCE_ADDRESS'] ?? 'г. Санкт-Петербург, ул. Бабушкина, 3'
                    ]
                ],
                [
                    'point_id' => 2,
                    'visit_order' => 2,
                    'type' => 'destination',
                    'contact' => [
                        'name' => $recipientName,
                        'phone' => $destPhone
                    ],
                    'address' => [
                        'fullname' => $this->data['order_delivery_address'] ?? ''
                    ]
                ]
            ],
            'items' => $items,
            'client_requirements' => [
                'taxi_class' => 'express'
            ],
            'skip_door_to_door' => false,
            'comment' => 'Заказ с сайта. ' . ($this->data['order_datetime'] ?? '')
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
