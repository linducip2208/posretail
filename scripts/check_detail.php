<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\Models\User::first();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app['auth']->guard('web')->setUser($user);
$no = App\Models\Order::value('order_number');
echo "sample order_number=[$no]\n";
foreach (['/app/sale-details?id='.$no, '/app/sale-details?id='.urlencode($no)] as $u) {
    $req = Illuminate\Http\Request::create($u, 'GET');
    $resp = $kernel->handle($req);
    echo "$u => ".$resp->getStatusCode();
    if ($resp->isRedirect()) echo ' -> '.$resp->headers->get('Location');
    echo PHP_EOL;
    $kernel->terminate($req, $resp);
}
$o = App\Models\Order::where('order_number', $no)->first();
echo "lookup by exact: ".($o ? 'found id='.$o->id : 'NOT FOUND').PHP_EOL;
echo "len=".strlen($no)." hex=".bin2hex(substr($no,0,4)).PHP_EOL;
