<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

// Show a random activity, with some attributes replaced:
$app->get('/', function (Request $request, Response $response, array $args) {
    $activityLoader = $this['activity.loader'];
    $activity = $activityLoader->getRandomActivity(true);
    $replacements = $activityLoader->getReplacementItems(3);

    $response = $this->renderer->render($response, 'activity_random.phtml', [
        'activity' => $activity, 
        'replacements' => $replacements
    ]);
});

$app->get('/activity/list', function (Request $request, Response $response, array $args) {
    $activityLoader = $this['activity.loader'];
    $activities = $activityLoader->getAll();
    $response = $this->renderer->render($response, 'activity_list.phtml', ['activities' => $activities, "router" => $this->router]);
    return $response;
})->setName('activity-list');


$app->get('/activity/{id}', function (Request $request, Response $response, array $args) {
    $activityLoader = $this['activity.loader'];
    $activity = $activityLoader->findById($args["id"], true);
    $response = $this->renderer->render($response, 'activity_edit.phtml', ['activity' => $activity, "router" => $this->router]);
    return $response;
})->setName('activity-detail');


$app->get('/tickets', function (Request $request, Response $response) {
    $response = $this->renderer->render($response, 'fake.phtml', ['tickets' => "I guess there are tix?", "router" => $this->router]);
    return $response;
})->setName('ticket-msg');

// // default view here
// $app->get('/[{name}]', function (Request $request, Response $response, array $args) {
//     // Sample log message
//     // $this->logger->info("Slim-Skeleton '/' route");

//     // Render index view
//     $args = array_merge($args, array("router" => $this->router));
//     return $this->renderer->render($response, 'index.phtml', $args);
// });