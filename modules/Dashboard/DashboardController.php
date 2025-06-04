<?php
namespace Modules\Dashboard;
use Core\Controller;
class DashboardController extends Controller {
    protected $requiredPermission = 'Dashboard';
    public function handle(): void {
        $this->data['message'] = 'Welcome to the Dashboard!';
        $this->render('Dashboard/dashboard.php');
    }
}
?>