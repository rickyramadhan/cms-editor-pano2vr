<?php
declare(strict_types=1);
// smart-schema.php — menyajikan definisi formulir konten Smart Kit (studio/ui/schema.js) ke GUI CMS, satu sumber dengan Studio.
define('SMART_HUB_INCLUDE_ONLY', true);
require_once __DIR__ . '/smart-hub.php';
$st = shSettings(activeProject());
$file = $st['path'] . '/studio/ui/schema.js';
if (!is_file($file)) { http_response_code(404); header('Content-Type: text/plain'); echo 'schema.js tidak ditemukan'; exit; }
header('Content-Type: text/javascript; charset=utf-8');
header('Cache-Control: no-cache');
readfile($file);
