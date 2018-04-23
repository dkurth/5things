<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

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

    $action = null;
    $postBody = null;
    $status = null;
    if ($request->isPost()) {
        $postBody = $request->getParsedBody();
        if (isset($postBody["actionSave"])) {
            $action = "save";
            $status = "saved"; // todo - at least pretend to handle errors
            $activity = $activityLoader->save($args, $postBody, $action);

            // TODO - redirect to the URL with the activity id

            //$response->redirect($app->urlFor('activity-detail', $activity->id), 303);
        } elseif (isset($postBody["actionDelete"])) {
            $action = "delete";
            $status = "deleted"; // todo 

            // TODO - redirect to the /edit page

            //$response->redirect($app->urlFor('activity-detail'), 303);
        }
    }

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
