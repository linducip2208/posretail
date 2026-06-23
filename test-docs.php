<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/docs', 'GET');
$response = $kernel->handle($request);
$body = $response->getContent();

echo "Status: " . $response->getStatusCode() . "\n";
echo "Length: " . strlen($body) . "\n";

// Check for key error patterns
if (str_contains($body, 'debug-info')) {
    preg_match_all('/class="[^"]*"[^>]*>([^<]{10,200})</', $body, $m);
    foreach($m[1] as $i => $v) { if(strlen($v)>30) echo "MSG[$i]: $v\n"; }
}
file_put_contents(__DIR__.'/docs-debug.html', $body);
echo "\nFull output saved to docs-debug.html\n";
