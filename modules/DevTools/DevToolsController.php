<?php
namespace Modules\DevTools;
use Core\Controller;
class DevToolsController extends Controller {
    protected $requiredPermission = 'DevTools';
    public function handle(): void {
        $this->data['info'] = 'DevTools placeholder (debug).';
        $this->render('DevTools/devtools.php');
    }
}
?>