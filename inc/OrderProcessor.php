<?php

class OrderProcessor
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function processOrder()
    {
        $deliveryType = $this->data['delivery_type'] ?? '';
        $deliveryInfo = '';

        if ($deliveryType === 'cdek') {
            $deliveryInfo = $this->handleCdek();
        } elseif ($deliveryType === 'yandex_pickup') {
            $deliveryInfo = $this->handleYandex();
        }

        $mailService = new MailService($this->data, $deliveryInfo);
        $mailSent = $mailService->sendOrderEmail();

        if ($mailSent) {
            if (class_exists('WooCommerce') && WC()->cart) {
                WC()->cart->empty_cart();
            }
            return ['status' => 'success', 'message' => 'Заказ успешно отправлен'];
        }

        return ['status' => 'error', 'message' => 'Ошибка отправки письма'];
    }

    private function handleCdek()
    {
        $service = DeliveryServiceFactory::make('cdek');
        $formatter = new CdekOrderFormatter($this->data);

        $cartItems = $formatter->getCartItems();
        if (empty($cartItems)) return '<p style="color:red;">СДЭК: Корзина пуста</p>';

        $result = $service->createOrder($formatter->getOrderData());

        if (isset($result['entity']['uuid'])) {
            return '<p style="color:green;">СДЭК создан: ' . esc_html($result['entity']['uuid']) . '</p>';
        }

        $error = $result['requests'][0]['errors'][0]['message'] ?? 'Ошибка СДЭК';
        return '<p style="color:red;">СДЭК Ошибка: ' . esc_html($error) . '</p>';
    }

    private function handleYandex()
    {
        $service = DeliveryServiceFactory::make('yandex_pickup');
        $formatter = new YandexOrderFormatter($this->data);

        $result = $service->createOrder($formatter->getOrderData());

        if (isset($result['id'])) {
            $status = $result['status'] ?? 'создана';
            return '<p style="color:green;">Яндекс Доставка: заявка ' . esc_html($result['id']) . ' (' . esc_html($status) . ')</p>';
        }

        $error = $result['message'] ?? 'Ошибка Яндекса';
        $errorCode = $result['code'] ?? '';

        $fullError = $errorCode ? "[$errorCode] $error" : $error;

        return '<p style="color:red;">Яндекс Ошибка: ' . esc_html($fullError) . '</p>';
    }
}
