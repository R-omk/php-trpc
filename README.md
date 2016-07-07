### USAGE

```php
<?php
use Romkart\Trpc\Server;
use Tarantool\Client\Client;
use Tarantool\Client\Connection\StreamConnection;
use Tarantool\Client\Packer\PurePacker;

require __DIR__ . '/vendor/autoload.php';
$conn = new StreamConnection('tcp://trpc:3019');
$client = new Client($conn, new PurePacker());
$trpcServer = new Server($client, 'phptest');
$trpcServer->Ever();

```

By default used `BasicCallDriver`
```php
$trpcServer->setCallDriver(new Romkart\Trpc\BasicCallDriver);
```