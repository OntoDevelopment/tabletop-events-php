<?php
require '../vendor/autoload.php';
require '../src/SDK.php';
require '../src/Grid.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Test</title>
    </head>
    <body>
        <pre><?php
            $TabletopEvents = new TabletopEvents\SDK('0781F722-6C9C-11E9-BE08-996502F0A829');
            $Grid = new TabletopEvents\Grid($TabletopEvents);
            $days = $Grid->days();
            //print_r($days);
            ?></pre>
        <?php foreach ($days as $Day) { ?>
            <h2 class="tte-header tte-day-info">
                <span class="tte-day-name"><?= $Day->name ?></span> - <span class="tte-day-date"><?= date('M, jS Y', strtotime($Day->start_date)) ?></span>
            </h2>
            <table border="1" class="tte-grid" id="table-<?= $Day->id ?>">
                <thead>
                    <tr>
                        <th class="tte-grid-header tte-grid-header-blank"></th>
                        <?php foreach ($Day->parts as $Part) { ?>
                            <th class="tte-grid-header tte-daypart-header" id="<?= $Part->id ?>">
                                <?= substr($Part->name, strpos($Part->name, ' at ') + 4) ?>
                            </th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($Day->rooms as $Room) { ?>
                        <tr id="<?= $Room->id ?>">
                            <td class="tte-section-header-room" colspan="<?= $Day->parts_count + 1 ?>">
                                <?= $Room->name ?>
                            </td>
                        </tr>
                        <?php foreach ($Room->spaces as $Space) { ?>
                            <tr class="tte-space">
                                <td class="tte-space-name"><?= $Space->name ?></td>
                                <?php foreach ($Space->slots as $Slot) { ?>
                                    <td class="tte-space-slot <?= $Slot->Event ? 'tte-event-name' : 'tte-event-blank' ?>" colspan="<?= $Slot->colspan ?>" id="<?= $Slot->id ?>">
                                        <?= ($Slot->Event) ? $Slot->Event->name : '' ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>

        <pre></pre>
    </body>
</html>
