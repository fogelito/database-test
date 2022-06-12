<?php
require_once __DIR__ . '/vendor/autoload.php';

use Utopia\App;
use Utopia\Database\Validator\Key;
use Utopia\Registry\Registry;
use Utopia\Request;
use Utopia\Response;
use Utopia\Database\Database;
use Utopia\Cache\Cache;
use Utopia\Cache\Adapter\None as NoCache;
use Utopia\Database\Adapter\MySQL;
use Utopia\Validator\Text;


App::get('/health')
    ->inject('request')
    ->inject('response')
    ->action(
        function (Request $request, Response $response) {
            $response
                ->addHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->addHeader('Expires', '0')
                ->addHeader('Pragma', 'no-cache')
                ->setStatusCode()
                ->send("ok");
        }
    );




App::get('/collection/get')
    ->inject('request')
    ->inject('response')
    ->inject('db')
    ->action(
        function (Request $request, Response $response, Database $db) {
            $collection = $db->getCollection('shmuel');

            $response
                ->addHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->addHeader('Expires', '0')
                ->addHeader('Pragma', 'no-cache')
                ->setStatusCode(201)
                ->json([$collection]);
        }
    );


App::get('/attribute/create')
    ->inject('request')
    ->inject('response')
    ->inject('db')
    ->param('collection-id', null, new Key(), "collection-id", false)
    ->param('attribute-name', null, new Text(50), "attribute-id", false)
    ->action(
        function ($collectionId, $attributeName, Request $request, Response $response, Database $db) {
            $collection = $db->getCollection($collectionId);
            if($collection->isEmpty()){
                throw new Exception('collection not found');
            }

            $result = $db->createAttribute($collection->getId(), $attributeName, "string", 21, true);

            $response
                ->addHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->addHeader('Expires', '0')
                ->addHeader('Pragma', 'no-cache')
                ->setStatusCode(201)
                ->json(['$result' => $result, $collection]);
        }
    );



App::get('/collection/create')
    ->inject('request')
    ->inject('response')
    ->inject('db')
    ->param('name', null, new Text(50, ), "create collection", false)
    ->action(
        function ($name, Request $request, Response $response, Database $db) {
            $collection = $db->getCollection($name);
            if($collection->isEmpty()){
                $collection = $db->createCollection($name,[],[]);
            }
            else {
                throw new Exception('collection exist', 404);
            }

            $response
                ->addHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->addHeader('Expires', '0')
                ->addHeader('Pragma', 'no-cache')
                ->setStatusCode(201)
                ->json([$collection]);
        }
    );


App::error(function (Exception $error, $request, $response) {
    $response->text($error);
    //$response->json($error->getTrace());
}, ['error', 'request', 'response']);


App::setMode(App::MODE_TYPE_PRODUCTION); // Define Mode

            $dbHost = 'mysql';
$dbPort = '3306';
$dbUser = 'root';
$dbPass = 'password';

$pdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass, [
    PDO::ATTR_TIMEOUT => 3, // Seconds
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => true,
    PDO::ATTR_STRINGIFY_FETCHES => true
]);

$app        = new App('America/New_York');
$request    = new Request();
$response   = new Response();


App::setResource('db', function() use($pdo) {
    $database = new Database(new MySQL($pdo), new Cache(new NoCache()));
    $database->setNamespace("ns");
    $database->create('foo'); // Creates a new schema named `mydb`
    return $database;
});

$app->run($request, $response);



