<?php
require '../vendor/autoload.php';
require '../src/autoload.php';
require '../../tabletop-events-php.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Test</title>
    </head>
    <body>
        <pre><?php
        $SDK = new TabletopEvents\SDK($convention_id);
        print_r($SDK->public->getLibraryGames(''));
        ?></pre>
    </body>
</html>
