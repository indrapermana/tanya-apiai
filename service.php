<?php

function get_web_page($url, $data = NULL) {

    $options = array(
        CURLOPT_CUSTOMREQUEST => "GET", // Atur type request, get atau post
        CURLOPT_POST => false, // Atur menjadi GET
        CURLOPT_FOLLOWLOCATION => true, // Follow redirect aktif
        CURLOPT_CONNECTTIMEOUT => 3, // Atur koneksi timeout
        CURLOPT_TIMEOUT => 3, // Atur response timeout
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Content-type: application/json'),
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_USERAGENT => 'twh:24699624;PT Lakon Teknologi Dwipantara;'
    );

    $ch = curl_init($url);          // Inisialisasi Curl
    curl_setopt_array($ch, $options);    // Set Opsi
    $content = curl_exec($ch);           // Eksekusi Curl
    curl_close($ch);                     // Stop atau tutup script

    $header['content'] = $content;
    return $header;
}

function search_flight($token, $dep, $arr, $date) {
    $url = "http://api-sandbox.tiket.com/search/flight?d=$dep&a=$arr&date=$date&adult=1&child=0&infant=0&token=$token&v=3&output=json";
    file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' content ' . $url . PHP_EOL, FILE_APPEND);
    $result = get_web_page($url);
    $content = json_decode($result['content'], true);
    return $content;
}

function search_train($token, $dep, $arr, $date) {
    $url = "https://api-sandbox.tiket.com/search/train?d=$dep&a=$arr&date=$date&ret_date=&adult=1&child=0&class=all&token=$token&output=json";
    file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' content ' . $url . PHP_EOL, FILE_APPEND);
    $result = get_web_page($url);
    $content = json_decode($result['content'], true);
    $go_det = $content['departures'];
    return $go_det;
}

function rp($angka) {
    $jadi = "Rp " . number_format($angka, 2, ',', '.');
    return $jadi;
}

function is_date($x) {
    return (date('d-m-Y', strtotime($x)) == $x);
}

function is_in_range_waktu($rangetimearrival, $value) {
    date_default_timezone_set('Asia/Jakarta');
    /* This sets the $time variable to the current hour in the 24 hour clock format */
    $date = date_create_from_format("H:i", $value);//value : 17:00
    $time = $date->format("H:i");
    if (trim(strtolower($rangetimearrival)) == 'subuh' and $time >= "3" and $time < "6") {
        file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' subuh ' . PHP_EOL, FILE_APPEND);
        return true;
    } else if (trim(strtolower($rangetimearrival)) == 'pagi' and $time >= "6" and $time < "11") {
         file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' pagi ' . PHP_EOL, FILE_APPEND);
        return true;
    } else if (trim(strtolower($rangetimearrival)) == 'siang' and $time >= "11" and $time < "13") {
         file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' siang ' . PHP_EOL, FILE_APPEND);
        return true;
    } else if (trim(strtolower($rangetimearrival)) == 'sore' and $time >= "15" and $time < "18") {
         file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' sore ' . PHP_EOL, FILE_APPEND);
        return true;
    } else if (trim(strtolower($rangetimearrival)) == 'malam' and $time >= "18" and $time < "3") {
         file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' malam ' . PHP_EOL, FILE_APPEND);
        return true;
    } else {
        return false;
    }
}

$params = json_decode(file_get_contents('php://input'), TRUE);
//file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' content ' . $params . PHP_EOL, FILE_APPEND);
$result = $params['result'];
$metadata = $result['metadata'];
$intent = $metadata['intentName'];
$resolved_query = $result['resolvedQuery'];
$param = $result['parameters'];
$aksi = $param['aksi'];
$fromplace = $param['fromplace'];
$jenis = $param['jenis']; //bisa diganti intentName
$p1 = $param['p1']; //periode eq.besok
$p2 = $param['p2']; //periode eq.pagi
$tanggal = $param['tanggal']; //periode eq.pagi
$toplace = $param['toplace'];

//token
$token = "341798d4c59c49156235e09acd70972d9e145a2c"; //token tiket.com

 date_default_timezone_set('Asia/Jakarta');
$today = date("Y-m-d");
file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' today =  ' . $today . PHP_EOL, FILE_APPEND);
$dt = new DateTime($today);
if ($p1 == 'besok' or $p2 == 'besok') {
    $dt->add(new DateInterval('P1D')); //tambah 1 hari
} else if ($p1 == 'lusa' or $p2 == 'lusa') {
    $dt->add(new DateInterval('P2D')); //tambah 2 hari
} else if (is_numeric($p1)) {
    $now = date('d');
    $add = $p1 - $now;
    date_add($dt, date_interval_create_from_date_string($add . ' days'));
}else{
    $today = date("Y-m-d");
    $dt = new DateTime($today);
}
if ($tanggal) {
    file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' masupp  ' . PHP_EOL, FILE_APPEND);
    $date = date('Y-m-d', strtotime(trim($tanggal)));
    $dt = new DateTime($date);
}
$date = $dt->format('Y-m-d');
file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' waktu =  ' . $p1 . PHP_EOL, FILE_APPEND);
file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' date =  ' . $date . PHP_EOL, FILE_APPEND);
switch ($intent) {
    case 'cari_pesawat':
        $content = search_flight($token, $fromplace, $toplace, $date);
        $results[0] = $content['departures'];
        file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' count ' . count($results[0]) . PHP_EOL, FILE_APPEND);
        if (count($results[0]) > 0) {
            file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' enter search.. ' . PHP_EOL, FILE_APPEND);
            $output = '';
            $idx = 0;
            if ($results && count($results) > 0) {
                foreach ($results as $i => $r) {
                    if (is_array($r)) {
                        foreach ($r as $key => $row) {
                            if (is_array($row)) {
                                foreach ($row as $k => $v) {
                                    if ($p2 and $p2 != 'now' and $p2 != 'besok' and $p2 != 'lusa') {
                                        if (is_in_range_waktu($p2, $v['simple_departure_time'])) {
                                            $output .= 'rute : ' . $v['departure_city_name'] . " ke " . $v['arrival_city_name'] . PHP_EOL;
                                            $output .= 'airlines : ' . $v['airlines_name'] . PHP_EOL;
                                            $output .= 'harga : ' . rp(intval($v['price_value'])) . PHP_EOL;
                                            $output .= 'berangkat : ' . $v['departure_flight_date_str'] . " " . $v['simple_departure_time'] . "-" . $v['simple_arrival_time'] . PHP_EOL;
                                            $output .= PHP_EOL;
                                            $idx++;
                                        }
                                    } else {
                                        $output .= 'rute : ' . $v['departure_city_name'] . " ke " . $v['arrival_city_name'] . PHP_EOL;
                                        $output .= 'airlines : ' . $v['airlines_name'] . PHP_EOL;
                                        $output .= 'harga : ' . rp(intval($v['price_value'])) . PHP_EOL;
                                        $output .= 'berangkat : ' . $v['departure_flight_date_str'] . " " . $v['simple_departure_time'] . "-" . $v['simple_arrival_time'] . PHP_EOL;
                                        $output .= PHP_EOL;
                                        $idx++;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $output = 'tidak ada jadwal';
            }
        } else {
            $output = 'tidak ada jadwal';
        }
        break;

    case 'cari_kereta':
        $result[0] = search_train($token, $fromplace, $toplace, $date);
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
        break;

    default:

        break;
}
file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' output ' . $output . PHP_EOL, FILE_APPEND);
if(count($result) == 0 or !$output or empty($output)){
    $output = "tidak ada jadwal";
}
$out = [
    'speech' => $output,
    'displayText' => $output,
    'data' => NULL,
    'contextOut' => NULL,
    'source' => 'webhook'];

header('Content-type: application/json');
echo json_encode($out);
