<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
require_once './functions.php';
$token = "";

$params = json_decode(file_get_contents('php://input'), TRUE);
file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . file_get_contents('php://input') . PHP_EOL, FILE_APPEND);
$result = $params['result'];
$param = $result['parameters'];
$to = $param['to'];
$from = $param['from'];
/////////// HARD CODE BENTROK DENGAN API AI ///////// 
if ($to == 'BDO') {
    $to = 'bandung';
}
if ($from == 'BDO') {
    $from = 'bandung';
}
if ($to == 'JOG') {
    $to = 'yogyakarta';
}
if ($from == 'JOG') {
    $from = 'yogyakarta';
}
/////////// HARD CODE BENTROK DENGAN API AI ///////// 
$rangetimedayshiftarrival = $param['rangetimedayshiftarrival'];
$jenistransportasi = $param['jenistransportasi'];
$dt = new DateTime();
if ($rangetimedayshiftarrival == 'besok') {
    $dt->add(new DateInterval('P1D')); //tambah 1 hari
} else if ($rangetimedayshiftarrival == 'lusa') {
    $dt->add(new DateInterval('P2D')); //tambah 2 hari
} else {
    if (is_nan($rangetimedayshiftarrival)) {
        $dt = new DateTime();
    } else {
        $now = date('d');
        $add = $rangetimedayshiftarrival - $now;
        date_add($dt, date_interval_create_from_date_string($add . ' days'));
    }
}
$date = $dt->format('Y-m-d');
if ($jenistransportasi == 'pesawat') {
    $results[0] = search_flight($token, $from, $to, $date);
    $output = '';
    $idx = 0;
    if ($results && count($results) > 0) {
        foreach ($results as $i => $r) {
            if (is_array($r)) {
                foreach ($r as $key => $row) {
                    if (is_array($row)) {
                        foreach ($row as $k => $v) {
                            if ($idx < 7) {
                                $output .= 'rute : ' . $v['departure_city_name'] . " ke " . $v['arrival_city_name'] . PHP_EOL;
                                $output .= 'airlines : ' . $v['airlines_name'] . PHP_EOL;
                                $output .= 'harga : ' . rp(intval($v['price_value'])) . PHP_EOL;
                                $output .= 'berangkat : ' . $v['departure_flight_date_str'] . " " . $v['simple_departure_time'] . "-" . $v['simple_arrival_time'] . PHP_EOL;
                                $output .= PHP_EOL;
                            }
                            $idx++;
                        }
                    }
                }
            }
        }
    } else {
        $output = 'tidak ada jadwal';
    }
} else {
    //kereta commuterline
    try {
        $db = new PDO('mysql:host=127.0.0.1;dbname=tanya', 'root', 'password');
        if ($db) {
            //from
            $query = "SELECT station_code FROM station WHERE station_name = '$from'";
            file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $query . PHP_EOL, FILE_APPEND);
            $stmt = $db->query($query) or die(file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $db->errorInfo() . PHP_EOL, FILE_APPEND));
            $dep = '';
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $dep = $row['station_code'];
            }
            if ($dep == '') {
                $query = "SELECT station_code FROM station WHERE city_name = '$from'";
                file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $query . PHP_EOL, FILE_APPEND);
                $stmt = $db->query($query) or die(file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $db->errorInfo() . PHP_EOL, FILE_APPEND));
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $dep = $row['station_code'];
                }
            }
            file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . 'dep = ' . $dep . PHP_EOL, FILE_APPEND);
            //to
            $query = "SELECT station_code FROM station WHERE station_name = '$to'";
            file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $query . PHP_EOL, FILE_APPEND);
            $stmt = $db->query($query) or die(file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $db->errorInfo() . PHP_EOL, FILE_APPEND));
            $arr = '';
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $arr = $row['station_code'];
            }
            if ($arr == '') {
                $query = "SELECT station_code FROM station WHERE city_name = '$to'";
                file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $query . PHP_EOL, FILE_APPEND);
                $stmt = $db->query($query) or die(file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $db->errorInfo() . PHP_EOL, FILE_APPEND));
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $arr = $row['station_code'];
                }
            }
            file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . 'arr = ' . $arr . PHP_EOL, FILE_APPEND);

            $result[0] = search_train($token, $dep, $arr, $date);

            $output = '';
            $idx = 0;
            if ($result && count($result) > 0) {
                foreach ($result as $i => $r) {
                    if (is_array($r)) {
                        foreach ($r as $key => $row) {
                            if (is_array($row)) {
                                foreach ($row as $k => $v) {
                                    if ($idx < 10) {
                                        if ($v['class_name_lang'] != '' or $v['class_name_lang'] != NULL) {
                                            $output .= 'kode kereta : ' . $v['train_id'] . PHP_EOL;
                                            $output .= 'kelas : ' . $v['class_name_lang'] . PHP_EOL;
                                            $output .= 'harga : ' . rp(intval($v['price_adult'])) . PHP_EOL;
                                            $output .= 'berangkat : ' . $v['departure_time'] . "  sampai " . $v['arrival_time'] . PHP_EOL;
                                            $output .= PHP_EOL;
                                        }
                                    }
                                    $idx++;
                                }
                            }
                        }
                    }
                }
            }
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $output . PHP_EOL, FILE_APPEND);

if ($output == '') {
    $output = 'tidak ada jadwal';
}
$out = [
    'speech' => $output,
    'displayText' => $output,
    'data' => NULL,
    'contextOut' => NULL,
    'source' => 'webhook'];
header('Content-type: application/json');
echo json_encode($out);
