<?php
$EventsGrid = new TabletopEvents\EventsGrid($SDK);
$days = $EventsGrid->days();
foreach ($days as $Day) {
    include '../templates/header.day.php';
    include '../templates/table.day.php';
} 