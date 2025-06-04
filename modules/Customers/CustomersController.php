<?php
namespace Modules\Customers;
use Core\Controller;
class CustomersController extends Controller {
    protected $requiredPermission = 'Customers';
    public function handle(): void {
        $this->data['info'] = 'Customer list placeholder.';
        $this->render('Customers/customers.php');
    }
}
?>