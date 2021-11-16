<?php
require_once 'config.php';
require_once 'includes/savemaintainer.php'

?>
    <!DOCTYPE html>
    <html lang="en">
    <?php
    $PageTitle = "RailroadsOnlineMapper";
    include_once(SHELL_ROOT . 'includes/head.php');

    // Create required folders if they don't exist
    $folders = array("saves", "saves/public", "uploads");
    foreach ($folders as $folder) {
        if (!file_exists($folder)) {
            mkdir($folder);
        }
    }

    // Create counter if it doesn't exist
    if (!file_exists('counter')) {
        file_put_contents('counter', 0);
    }

    $tableHeader = '<thead>
                        <th style="background-color: beige">                                                        <img height="28" width="40" src="images/player.svg"></th>
                        <th style="background-color: beige"><A href="?sortby=0&sortorder=desc" style="color: white"><img height="28" width="40" src="images/distance.svg"></A></th>
                        <th style="background-color: beige"><A href="?sortby=1&sortorder=desc" style="color: white"><img height="28" width="40" src="images/switch.svg"></A></th>
                        <th style="background-color: beige"><A href="?sortby=6&sortorder=desc" style="color: white"><img height="28" width="40" src="images/tree.svg"></A></th>
                        <th style="background-color: beige"><A href="?sortby=2&sortorder=desc" style="color: white"><img height="28" width="40" src="images/loco.svg"></A></th>
                        <th style="background-color: beige"><A href="?sortby=3&sortorder=desc" style="color: white"><img height="28" width="40" src="images/cart.svg"></A></th>
                        <th style="background-color: beige"><A href="?sortby=4&sortorder=desc" style="color: white"><img height="28" width="40" src="images/slope.svg"></A></th>
                    </thead>';

    function mysort($a, $b)
    {
        global $db;
        $x = 1;
        if (strtolower($db[substr($a, 0, -5) . '.sav'][$_GET['sortby']]) == strtolower($db[substr($b, 0, -5) . '.sav'][$_GET['sortby']])) {
            return 0;
        }
        if (strtolower($db[substr($a, 0, -5) . '.sav'][$_GET['sortby']]) > strtolower($db[substr($b, 0, -5) . '.sav'][$_GET['sortby']])) {
            $x = -1;
        } else {
            $x = 1;
        }
        if ($_GET['sortorder'] == 'desc') {
            return $x;
        } else {
            return -$x;
        }
    }


    ?>
    <body>
    <header class="header">
        <h1 class="logo">RailroadsOnlineMapper</h1>
        <a class="button" href="upload.php">Upload Savegame</a>
    </header>
    <main>
        <section class="uploads">
            <h2>Latest uploads</h2>
            <div class="uploads__tables">
                <table>
                    <?php
                    echo $tableHeader;
                    $dh = opendir(SHELL_ROOT . 'maps/');
                    while ($file = readdir($dh)) {
                        if (substr($file, -5) == '.html') {
                            $files[filemtime(SHELL_ROOT . 'maps/' . $file)] = $file;
                        }
                    }

                    if ((isset($files) && $files != null) && file_exists(SHELL_ROOT.'db.db')) {
                        $db = unserialize(file_get_contents(SHELL_ROOT.'db.db'));
                        //array($totalTrackLength, $totalSwitches, $totalLocos, $totalCarts, $maxSlope);

                        if (!isset($_GET['sortby']) || !isset($_GET['sortorder'])) {
                            krsort($files);
                        } else {
                            usort($files, 'mysort');
                        }

                        $hard_limit = 1600;
                        $soft_limit = 800;
                        $dbkeys = array_keys($db);

                        for ($i = 0; $i < sizeof($files); $i++) {

                            $file = $files[array_keys($files)[$i]];

                            if (!$file) {
                                echo 'BRESK';
                                break;
                            }

                            if ($i > $hard_limit) {
                                unlink(SHELL_ROOT . "maps/" . substr($file, 5, -5) . ".html");
                            }

                            if ($i >= $soft_limit) {
                                continue;
                            }

                            // Create savefile name from map file
                            $saveFile = substr($file, 0, -5) .'.sav';

                            $results = findFiles(SHELL_ROOT.'saves', $saveFile); // Locating oldest file in the saves/ folder
                            arsort($results); // Sort in reverse order, preserving the array keys, which are the filenames. The oldest file should be at the bottom.

                            // Get the oldest file (the last array key)
                            $filenames = array_keys($results);
                            $saves = count($filenames);
                            $oldestFile = end($filenames);

                            // Checks count of files and if there is only 1 do not remove it!!!
                            if($saves < 2 ) {
                              echo "";
                            } else {
                              @unlink($oldestFile);
                            }

                            // Check to see if savefile exists in public folder and create download link
                            if (file_exists(SHELL_ROOT.'saves/public/' . $saveFile)) {

                              $uploaded = filemtime(SHELL_ROOT.'saves/public/'.$saveFile); // Checks the timestamp of saves in the public folder
                              $timediff = time() - $uploaded; // Measure difference between current time and save file creation time

                                // Timecheck to remove public link for download
                                if ($timediff < (60 * 60 * 24 * 2)) { // two days
                                    echo '<td><a href="'.WWW_ROOT.'maps/' .$file. '">'.substr($file, 0, -5) .'</a> <a href="'.WWW_ROOT.'saves/public/'.$saveFile.'">(DL)</a></td>';
                                } else {
                                    echo '<td><a href="'.WWW_ROOT.'maps/' .$file. '">'.substr($file, 0, -5) .'</a></td>';
                                }

                            } else {
                              echo '<td><a href="'.WWW_ROOT.'maps/' .$file.'"</a>'.substr($file, 0, -5).'</td>';
                            }
                            echo '
                                <!-- Track Length -->
                                <td>' . round($db[substr($file, 0, -5) . '.sav'][0] / 100000, 2) . 'km</td>

                                <!-- Switch Count -->
                                <td>' . $db[substr($file, 0, -5) . '.sav'][1] . '</td>

                                <!-- Tree Death Count -->
                                <td>' . $db[substr($file, 0, -5) . '.sav'][6] . '</td>

                                <!-- Locos -->
                                <td>' . $db[substr($file, 0, -5) . '.sav'][2] . '</td>

                                <!-- Carts -->
                                <td>' . $db[substr($file, 0, -5) . '.sav'][3] . '</td>

                                <!-- Max Slope -->
                                <td>' . round($db[substr($file, 0, -5) . '.sav'][4]) . '%</td>';

                            echo '</tr>';
                            if (!(($i + 1) % 15)) {
                                if (($i + 1) < $soft_limit) {
                                    echo '</table><table>' . $tableHeader;
                                }
                            }
                        }
                    }
                    echo '</table>';
                    ?>
                </table>
            </div>
        </section>
    </main>
    <?php include_once(SHELL_ROOT . 'includes/footer.php') ?>
    </body>
    </html>
<?php
$dir = SHELL_ROOT . 'saves';
$dh = opendir($dir);

while ($file = readdir($dh)) {
    if ($file && (substr($file, -4) == '.sav' || substr($file, -13) == '.sav')) {
        if (filemtime($dir . '/' . $file) < time() - 600) {
            unlink($dir . '/' . $file);
        }
    }
}
