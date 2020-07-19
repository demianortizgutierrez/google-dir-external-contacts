<?php
// Autoload composer external dependencies
require __DIR__ . '/vendor/autoload.php';

// Require Google API Client library
require __DIR__ . '/classes/Google_Client.php';

// Require functions & XML parsers
require __DIR__ . '/classes/functions.php';

// Include views
include __DIR__. '/views/body.php';
include __DIR__. '/views/list.php';
include __DIR__. '/views/footer.php';