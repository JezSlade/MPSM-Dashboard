<?php
/**
 * Recent Transactions Widget for MPSM Dashboard
 * Displays recent transactions from the API
 */
require_once 'widgets/types/api_widget.php';

class RecentTransactionsWidget extends ApiWidget {
    public function __construct($config = []) {
        parent::__construct($config);
        $this->endpoint_id = $config['endpoint_id'] ?? '/Transactions/GetRecent';
        $this->method = $config['method'] ?? 'get';
        $this->params = $config['params'] ?? ['limit' => 5];
    }
    
    /**
     * Render widget with data
     * @param mixed $data
     * @return string HTML
     */
    protected function render_success($data) {
        if (!isset($data['Result']) || !is_array($data['Result'])) {
            return $this->render_error();
        }
        
        $transactions = $data['Result'];
        
        $html = '
        <div class="widget api-widget" id="widget-' . $this->id . '">
            <div class="widget-header">
                <h3>' . htmlspecialchars($this->config['title'] ?? 'Recent Transactions') . '</h3>
            </div>
            <div class="widget-content">
                <div class="transactions-list">';
        
        if (empty($transactions)) {
            $html .= '<p class="no-transactions">No recent transactions found.</p>';
        } else {
            $html .= '<table class="transactions-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($transactions as $transaction) {
                $amount = $transaction['Amount'] ?? 0;
                $amountClass = $amount < 0 ? 'negative' : 'positive';
                $currency_symbol = $this->get_currency_symbol($transaction['Currency'] ?? 'USD');
                
                $html .= '<tr>
                    <td>' . htmlspecialchars(date('M j, Y', strtotime($transaction['Date'] ?? 'now'))) . '</td>
                    <td>' . htmlspecialchars($transaction['Description'] ?? 'Unknown transaction') . '</td>
                    <td class="amount ' . $amountClass . '">' . $currency_symbol . htmlspecialchars(number_format(abs($amount), 2)) . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
        }
        
        $html .= '
                </div>
            </div>
        </div>';
        
        return $html;
    }
    
    /**
     * Get settings form for the widget
     * @return string HTML
     */
    public function get_settings_form() {
        $limit = $this->params['limit'] ?? 5;
        
        $form = parent::get_settings_form();
        
        // Add limit field
        $limit_field = '
        <div class="form-group">
            <label for="limit">Number of Transactions:</label>
            <input type="number" id="limit" name="limit" min="1" max="20" value="' . htmlspecialchars($limit) . '">
        </div>';
        
        // Insert before the closing form tag
        $form = str_replace('</form>', $limit_field . '</form>', $form);
        
        return $form;
    }
    
    /**
     * Save settings for the widget
     * @param array $settings
     * @return bool
     */
    public function save_settings($settings) {
        if (isset($settings['limit'])) {
            $this->params['limit'] = (int)$settings['limit'];
        }
        
        return parent::save_settings($settings);
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
