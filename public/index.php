<?php
// public/index.php
// ------------------------------------------------------------------
// Single entry point—loads env, then your app bootstrap.
// ------------------------------------------------------------------
require __DIR__ . '/../config/env.php';

// From here on, any exception or error is caught by our handlers.
// You can now include your router or installer logic.
try {
    // Example: check DB connectivity stub
    // $ok = checkDatabaseConnection();
    // if (!$ok) throw new Exception('DB connect failed');
    
    echo "<!doctype html>
    <html lang='en'>
    <head>
      <meta charset='utf-8'>
      <title>MPSM Dashboard</title>
      <script>
        // Quick JS sanity-check: if JS errors before DebugService loads
        window.addEventListener('error', function() {
          document.body.insertAdjacentHTML('afterbegin',
            '<div style=\"position:fixed;top:0;left:0;width:100%;padding:5px;background:#a00;color:#fff;z-index:9999;\">JS failed to load properly—check console</div>'
          );
        });
      </script>
      <script src=\"js/services/debug.js\" defer></script>
      <link rel=\"stylesheet\" href=\"css/glass.css\">
    </head>
    <body>
      <div id=\"app\">
        <!-- Your installer or dashboard will render here -->
      </div>
      <?php
        // Optionally, dump any PHP-side debug JSON into a JS var
        if (DEBUG && isset($_ENV['_DEBUG_BUFFER'])) {
            echo '<script>console.debug("PHP debug:", '.json_encode($_ENV['_DEBUG_BUFFER']).');</script>';
        }
      ?>
    </body>
    </html>";
} catch (Throwable $e) {
    // This should never run (exception handler should catch first),
    // but just in case:
    echo '<pre style="color:red;">Bootstrap Error: ' 
         . htmlentities($e->getMessage()."\n".$e->getTraceAsString()) 
         . '</pre>';
}
