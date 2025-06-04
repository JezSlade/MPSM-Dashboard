<?php
namespace Modules\Admin;
use Core\Controller;
class RolesController extends Controller {
    protected $requiredPermission = 'Admin';
    public function handle(): void {
        $this->data['info'] = 'Roles & Permissions UI placeholder.';
        $this->render('Admin/roles.php');
    }
}
?>