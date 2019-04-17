<?php

ini_set('display_errors', 1);
ini_set("session.cookie_httponly", 1);

session_start();

require_once getenv('WEBAPP_BASEDIR').'/classes/Configuration.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/Runtime.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/Configuration.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/Auth.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/properties/BaseProperty.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/properties/IntegerProperty.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/properties/StringProperty.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/properties/TextProperty.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/Request.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/Router.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/Storage.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/View.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/controllers/Get.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/controllers/Post.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/models/Posts.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/models/Users.php';
require_once getenv('WEBAPP_BASEDIR').'/classes/Fail2Ban.php';

$systemInstance = new SecureGuestbook\Runtime();

$systemInstance->processRequest();
