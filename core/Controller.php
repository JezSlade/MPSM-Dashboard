<?php
namespace Core;
abstract class Controller {
    protected $db;
    protected $data = [];
    protected $requiredPermission = null;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function authorize(): void {
        if ($this->requiredPermission !== null) {
            if (!\user_has_permission($this->requiredPermission)) {
                throw new \Core\Exceptions\NotAuthorizedException();
            }
        }
    }

    abstract public function handle(): void;

    protected function render(string $viewPath): void {
        extract($this->data, EXTR_SKIP);
        ob_start();
        require __DIR__ . "/../modules/{$viewPath}";
        $content = ob_get_clean();
        require __DIR__ . "/../views/layouts/main.php";
    }
}
?>