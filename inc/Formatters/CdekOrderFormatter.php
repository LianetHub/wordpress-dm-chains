<?php

class CdekOrderFormatter
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getCartItems()
    {
        $cart_items = [];
        if (!empty($this->data['cart_items'])) {
            $cart_items = json_decode(stripslashes($this->data['cart_items']), true);
        }
        return is_array($cart_items) ? $cart_items : [];
    }

    private function getRecipientName(bool $isBusiness)
    {
        $firstName = $isBusiness ? ($this->data['first_name_business'] ?? '') : ($this->data['first_name_individual'] ?? '');
        $lastName = $isBusiness ? ($this->data['last_name_business'] ?? '') : ($this->data['last_name_individual'] ?? '');
        return trim($firstName . ' ' . $lastName);
    }

    private function getRecipientPhone(bool $isBusiness)
    {
        return $isBusiness ? ($this->data['phone_business'] ?? '') : ($this->data['phone_individual'] ?? '');
    }

    public function getOrderData()
    {
        $cartItems = $this->getCartItems();
        $isBusiness = (($this->data['payment_type'] ?? '') === 'business_invoice');

        $totalWeight = 0;
        $cdekItems = [];
        $firstItemName = 'Заказ с сайта';

        if (!empty($cartItems)) {
            $firstItemName = 'Заказ: ' . ($cartItems[0]['name'] ?? 'Товары');

            foreach ($cartItems as $index => $item) {
                $itemWeight = (int)($item['weight'] ?? 1000);
                $itemQuantity = (int)($item['quantity'] ?? 1);
                $itemCostPerUnit = (float)($item['cost_per_unit'] ?? 0);
                $itemName = $item['name'] ?? 'Товар без названия';
                $finalItemCost = round($itemCostPerUnit * $itemQuantity, 2);
                $totalWeight += $itemWeight * $itemQuantity;

                $cdekItems[] = [
                    'name' => mb_substr($itemName, 0, 150),
                    'ware_key' => $item['sku'] ?? ('goods-' . ($index + 1)),
                    'payment' => ['value' => 0],
                    'cost' => $finalItemCost,
                    'weight' => $itemWeight,
                    'amount' => $itemQuantity,
                ];
            }
        }

        $packageLength = (int)($cartItems[0]['length'] ?? 20);
        $packageWidth = (int)($cartItems[0]['width'] ?? 15);
        $packageHeight = (int)($cartItems[0]['height'] ?? 10);
        $finalTotalWeight = max(100, $totalWeight);

        $orderData = [
            'type' => 1,
            'number' => uniqid('ORDER_'),
            'tariff_code' => (int)($this->data['cdek_tariff_code'] ?? 0),
            'comment' => 'Заказ с сайта. Состав: ' . mb_substr(($this->data['product_list'] ?? ''), 0, 200),
            'shipment_point' => 'SPB12',
            'recipient' => [
                'name' => $this->getRecipientName($isBusiness),
                'phones' => [['number' => $this->getRecipientPhone($isBusiness)]]
            ],
            'packages' => [
                [
                    'number' => '1',
                    'weight' => $finalTotalWeight,
                    'length' => $packageLength,
                    'width' => $packageWidth,
                    'height' => $packageHeight,
                    'comment' => $firstItemName,
                    'items' => $cdekItems
                ]
            ]
        ];

        if (!empty($this->data['cdek_pvz_code'])) {
            $orderData['delivery_point'] = $this->data['cdek_pvz_code'];
        } elseif (!empty($this->data['order_delivery_address'])) {
            $orderData['to_location'] = ['address' => $this->data['order_delivery_address']];
        }

        return $orderData;
    }
}
