<?php

/**
 * Class GVASParser
 */
class GVASParser
{

    var $position = 0;
    var $x = '';
    var $goldenBucket = array();
    var $NEWUPLOADEDFILE;
    var $saveObject = array();

    /**
     * @param $x
     * @param bool $againAllowed
     * @return false|string
     */
    public function parseData($x, $againAllowed = true)
    {
        $this->x = $x;
        $this->goldenBucket = array();
        $this->position = 0;

        $myHeader = new dtHeader();
        $this->position = $myHeader->unserialize($this->x, $this->position);
        $headerTotal = substr($this->x, 0, $this->position);
        $myHeader->content = $headerTotal;

        $this->saveObject['objects'][] = $myHeader;

        $myString = new dtString();
        $results = $myString->unserialize($x, $this->position);
//        $string = $results[0];
        $this->position = $results[1];
        $this->saveObject['objects'][] = $myString;

        while ($this->position < strlen($x)) {
            $myProperty = new dtProperty();
            $results = $myProperty->unserialize($this->x, $this->position);
            if ($results['0'] != 'EOF') {

                $original = substr($this->x, $this->position, $results[1] - $this->position);
                $test = $myProperty->serialize();
                if ($original != $test) {
                    file_put_contents('tmp_' . trim($myProperty->NAME), $original);
                    file_put_contents('tmp_' . trim($myProperty->NAME) . '.test', $test);
                }

                $this->saveObject['objects'][] = $myProperty;
                $this->position = $results[1];
                $resultRows = $results[0];
                foreach ($resultRows as $row) {
                    $pieces = $row[0];
                    $something = $row[1];
                    if (is_array($something) && sizeof($something) == 2) {
                        $something = $something[0];
                    }
                    $code = '$this->goldenBucket[\'' . implode("']['", $pieces) . '\'][]=$something;';
                    eval($code);
                }
            } else {
                break;
            }
        }


        $silverPlate = array();

        $keys = array('Player', 'Freight', 'Compressor', 'Tender', 'Coupler', 'Boiler', 'Headlight', 'Frame', 'Watertower', 'Switch');
        foreach ($keys as $key) {
            $silverPlate[$key . 's'] = array();
            if (isset($this->goldenBucket[$key])) {
                foreach ($this->goldenBucket[$key] as $index => $value) {
                    foreach ($value as $idx => $v) {
                        $silverPlate[$key . 's'][$idx][$index] = $v;
                    }
                }
                unset($this->goldenBucket[$key]);
                $this->goldenBucket[$key . 's'] = $silverPlate[$key . 's'];
            }
        }

        foreach ($this->goldenBucket['Frames'] as $i => $frame) {
            if (isset($this->goldenBucket['Boilers'][$i]))
                $this->goldenBucket['Frames'][$i]['Boiler'] = $this->goldenBucket['Boilers'][$i];

            if (isset($this->goldenBucket['Headlights'][$i]))
                $this->goldenBucket['Frames'][$i]['Headlights'] = $this->goldenBucket['Headlights'][$i];

            if (isset($this->goldenBucket['Freights'][$i]))
                $this->goldenBucket['Frames'][$i]['Freight'] = $this->goldenBucket['Freights'][$i];

            if (isset($this->goldenBucket['Compressors'][$i]))
                $this->goldenBucket['Frames'][$i]['Compressor'] = $this->goldenBucket['Compressors'][$i];

            if (isset($this->goldenBucket['Tenders'][$i]))
                $this->goldenBucket['Frames'][$i]['Tender'] = $this->goldenBucket['Tenders'][$i];

            if (isset($this->goldenBucket['Couplers'][$i]))
                $this->goldenBucket['Frames'][$i]['Coupler'] = $this->goldenBucket['Couplers'][$i];

            if (isset($this->goldenBucket['Regulatorvalue'][$i]))
                $this->goldenBucket['Frames'][$i]['Regulator'] = $this->goldenBucket['Regulatorvalue'][$i];

            if (isset($this->goldenBucket['Brakevalue'][$i]))
                $this->goldenBucket['Frames'][$i]['Brake'] = $this->goldenBucket['Brakevalue'][$i];

            if (isset($this->goldenBucket['Reverservalue'][$i]))
                $this->goldenBucket['Frames'][$i]['Reverser'] = $this->goldenBucket['Reverservalue'][$i];

            if (isset($this->goldenBucket['Reverservalue'][$i]))
                $this->goldenBucket['Frames'][$i]['Smokestack'] = $this->goldenBucket['Smokestacktype'][$i];

            if (isset($this->goldenBucket['Reverservalue'][$i]))
                $this->goldenBucket['Frames'][$i]['Generatorvalvevalue'] = $this->goldenBucket['Generatorvalvevalue'][$i];

            if (isset($this->goldenBucket['Marker'][$i]))
                $this->goldenBucket['Frames'][$i]['Marker']['Front']['Right'] = $this->goldenBucket['Marker']['Lights']['Front']['Right'][$i];

            if (isset($this->goldenBucket['Marker'][$i]))
                $this->goldenBucket['Frames'][$i]['Marker']['Front']['Left'] = $this->goldenBucket['Marker']['Lights']['Front']['Left'][$i];

            if (isset($this->goldenBucket['Marker'][$i]))
                $this->goldenBucket['Frames'][$i]['Marker']['Rear']['Right'] = $this->goldenBucket['Marker']['Lights']['Rear']['Right'][$i];

            if (isset($this->goldenBucket['Marker'][$i]))
                $this->goldenBucket['Frames'][$i]['Marker']['Rear']['Left'] = $this->goldenBucket['Marker']['Lights']['Rear']['Left'][$i];
        }

        unset($this->goldenBucket['Headlights']);
        unset($this->goldenBucket['Boilers']);
        unset($this->goldenBucket['Freights']);
        unset($this->goldenBucket['Compressors']);
        unset($this->goldenBucket['Tenders']);
        unset($this->goldenBucket['Couplers']);
        unset($this->goldenBucket['Regulatorvalue']);
        unset($this->goldenBucket['Brakevalue']);
        unset($this->goldenBucket['Reverservalue']);
        unset($this->goldenBucket['Smokestacktype']);
        unset($this->goldenBucket['Generatorvalvevalue']);
        unset($this->goldenBucket['Marker']);

        for ($i = 0; $i < sizeof($this->goldenBucket['Industry']['Type']); $i++) {
            $this->goldenBucket['Industries'][] = array(
                'Type' => $this->goldenBucket['Industry']['Type'][$i],
                'Location' => $this->goldenBucket['Industry']['Location'][$i],
                'Rotation' => $this->goldenBucket['Industry']['Rotation'][$i],
                'EductsStored' => array(
                    $this->goldenBucket['Industry']['Storage']['Educt1'][$i],
                    $this->goldenBucket['Industry']['Storage']['Educt2'][$i],
                    $this->goldenBucket['Industry']['Storage']['Educt3'][$i],
                    $this->goldenBucket['Industry']['Storage']['Educt4'][$i],),
                'ProductsStored' => array(
                    $this->goldenBucket['Industry']['Storage']['Product1'][$i],
                    $this->goldenBucket['Industry']['Storage']['Product2'][$i],
                    $this->goldenBucket['Industry']['Storage']['Product3'][$i],
                    $this->goldenBucket['Industry']['Storage']['Product4'][$i],
                )
            );
        }


        for ($i = 0; $i < sizeof($this->goldenBucket['Spline']['Points']['Index']['Start']); $i++) {
            $locArr = $this->goldenBucket['Spline']['Location'][$i];

            $spline = array(
                'Location' => array(
                    'X' => $locArr[0],
                    'Y' => $locArr[1],
                    'Z' => $locArr[2]
                ),
                'Type' => $this->goldenBucket['Spline']['Type'][$i]
            );

            $segmentArray = array();
            $startPos = $this->goldenBucket['Spline']['Points']['Index']['Start'][$i];
            $endPos = $this->goldenBucket['Spline']['Points']['Index']['End'][$i];

            while ($startPos < $endPos) {

                $startLocs = $this->goldenBucket['Spline']['Points']['Arr'][$startPos];
                $endLocs = $this->goldenBucket['Spline']['Points']['Arr'][$startPos + 1];

                $segmentArray[] = array(
                    'LocationStart' => array(
                        'X' => $startLocs[0],
                        'Y' => $startLocs[1],
                        'Z' => $startLocs[2]
                    ),
                    'LocationEnd' => array(
                        'X' => $endLocs[0],
                        'Y' => $endLocs[1],
                        'Z' => $endLocs[2]
                    ),
                    'LocationCenter' => array(
                        'X' => $startLocs[0] + ($endLocs[0] - $startLocs[0]) / 2,
                        'Y' => $startLocs[1] + ($endLocs[1] - $startLocs[1]) / 2,
                        'Z' => $startLocs[2] + ($endLocs[2] - $startLocs[2]) / 2
                    ),
                    'Visible' => array_shift($this->goldenBucket['Spline']['Segments']['Visibility']),

                );
                $startPos++;

                $cX = floor((200000 + $segmentArray[sizeof($segmentArray) - 1]['LocationCenter']['X']) / 100000);
                $cY = floor((200000 + $segmentArray[sizeof($segmentArray) - 1]['LocationCenter']['Y']) / 100000);
                $this->goldenBucket['Segments'][$cX][$cY][] = $segmentArray[sizeof($segmentArray) - 1];

                $sX = floor((200000 + $segmentArray[sizeof($segmentArray) - 1]['LocationCenter']['X']) / 100000);
                $sY = floor((200000 + $segmentArray[sizeof($segmentArray) - 1]['LocationCenter']['Y']) / 100000);
                if ($sX != $cX || $sY != $cY) {
                    $this->goldenBucket['Segments'][$sX][$sY][] = $segmentArray[sizeof($segmentArray) - 1];
                }

                $eX = floor((200000 + $segmentArray[sizeof($segmentArray) - 1]['LocationCenter']['X']) / 100000);
                $eY = floor((200000 + $segmentArray[sizeof($segmentArray) - 1]['LocationCenter']['Y']) / 100000);
                if ($eX != $cX || $eY != $cY) {
                    $this->goldenBucket['Segments'][$eX][$eY][] = $segmentArray[sizeof($segmentArray) - 1];
                }
            }

            $spline['Segments'] = $segmentArray;
            $this->goldenBucket['Splines'][] = $spline;
        }

        if (isset($this->goldenBucket['Turntable'])) {
            for ($i = 0; $i < sizeof($this->goldenBucket['Turntable']['Type']); $i++) {
                $this->goldenBucket['Turntables'][] = array(
                    'Type' => $this->goldenBucket['Turntable']['Type'][$i],
                    'Location' => $this->goldenBucket['Turntable']['Location'][$i],
                    'Rotator' => $this->goldenBucket['Turntable']['Rotator'][$i],
                    'Deck' => $this->goldenBucket['Turntable']['Deck']['Rotation'][$i]
                );
            }
        }
        unset($this->goldenBucket['Turntable']);
        unset($this->goldenBucket['Spline']);
        unset($this->goldenBucket['Save']);
        unset($this->goldenBucket['Industry']);

        /**
         * HANDLE DATA MANIPULATION AND SAVE FILE
         */

        $tmp = $this->handleEditAndSave($againAllowed);
        if ($tmp == 'AGAIN') {
            return $tmp;
        }

        $this->goldenBucket = $this->convert_from_latin1_to_utf8_recursively($this->goldenBucket);

        $json = json_encode($this->goldenBucket, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
//echo json_last_error_msg();
        file_put_contents('xx.json', $json);
        return $json;

    }


    /**
     * Encode array from latin1 to utf8 recursively
     * @param $dat
     * @return array|string
     */
    public function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[$i] = self::convert_from_latin1_to_utf8_recursively($d);

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

            return $dat;
        } else {
            return $dat;
        }
    }


    /**
     * @param $a
     * @param $b
     * @return float
     */
    function distance($a, $b)
    {

        return sqrt(pow($a[0] - $b['X'], 2) + pow($a[1] - $b['Y'], 2));
    }

    /**
     * @param $againAllowed
     * @return string
     */
    function handleEditAndSave($againAllowed)
    {
        $output = '';
        foreach ($this->saveObject['objects'] as $saveObjectIndex => $object) {
            if (is_object($object)) {
                if (trim($object->NAME) == 'RemovedVegetationAssetsArray' && isset($_POST['replant']) && $_POST['replant'] == 'YES') {
                    $v = 0;
                    $toRemove = array();
                    foreach ($object->CONTENTOBJECTS[3]->contentElements as $index => $vector) {
                        $v++;
                        // found a new fallen tree
                        if ($v < 1825) continue;
                        $treeX = floor((200000 + $vector->content[0]) / 100000);
                        $treeY = floor((200000 + $vector->content[1]) / 100000);

                        $minDistanceToSomething = 80000000;
//                        foreach ($this->goldenBucket['Splines'] as $spline) {
                        if (isset($this->goldenBucket['Segments'][$treeX][$treeY])) {
//                            foreach ($spline['Segments'] as $segment) {
                            foreach ($this->goldenBucket['Segments'][$treeX][$treeY] as $segment) {
                                if ($segment['LocationCenter']['X'] < $vector->content[0] - 6000) {
                                    continue;
                                }
                                if ($segment['LocationCenter']['X'] > $vector->content[0] + 6000) {
                                    continue;
                                }
                                if ($segment['LocationCenter']['Y'] < $vector->content[1] - 6000) {
                                    continue;
                                }
                                if ($segment['LocationCenter']['Y'] > $vector->content[1] + 6000) {
                                    continue;
                                }
                                $minDistanceToSomething = min($minDistanceToSomething, $this->distance($vector->content, $segment['LocationCenter']));
                            }
                        }
                        if ($minDistanceToSomething > 2000) {
                            $toRemove[] = $index;
                        }
                        //echo round($minDistanceToSomething)." ";
                    }
                    foreach ($toRemove as $tri) {
                        if (isset($_POST['replant']) && $_POST['replant'] == 'YES') {
                            unset($object->CONTENTOBJECTS[3]->contentElements[$tri]);
                        } else {
                            $this->goldenBucket['Removed']['Vegetation'][$tri]['replant'] = true;
                        }

                    }
                    $object->CONTENTOBJECTS[3]->COUNTER->value = (sizeof($object->CONTENTOBJECTS[3]->contentElements));
//                    echo "NEW VALUE = " . (sizeof($object->CONTENTOBJECTS[3]->contentElements));
                }

                if( false && trim($object->NAME) == 'FrameNumberArray'){
                    foreach($object->CONTENTOBJECTS[3]->contentElements as $index=>$textProp){
                        if(isset($_POST['number_'.$index]) && trim($_POST['number_'.$index])){
                            $x=2;
                            if(!isset($object->CONTENTOBJECTS[3]->contentElements[$index]->lines[0])){
                                $string = new dtString();
                                $string->nullBytes = 1;
                                $object->CONTENTOBJECTS[3]->contentElements[$index]->addLine($string);
                            }
                            $object->CONTENTOBJECTS[3]->contentElements[$index]->lines[0]->string = trim($_POST['number_'.$index]);
                            // terminator = 2
                            $object->CONTENTOBJECTS[3]->contentElements[$index]->terminator = pack('C', 2);
                            // second 4 = 1
                            $object->CONTENTOBJECTS[3]->contentElements[$index]->secondFour = pack('i', 1);
                        }
                    }
                }

                $output .= $object->serialize();
            } else {
                die('WHOPSI');
            }
        }
        $output .= hex2bin('050000004e6f6e650000000000');

        if (isset($_POST['save'])) {
            $db = unserialize(file_get_contents('db.db'));
            if (getUserIpAddr() != $db[$this->NEWUPLOADEDFILE][5]) {
                die("This does not seem to be your save file.");
            }
            echo "SAVING FILE " . $this->NEWUPLOADEDFILE . '.modified' . "<br>\n";
            file_put_contents('saves/' . $this->NEWUPLOADEDFILE . '.modified', $output, FILE_BINARY);
            echo '<A href="saves/' . $this->NEWUPLOADEDFILE . '.modified' . '">Download your modified save here </A><br>';
            echo 'Want to upload this map again?<A href="upload.php">Add your renumbered save again</A><br>';
        } else {
            file_put_contents('uploads/' . $this->NEWUPLOADEDFILE, $output, FILE_BINARY);
        }

    }
}