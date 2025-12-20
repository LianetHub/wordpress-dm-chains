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
            'yandex_pickup' => 'Яндекс Доставка — Доставка до ПВЗ',
            'cdek'          => 'СДЭК — Доставка',
            'pickup_spb'    => 'Самовывоз (СПБ и ЛО)',
        ];

        $paymentMap = [
            'individual_card'  => 'Я — физическое лицо (оплата картой)',
            'business_invoice' => 'Я — юридическое лицо или ИП (оплата по счёту)',
        ];

        $contactMap = [
            'email'    => 'Электронная почта',
            'telegram' => 'Telegram',
            'whatsapp' => 'Whatsapp',
        ];

        $deliveryType = $this->getMapValue($deliveryMap, 'delivery_type');
        $paymentType  = $this->getMapValue($paymentMap, 'payment_type');
        $contactMethod = $this->getMapValue($contactMap, 'contact_method');

        $isBusiness = (($this->data['payment_type'] ?? '') === 'business_invoice');

        $firstName = $isBusiness ? ($this->data['first_name_business'] ?? '') : ($this->data['first_name_individual'] ?? '');
        $lastName = $isBusiness ? ($this->data['last_name_business'] ?? '') : ($this->data['last_name_individual'] ?? '');
        $middleName = $isBusiness ? ($this->data['middle_name_business'] ?? '') : ($this->data['middle_name_individual'] ?? '');
        $phone = $isBusiness ? ($this->data['phone_business'] ?? '') : ($this->data['phone_individual'] ?? '');

        $subject = 'Новый заказ с сайта';
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $to = $this->getRecipients();

        ob_start();
?>
        <h3>Информация о заказе</h3>
        <p>
            <strong>Дата и время заявки:</strong> <?php echo esc_html($this->data['order_datetime'] ?? ''); ?><br>
            <strong>Состав заказа:</strong> <?php echo esc_html($this->data['product_list'] ?? ''); ?>
        </p>
        <p>
            <strong>Сумма товаров:</strong> <?php echo esc_html($this->data['price_product'] ?? '0'); ?> ₽<br>
            <strong>Стоимость доставки:</strong> <?php echo esc_html($this->data['price_delivery'] ?? '0'); ?> ₽<br>
            <strong>ИТОГО:</strong> <strong><?php echo esc_html($this->data['total_price'] ?? '0'); ?> ₽</strong>
        </p>

        <h3>Доставка и Оплата</h3>
        <p>
            <strong>Тип доставки:</strong> <?php echo esc_html($deliveryType); ?><br>
            <?php if (!empty($this->data['order_delivery_address'])) : ?>
                <strong>Адрес доставки:</strong> <?php echo esc_html($this->data['order_delivery_address']); ?><br>
            <?php endif; ?>
            <strong>Тип оплаты:</strong> <?php echo esc_html($paymentType); ?>
        </p>

        <?php if (!empty($this->deliveryInfo)): ?>
            <?php echo $this->deliveryInfo; ?>
        <?php endif; ?>

        <?php if ($isBusiness): ?>
            <h3>Детали организации:</h3>
            <p>
                <strong>ИНН:</strong> <?php echo esc_html($this->data['inn'] ?? ''); ?><br>
                <strong>Название организации:</strong> <?php echo esc_html($this->data['organization_name'] ?? ''); ?><br>
                <strong>Юридический адрес:</strong> <?php echo esc_html($this->data['legal_address'] ?? ''); ?>
            </p>
        <?php endif; ?>

        <h3>Контактная информация</h3>
        <p>
            <strong>Имя:</strong> <?php echo esc_html($firstName); ?><br>
            <strong>Фамилия:</strong> <?php echo esc_html($lastName); ?><br>
            <strong>Отчество:</strong> <?php echo esc_html($middleName); ?><br>
            <strong>Телефон:</strong> <?php echo esc_html($phone); ?> <br>
        </p>

        <p>
            <strong>Предпочтительный способ связи:</strong> <?php echo esc_html($contactMethod); ?>
        </p>

        <?php if (!empty($this->data['email'])): ?>
            <p><strong>E-mail:</strong> <?php echo esc_html($this->data['email']); ?></p>
        <?php endif; ?>

        <?php if (!empty($this->data['telegram_user'])): ?>
            <p><strong>Имя пользователя Telegram:</strong> <?php echo esc_html($this->data['telegram_user']); ?></p>
        <?php endif; ?>

        <?php if (!empty($this->data['whatsapp_phone'])): ?>
            <p><strong>Номер для WhatsApp:</strong> <?php echo esc_html($this->data['whatsapp_phone']); ?></p>
        <?php endif; ?>

        <p>
            <strong>Согласие на обработку данных:</strong> <?php echo isset($this->data['confirm_policies']) ? 'Да' : 'Нет'; ?>
        </p>
<?php
        $message = ob_get_clean();

        return wp_mail($to, $subject, $message, $headers);
    }
}
