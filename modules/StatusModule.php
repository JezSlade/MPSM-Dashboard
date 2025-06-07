<?php
class StatusModule extends BaseModule {
    public function render(): string {
        $user = Auth::user();
        $dbOk = Database::get() ? 'OK' : 'Down';
        $parts = [
          "Login" => $user ? 'Yes ('.h($user['username']).')' : 'No',
          "Role"  => $user ? h($user['role']) : 'N/A',
          "DB"    => $dbOk,
          "Time"  => date('Y-m-d H:i:s')
        ];
        $out = "<div class='module status-module'><h3>Status</h3><ul>";
        foreach ($parts as $k=>$v) {
            $out .= "<li><strong>{$k}:</strong> {$v}</li>";
        }
        $out .= "</ul></div>";
        return $out;
    }
    public static function describe(): array {
        return ['name'=>'Status','description'=>'Current system status'];
    }
}
