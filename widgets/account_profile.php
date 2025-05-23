<?php
/**
 * Account Profile Widget for MPSM Dashboard
 * Displays user profile information from the API
 */
require_once 'widgets/types/api_widget.php';

/**
 * @reusable
 */
class AccountProfileWidget extends ApiWidget {
    /**
     * @reusable
     */
    public function __construct($config = []) {
        parent::__construct($config);
        $this->endpoint_id = $config['endpoint_id'] ?? '/Account/GetProfile';
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
        
        $profile = $data['Result'];
        
        return '
        <div class="widget api-widget" id="widget-' . $this->id . '">
            <div class="widget-header">
                <h3>' . htmlspecialchars($this->config['title'] ?? 'Account Profile') . '</h3>
            </div>
            <div class="widget-content">
                <div class="profile-info">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <span class="avatar-placeholder">' . substr($profile['Nominative'] ?? 'U', 0, 1) . '</span>
                        </div>
                        <div class="profile-name">
                            <h4>' . htmlspecialchars($profile['Nominative'] ?? 'Unknown User') . '</h4>
                            <p>' . htmlspecialchars($profile['Email'] ?? 'No email') . '</p>
                        </div>
                    </div>
                    <div class="profile-details">
                        <div class="profile-detail">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value">' . htmlspecialchars($profile['Phone'] ?? 'Not provided') . '</span>
                        </div>
                        <div class="profile-detail">
                            <span class="detail-label">Address:</span>
                            <span class="detail-value">' . htmlspecialchars($profile['Address'] ?? 'Not provided') . '</span>
                        </div>
                        <div class="profile-detail">
                            <span class="detail-label">Account ID:</span>
                            <span class="detail-value">' . htmlspecialchars($profile['AccountId'] ?? 'N/A') . '</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
}
