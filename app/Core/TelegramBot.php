<?php
/**
 * Telegram Bot Helper Class
 */

class TelegramBot {
    
    private static $botToken;
    private static $apiUrl;

    /**
     * Initialize bot
     */
    private static function init() {
        if (!self::$botToken) {
            self::$botToken = TELEGRAM_BOT_TOKEN;
            self::$apiUrl = "https://api.telegram.org/bot" . self::$botToken;
        }
    }

    /**
     * Send message to chat
     */
    public static function sendMessage($chatId, $message, $parseMode = 'HTML') {
        self::init();
        
        $url = self::$apiUrl . "/sendMessage";
        
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => $parseMode
        ];
        
        return self::makeRequest($url, $data);
    }

    /**
     * Send message to admin
     */
    public static function sendToAdmin($message) {
        return self::sendMessage(TELEGRAM_ADMIN_CHAT_ID, $message);
    }

    /**
     * Notify new order
     */
    public static function notifyNewOrder($orderCode, $username, $totalAmount, $items) {
        $message = "🛒 <b>Đơn hàng mới!</b>\n\n";
        $message .= "📋 Mã đơn: <code>{$orderCode}</code>\n";
        $message .= "👤 Khách hàng: {$username}\n";
        $message .= "💰 Tổng tiền: " . number_format($totalAmount) . "đ\n\n";
        $message .= "📦 Sản phẩm:\n";
        
        foreach ($items as $item) {
            $message .= "  • {$item['name']} x{$item['quantity']} - " . number_format($item['price'] * $item['quantity']) . "đ\n";
        }
        
        return self::sendToAdmin($message);
    }

    /**
     * Notify order completed
     */
    public static function notifyOrderCompleted($orderCode, $username, $telegramId = null) {
        $adminMessage = "✅ <b>Đơn hàng hoàn thành!</b>\n\n";
        $adminMessage .= "📋 Mã đơn: <code>{$orderCode}</code>\n";
        $adminMessage .= "👤 Khách hàng: {$username}\n";
        
        self::sendToAdmin($adminMessage);
        
        // Send to user if telegram linked
        if ($telegramId) {
            $userMessage = "✅ <b>Đơn hàng của bạn đã hoàn thành!</b>\n\n";
            $userMessage .= "📋 Mã đơn: <code>{$orderCode}</code>\n";
            $userMessage .= "Cảm ơn bạn đã mua hàng! 🎉";
            
            self::sendMessage($telegramId, $userMessage);
        }
    }

    /**
     * Notify topup request
     */
    public static function notifyTopupRequest($transactionCode, $username, $amount) {
        $message = "💳 <b>Yêu cầu nạp tiền mới!</b>\n\n";
        $message .= "📋 Mã GD: <code>{$transactionCode}</code>\n";
        $message .= "👤 Người dùng: {$username}\n";
        $message .= "💰 Số tiền: " . number_format($amount) . "đ\n";
        
        return self::sendToAdmin($message);
    }

    /**
     * Notify topup completed
     */
    public static function notifyTopupCompleted($transactionCode, $username, $amount, $telegramId = null) {
        $adminMessage = "✅ <b>Nạp tiền thành công!</b>\n\n";
        $adminMessage .= "📋 Mã GD: <code>{$transactionCode}</code>\n";
        $adminMessage .= "👤 Người dùng: {$username}\n";
        $adminMessage .= "💰 Số tiền: " . number_format($amount) . "đ\n";
        
        self::sendToAdmin($adminMessage);
        
        // Send to user if telegram linked
        if ($telegramId) {
            $userMessage = "✅ <b>Nạp tiền thành công!</b>\n\n";
            $userMessage .= "💰 Số tiền: " . number_format($amount) . "đ\n";
            $userMessage .= "Số dư của bạn đã được cập nhật! 🎉";
            
            self::sendMessage($telegramId, $userMessage);
        }
    }

    /**
     * Send VPS info to user
     */
    public static function sendVPSInfo($telegramId, $orderCode, $vpsInfo) {
        $message = "🖥️ <b>Thông tin VPS của bạn</b>\n\n";
        $message .= "📋 Đơn hàng: <code>{$orderCode}</code>\n";
        $message .= "🌐 IP: <code>{$vpsInfo['ip_address']}</code>\n";
        $message .= "👤 Username: <code>{$vpsInfo['username']}</code>\n";
        $message .= "🔑 Password: <code>{$vpsInfo['password']}</code>\n";
        
        if (!empty($vpsInfo['os_info'])) {
            $message .= "💿 OS: {$vpsInfo['os_info']}\n";
        }
        
        if (!empty($vpsInfo['specs'])) {
            $message .= "⚙️ Cấu hình: {$vpsInfo['specs']}\n";
        }
        
        $message .= "\n⚠️ Vui lòng lưu lại thông tin này!";
        
        return self::sendMessage($telegramId, $message);
    }

    /**
     * Make HTTP request to Telegram API
     */
    private static function makeRequest($url, $data) {
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === false) {
            error_log("Telegram API request failed: " . $url);
            return false;
        }
        
        return json_decode($result, true);
    }

    /**
     * Get bot info (for testing)
     */
    public static function getMe() {
        self::init();
        $url = self::$apiUrl . "/getMe";
        $result = @file_get_contents($url);
        return json_decode($result, true);
    }
}
