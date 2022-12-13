<?php

use App\{Router, Session};

require_once '../vendor/autoload.php';

date_default_timezone_set("UTC");
Session::start();
Router::handleUri();