<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

// To generate credentials for the config file, run this command: htpasswd -nbBC 10 username password
$configJson = file_get_contents("../src/config.json");
$config = json_decode($configJson, true);

$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/activity",
    "secure" => false, // required if you don't have an SSL cert
    "realm" => "Protected",
    "users" => $config['users']
]));

// Show a random activity, with some attributes replaced (the home screen):

$app->get('/', function (Request $request, Response $response, array $args) {
    $activityLoader = $this['activity.loader'];
    $activity = $activityLoader->getRandomActivity(true);
    $replacements = $activityLoader->getReplacementItems(3);

    $response = $this->renderer->render($response, 'activity_random.phtml', [
        'activity' => $activity, 
        'replacements' => $replacements
    ]);
});


// Listing all activities:

$app->get('/activity/list', function (Request $request, Response $response, array $args) {
    $activityLoader = $this['activity.loader'];
    $activities = $activityLoader->getAll();
    $response = $this->renderer->render($response, 'activity_list.phtml', ['activities' => $activities, "router" => $this->router]);
    return $response;
})->setName('activity-list');


// Creating and editing activities:

$app->map(['GET', 'POST'], '/activity/{id}', function (Request $request, Response $response, array $args) {

    $activityLoader = $this['activity.loader'];

    $parsedBody = $request->getParsedBody();
    $action = null;

    if ($request->isPost()) {
        if (isset($parsedBody["actionSave"])) {
            $action = "save";
            $activityId = $activityLoader->save($args["id"], $parsedBody, $action); // id is "edit" when new
            return $response->withRedirect("/activity/$activityId?status=saved");
        } elseif (isset($parsedBody["actionDelete"])) {
            $action = "delete";
            $activityLoader->save($args["id"], $parsedBody, $action);
            return $response->withRedirect("/activity/edit?status=deleted");
        }
    }

    $qs = $request->getQueryParams();
    $status = $qs["status"] ?? "";

    if (isset($args["id"])) {
        $activity = $activityLoader->findById($args["id"], true);
    } else {
        $activity = null;
    }

    $response = $this->renderer->render(
        $response, 
        'activity_edit.phtml', 
        [
                'activity' => $activity, 
                'status' => $status,
                'submitSave' => $activityLoader->randomButtonLabel("save"),
                'submitDelete' => $activityLoader->randomButtonLabel("delete"),
                "router" => $this->router
        ]
    );
    return $response;
})->setName('activity-detail');

// // default view here
// $app->get('/[{name}]', function (Request $request, Response $response, array $args) {
//     // Sample log message
//     // $this->logger->info("Slim-Skeleton '/' route");

//     // Render index view
//     $args = array_merge($args, array("router" => $this->router));
//     return $this->renderer->render($response, 'index.phtml', $args);
// });
