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
        <?php
        $SDK = new TabletopEvents\SDK($convention_id);
        include '../templates/schedule.php';
        ?>
    </body>
</html>
