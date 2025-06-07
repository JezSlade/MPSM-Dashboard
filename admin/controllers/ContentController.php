<?php
class ContentController {
    public function list(): void {
        $rows = Database::get()->query("SELECT * FROM content")
                     ->fetchAll(PDO::FETCH_ASSOC);
        include ADMIN_PATH . '/views/content_list.php';
    }
    public function edit(): void {
        $id = $_GET['id'] ?? null;
        $row = null;
        if ($id) {
            $stmt = Database::get()->prepare("SELECT * FROM content WHERE id=:id");
            $stmt->execute([':id'=>$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        include ADMIN_PATH . '/views/content_edit.php';
    }
    public function save(): void {
        $pdo = Database::get();
        if (!empty($_POST['id'])) {
            $stmt = $pdo->prepare("
              UPDATE content SET slug=:s,title=:t,body=:b WHERE id=:id
            ");
            $stmt->execute([
              ':s'=>$_POST['slug'],
              ':t'=>$_POST['title'],
              ':b'=>$_POST['body'],
              ':id'=>$_POST['id']
            ]);
        } else {
            $stmt = $pdo->prepare("
              INSERT INTO content (slug,title,body)
              VALUES (:s,:t,:b)
            ");
            $stmt->execute([
              ':s'=>$_POST['slug'],
              ':t'=>$_POST['title'],
              ':b'=>$_POST['body']
            ]);
        }
        header('Location: ' . APP_BASE . '/?path=admin/content');
        exit;
    }
}
