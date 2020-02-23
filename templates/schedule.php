<div class="tte-schedule">
    <?php
    foreach ($EventsGrid->days as $Day) {
        include 'header.day.php';
        include 'table.day.php';
    }
    ?>
</div>