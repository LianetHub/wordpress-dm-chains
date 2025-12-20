<?php

class MailService
{
    private $data;
    private $deliveryInfo;

    public function __construct(array $data, string $deliveryInfo = '')
    {
        $this->data = $data;
        $this->deliveryInfo = $deliveryInfo;
    }

    private function getMapValue(array $map, string $key)
    {
        $val = $this->data[$key] ?? '';
        return $map[$val] ?? $val;
    }

    private function getRecipients()
    {
        $recipients = [];
        if (function_exists('get_field')) {
            $emails_repeater = get_field('send_email', 'option');
            if (is_array($emails_repeater)) {
                foreach ($emails_repeater as $row) {
                    if (!empty($row['email'])) {
                        $recipients[] = sanitize_email($row['email']);
                    }
                }
            }
        }
        return empty($recipients) ? get_option('admin_email') : implode(', ', $recipients);
    }

    public function sendOrderEmail()
    {
        $deliveryMap = [
            'yandex_pickup' => 'Яндекс Доставка — ПВЗ',
            'cdek' => 'СДЭК — Доставка',
            'pickup_spb' => 'Самовывоз (СПБ и ЛО)',
        ];

        $paymentMap = [
            'individual_card' => 'Физ. лицо (карта)',
            'business_invoice' => 'Юр. лицо / ИП (счёт)',
        ];

        $contactMap = [
            'email' => 'E-mail',
            'telegram' => 'Telegram',
            'whatsapp' => 'Whatsapp',
        ];

        $deliveryType = $this->getMapValue($deliveryMap, 'delivery_type');
        $paymentType = $this->getMapValue($paymentMap, 'payment_type');
        $contactMethod = $this->getMapValue($contactMap, 'contact_method');

        $isBusiness = (($this->data['payment_type'] ?? '') === 'business_invoice');
        $firstName = $isBusiness ? ($this->data['first_name_business'] ?? '') : ($this->data['first_name_individual'] ?? '');
        $lastName = $isBusiness ? ($this->data['last_name_business'] ?? '') : ($this->data['last_name_individual'] ?? '');
        $phone = $isBusiness ? ($this->data['phone_business'] ?? '') : ($this->data['phone_individual'] ?? '');

        $subject = 'Новый заказ с сайта';
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $to = $this->getRecipients();

        ob_start();
?>
        <h3>Информация о заказе</h3>
        <p><strong>Дата:</strong> <?php echo esc_html($this->data['order_datetime'] ?? ''); ?></p>
        <p><strong>Состав:</strong> <?php echo esc_html($this->data['product_list'] ?? ''); ?></p>
        <p><strong>ИТОГО:</strong> <?php echo esc_html($this->data['total_price'] ?? ''); ?> ₽</p>

        <h3>Доставка и Оплата</h3>
        <p><strong>Тип:</strong> <?php echo esc_html($deliveryType); ?></p>
        <p><strong>Адрес:</strong> <?php echo esc_html($this->data['order_delivery_address'] ?? ''); ?></p>
        <?php echo $this->deliveryInfo; ?>

        <h3>Контакты</h3>
        <p><strong>Клиент:</strong> <?php echo esc_html($firstName . ' ' . $lastName); ?></p>
        <p><strong>Телефон:</strong> <?php echo esc_html($phone); ?></p>
        <p><strong>Связь:</strong> <?php echo esc_html($contactMethod); ?></p>
<?php
        $message = ob_get_clean();

        return wp_mail($to, $subject, $message, $headers);
    }
}
