<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/activity/{id}', function (Request $request, Response $response, array $args) {
    $activityLoader = $this['activity.loader'];
    var_dump($activityLoader);
    $activity = $activityLoader->findById($args["id"]);
    $response = $this->renderer->render($response, 'activity_edit.phtml', ['activity' => $activity, "router" => $this->router]);
    return $response;
})->setName('activity-detail');


$app->get('/tickets', function (Request $request, Response $response) {
    $response = $this->renderer->render($response, 'fake.phtml', ['tickets' => "I guess there are tix?", "router" => $this->router]);
    return $response;
})->setName('ticket-msg');

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    $args = array_merge($args, array("router" => $this->router));
    return $this->renderer->render($response, 'index.phtml', $args);
});