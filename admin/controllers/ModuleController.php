<?php
class ModuleController {
    public function list(): void {
        $mods = Database::get()
          ->query("SELECT mi.id,m.name,mi.position,mi.config
                   FROM module_instances mi
                   JOIN modules m ON mi.module_id=m.id")
          ->fetchAll(PDO::FETCH_ASSOC);
        include ADMIN_PATH . '/views/modules.php';
    }
}
