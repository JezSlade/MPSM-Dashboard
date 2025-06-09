<?php
class ModuleManager {
    private static array $instances = [];

    public static function init(): void {
        $pdo = Database::get();
        $existing = $pdo->query("SELECT class FROM modules")
                        ->fetchAll(PDO::FETCH_COLUMN);

        // Register new module classes
        foreach (glob(MODULE_PATH . "/*Module.php") as $file) {
            require_once $file;
            $class = pathinfo($file, PATHINFO_FILENAME);
            if (!in_array($class, $existing, true)) {
                $ins = $pdo->prepare("INSERT INTO modules (name,class) VALUES (:n,:c)");
                $ins->execute([
                    ':n' => str_replace('Module','',$class),
                    ':c' => $class
                ]);
            }
        }

        // Load active instances
        $sql = "
          SELECT mi.id,m.class,mi.position,mi.config
          FROM module_instances mi
          JOIN modules m ON mi.module_id=m.id
          WHERE mi.is_active=1
        ";
        foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (class_exists($r['class'])) {
                $cfg = json_decode($r['config'], true) ?: [];
                self::$instances[$r['position']][] =
                    new $r['class']($cfg);
            }
        }
    }

    public static function renderPosition(string $pos): void {
        foreach (self::$instances[$pos] ?? [] as $mod) {
            try {
                echo $mod->render();
            } catch (Exception $e) {
                echo "<div class='module-error'>Error: " . h($e->getMessage()) . "</div>";
            }
        }
    }
}
