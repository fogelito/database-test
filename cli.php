<?php
require_once './vendor/autoload.php';

use Utopia\CLI\CLI;
use Utopia\CLI\Console;
use Utopia\Database\Document;
use Utopia\Registry\Registry;
use Utopia\Validator\Wildcard;
use Utopia\Database\Database;
use Utopia\Cache\Cache;
use Utopia\Cache\Adapter\None as NoCache;
use Utopia\Database\Adapter\MySQL;

$register = new Registry();

$register->set('db', function () {
    $dbHost = '127.0.0.1';
    $dbPort = '33062';
    $dbUser = 'root';
    $dbPass = 'password';

    return new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_TIMEOUT => 3, // Seconds
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_STRINGIFY_FETCHES => true
    ]);

});

$cli = new CLI();

$cli
    ->task('task1')
    ->param('email', null, new Wildcard())
    ->action(function ($email) {
        Console::success($email);
        Console::log('Plain Log'); // stdout
        Console::success('Green log message'); // stdout
        Console::info('Blue log message'); // stdout
        Console::warning('Yellow log message'); // stderr
        Console::error('Red log message'); // stderr
    });



$cli
    ->task('load')
    ->action(function () use($register) {
        Console::success('start'); // stdout
//
//        $stdout = '';
//        $stderr = '';
//        $stdin = '123';
//        $timeout = 3; // seconds
//        $code = Console::execute('>&1 echo "success"', $stdin, $stdout, $stderr, $timeout);
//
//        Console::log('$code=' . $code); // '0'
//        Console::log('$stdout=' . $stdout); // 'success'
//        Console::log('$stderr=' . $stderr); // ''
//
//        Console::loop(function() {echo "Hello World\n";}, 10.1 );
//


        $db = new Database(new MySQL($register->get('db')), new Cache(new NoCache()));
        $db->setNamespace("ns");
        $db->create('foo2');

        $collection = $db->getCollection('shmuel');
        if($collection->isEmpty()){
            $collection = $db->createCollection('shmuel',[],[]);
        }

        try {
            $db->createAttribute($collection->getId(), "title", "string", 100, true);
        }
        catch (Exception $e){
        }

        Console::loop(function() use ($collection, $db)   {
            Console::success('insert start ');

            $doc = $db->createDocument($collection->getId(), new Document([
                'title' => "this is a title ", //. rand(9999999999, 99999999999),
                '$write' => ['role:all'],
                '$read' => ['role:all']
            ]));

            var_dump($doc);

        }, 0.0000 );

    });

$cli->run();


