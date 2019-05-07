<div class="tte-schedule">
    <?php
    $days = $EventsGrid->days();
    foreach ($days as $Day) {
        include 'header.day.php';
        include 'table.day.php';
    }
    ?>
</div>