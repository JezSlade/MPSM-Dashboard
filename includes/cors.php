<?php
// only for /api/*.php
function send_cors_headers(): void {
  header('Access-Control-Allow-Origin: https://chat.openai.com');
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type, Authorization');
  if ($_SERVER['REQUEST_METHOD']==='OPTIONS') exit(0);
}
