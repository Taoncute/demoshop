<?php
/**
 * VietQR Helper Class
 * Generate QR code for bank transfer
 */

class VietQR {
    
    /**
     * Generate VietQR URL
     * 
     * @param string $accountNo Bank account number
     * @param string $accountName Account holder name
     * @param string $bankId Bank ID (VCB, TCB, MB, etc.)
     * @param float $amount Transfer amount
     * @param string $description Transfer description
     * @param string $template QR template (compact, compact2, qr_only)
     * @return string QR code image URL
     */
    public static function generateQR($accountNo, $accountName, $bankId, $amount, $description, $template = 'compact') {
        // VietQR API endpoint
        $baseUrl = 'https://img.vietqr.io/image';
        
        // Build URL
        $url = "{$baseUrl}/{$bankId}-{$accountNo}-{$template}.png";
        $url .= "?amount={$amount}";
        $url .= "&addInfo=" . urlencode($description);
        $url .= "&accountName=" . urlencode($accountName);
        
        return $url;
    }

    /**
     * Generate order QR code
     */
    public static function generateOrderQR($orderCode, $amount) {
        $description = "Thanh toan don hang {$orderCode}";
        
        return self::generateQR(
            VIETQR_ACCOUNT_NO,
            VIETQR_ACCOUNT_NAME,
            VIETQR_BANK_ID,
            $amount,
            $description,
            VIETQR_TEMPLATE
        );
    }

    /**
     * Generate topup QR code
     */
    public static function generateTopupQR($transactionCode, $amount) {
        $description = "Nap tien {$transactionCode}";
        
        return self::generateQR(
            VIETQR_ACCOUNT_NO,
            VIETQR_ACCOUNT_NAME,
            VIETQR_BANK_ID,
            $amount,
            $description,
            VIETQR_TEMPLATE
        );
    }

    /**
     * Parse transaction description to get reference code
     */
    public static function parseDescription($description) {
        // Extract SHOP_XXXXX or TOPUP_XXXXX from description
        if (preg_match('/(SHOP_[A-Z0-9]+|TOPUP_[A-Z0-9]+)/i', $description, $matches)) {
            return strtoupper($matches[1]);
        }
        return null;
    }

    /**
     * Verify payment (placeholder - needs actual bank API integration)
     * 
     * In production, you would:
     * 1. Use bank API to check transactions
     * 2. Use webhook from payment gateway
     * 3. Use third-party service like PayOS, Casso, etc.
     */
    public static function verifyPayment($referenceCode, $amount) {
        // This is a placeholder
        // In real implementation, you would call bank API or payment gateway
        
        // Example with PayOS or Casso:
        // $apiKey = PAYMENT_API_KEY;
        // $apiUrl = PAYMENT_API_URL . "/transactions?reference={$referenceCode}";
        // $response = file_get_contents($apiUrl, false, stream_context_create([
        //     'http' => ['header' => "Authorization: Bearer {$apiKey}"]
        // ]));
        // $data = json_decode($response, true);
        // return $data['status'] === 'success' && $data['amount'] >= $amount;
        
        return false;
    }
}
