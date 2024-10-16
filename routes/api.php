<?php

declare(strict_types=1);

use App\Controllers\MainController;
use Slim\App;

#Application routes
return function (App $app): void {
    $app->get("/{routepath}", [MainController::class, "helloWorld"]);
};
