<?php
/**
 * Panel Message Controller
 * Handles panel message webhook and query endpoints
 *
 * Routes:
 * - GET    /api/v1/panel-messages              - List recent messages
 * - GET    /api/v1/panel-messages/device/:serial - Get messages for device
 * - POST   /api/v1/panel-messages              - Store webhook message
 * - GET    /api/v1/panel-messages/stats        - Get statistics
 */

class PanelMessageController extends BaseController
{
    private PanelMessageRepository $messageRepo;

    public function __construct()
    {
        parent::__construct();
        $this->messageRepo = app(PanelMessageRepository::class);
    }

    /**
     * List recent panel messages
     *
     * Query params:
     * - hours: Time window in hours (default 24)
     * - limit: Results limit (default 100)
     * - device_serial: Filter by device serial
     */
    public function index(): void
    {
        try {
            $hours = (int) $this->query('hours', 24);
            $limit = (int) $this->query('limit', 100);
            $deviceSerial = $this->query('device_serial');

            $messages = $this->messageRepo->findRecent($hours, $limit, $deviceSerial);

            $this->success([
                'messages' => $messages,
                'count' => count($messages),
                'hours' => $hours,
            ]);
        } catch (Exception $e) {
            error_log("PanelMessageController::index error: " . $e->getMessage());
            $this->error('Failed to fetch messages', 500);
        }
    }

    /**
     * Get messages for specific device
     *
     * @param string $serial Device serial number
     */
    public function device(string $serial): void
    {
        try {
            $limit = (int) $this->query('limit', 100);
            $messages = $this->messageRepo->findByDevice($serial, $limit);

            $this->success([
                'device_serial' => $serial,
                'messages' => $messages,
                'count' => count($messages),
            ]);
        } catch (Exception $e) {
            error_log("PanelMessageController::device error: " . $e->getMessage());
            $this->error('Failed to fetch device messages', 500);
        }
    }

    /**
     * Store panel message from webhook
     *
     * POST body:
     * - customer_code: Customer code
     * - customer_description: Customer description
     * - device_serial: Device serial number
     * - maintenance_alert_code: Alert code
     * - maintenance_alert_id: Alert ID
     * - panel_configuration: Panel config
     * - payload: Full webhook payload
     */
    public function store(): void
    {
        try {
            $this->validate([
                'device_serial' => ['required' => true, 'type' => 'string'],
            ]);

            $messageData = [
                'customer_code' => $this->input('customer_code'),
                'customer_description' => $this->input('customer_description'),
                'device_serial' => $this->input('device_serial'),
                'maintenance_alert_code' => $this->input('maintenance_alert_code'),
                'maintenance_alert_id' => $this->input('maintenance_alert_id'),
                'panel_configuration' => $this->input('panel_configuration'),
                'payload' => $this->input,
            ];

            $id = $this->messageRepo->store($messageData);

            $this->success([
                'id' => $id,
                'device_serial' => $messageData['device_serial'],
            ], 'Message stored successfully', 201);
        } catch (ValidationException $e) {
            $this->validationError($e->getMessage());
        } catch (Exception $e) {
            error_log("PanelMessageController::store error: " . $e->getMessage());
            $this->error('Failed to store message', 500);
        }
    }

    /**
     * Get panel message statistics
     *
     * Query params:
     * - hours: Time window in hours (default 24)
     */
    public function stats(): void
    {
        try {
            $hours = (int) $this->query('hours', 24);
            $stats = $this->messageRepo->getStatistics($hours);

            $this->success([
                'statistics' => $stats,
                'hours' => $hours,
            ]);
        } catch (Exception $e) {
            error_log("PanelMessageController::stats error: " . $e->getMessage());
            $this->error('Failed to fetch statistics', 500);
        }
    }
}
