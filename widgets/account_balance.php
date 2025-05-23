<?php
/**
 * Account Balance Widget for MPSM Dashboard
 * Displays account balance information from the API
 */
require_once 'widgets/types/api_widget.php';

/**
 * @reusable
 */
class AccountBalanceWidget extends ApiWidget {
    /**
     * @reusable
     */
    public function __construct($config = []) {
        parent::__construct($config);
        $this->endpoint_id = $config['endpoint_id'] ?? '/Account/GetBalance';
        $this->method = $config['method'] ?? 'get';
    }
    
    /**
     * Render widget with data
     * @param mixed $data
     * @return string HTML
     */
    protected function render_success($data) {
        if (!isset($data['Result'])) {
            return $this->render_error();
        }
        
        $balance = $data['Result'];
        
        // Format the amount with proper currency symbol
        $currency_symbol = $this->get_currency_symbol($balance['Currency'] ?? 'USD');
        $formatted_amount = number_format($balance['Amount'] ?? 0, 2);
        
        // Determine if balance is positive or negative
        $balance_class = ($balance['Amount'] ?? 0) >= 0 ? 'positive' : 'negative';
        
        return '
        <div class="widget api-widget" id="widget-' . $this->id . '">
            <div class="widget-header">
                <h3>' . htmlspecialchars($this->config['title'] ?? 'Account Balance') . '</h3>
            </div>
            <div class="widget-content">
                <div class="balance-info">
                    <div class="balance-amount ' . $balance_class . '">
                        <span class="currency-symbol">' . $currency_symbol . '</span>
                        <span class="amount">' . $formatted_amount . '</span>
                    </div>
                    <div class="balance-details">
                        <div class="balance-detail">
                            <span class="detail-label">Currency:</span>
                            <span class="detail-value">' . htmlspecialchars($balance['Currency'] ?? 'USD') . '</span>
                        </div>
                        <div class="balance-detail">
                            <span class="detail-label">Last Updated:</span>
                            <span class="detail-value">' . htmlspecialchars(date('M j, Y g:i A', strtotime($balance['LastUpdated'] ?? 'now'))) . '</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Get currency symbol from currency code
     * @param string $currency_code
     * @return string
     */
    private function get_currency_symbol($currency_code) {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'CHF' => 'Fr',
            'CNY' => '¥',
            'INR' => '₹',
            'RUB' => '₽'
        ];
        
        return $symbols[$currency_code] ?? $currency_code;
    }
}
