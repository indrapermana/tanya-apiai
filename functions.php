<?php

function get_web_page($url, $data = NULL) {

    $options = array(
        CURLOPT_CUSTOMREQUEST => "GET", // Atur type request, get atau post
        CURLOPT_POST => false, // Atur menjadi GET
        CURLOPT_FOLLOWLOCATION => true, // Follow redirect aktif
        CURLOPT_CONNECTTIMEOUT => 120, // Atur koneksi timeout
        CURLOPT_TIMEOUT => 120, // Atur response timeout
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
    file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $url . PHP_EOL, FILE_APPEND);
    $result = get_web_page($url);
    $content = json_decode($result['content'], true);
    return $content;
}

function search_train($token, $dep, $arr, $date) {
    $url = "https://api-sandbox.tiket.com/search/train?d=$dep&a=$arr&date=$date&ret_date=&adult=1&child=0&class=all&token=$token&output=json";
    file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $url . PHP_EOL, FILE_APPEND);
    $result = get_web_page($url);
    $content = json_decode($result['content'], true);
    $go_det = $content['departures'];
    return $go_det;
}

function get_all_airport($token) {
    //get all_airport
    $url = "http://api-sandbox.tiket.com/flight_api/all_airport?token=$token&output=json";
    $result = get_web_page($url);
    $content = json_decode($result['content'], true);
    $all_airport = $content['all_airport'];
    $airport = $all_airport['airport'];
    try {
        $db = new PDO('mysql:host=127.0.0.1;dbname=tanya', 'root', '');
        echo "inserting data..";
        if ($db) {
            $count = 0;
            foreach ($airport as $i => $r) {
                $airport_name = $r['airport_name'];
                $airport_code = $r['airport_code'];
                $location_name = $r['location_name'];
                $country_id = $r['country_id'];
                $country_name = $r['country_name'];
                $query = "INSERT INTO airport (`airport_name`,`airport_code`,`location_name`,`country_id`,`country_name`) "
                        . "VALUES ('$airport_name','$airport_code','$location_name','$country_id','$country_name')";
                $stmt = $db->query($query) or die(print_r($db->errorInfo()));
                $count++;
            }
        }
        echo "done inserting $count airport data";
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function get_all_station($token) {
    //get all_airport
    $url = "https://api-sandbox.tiket.com/train_api/train_station?token=$token&output=json";
    $result = get_web_page($url);
    $content = json_decode($result['content'], true);
    $stations = $content['stations'];
    $station = $stations['station'];
    try {
        $db = new PDO('mysql:host=127.0.0.1;dbname=tanya', 'root', '');
        echo "inserting data..";
        if ($db) {
            $count = 0;
            foreach ($station as $i => $r) {
                $station_name = $r['station_name'];
                $city_name = $r['city_name'];
                $station_code = $r['station_code'];
                $latitude = $r['latitude'];
                $longitude = $r['longitude'];
                $query = "INSERT INTO station (`station_name`,`city_name`,`station_code`,`latitude`,`longitude`) "
                        . "VALUES ('$station_name','$city_name','$station_code','$latitude','$longitude')";
                $stmt = $db->query($query) or die(file_put_contents("log-" . date('Y-m-d') . ".txt", date('Y:m:d H:i:s') . ' - ' . $db->errorInfo() . PHP_EOL, FILE_APPEND));
                $count++;
            }
        }
        echo "done inserting $count station data";
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function rp($angka) {
    $jadi = "Rp " . number_format($angka, 2, ',', '.');
    return $jadi;
}

function is_in_range_waktu($rangetimearrival, $value) {
    date_default_timezone_set('Asia/Jakarta');
    /* This sets the $time variable to the current hour in the 24 hour clock format */
    $date = date_create_from_format("H:i", $value);
    $time = $date->format("H:i");
    if (strtolower($rangetimearrival) == 'subuh') {
        if ($time >= '3' and $time < '6') {
            return true;
        }
    } else if (strtolower($rangetimearrival) == 'pagi') {
        if ($time >= '6' and $time < '11') {
            return true;
        }
    } else if (strtolower($rangetimearrival) == 'siang') {
        if ($time >= '11' and $time < '13') {
            return true;
        }
    } else if (strtolower($rangetimearrival) == 'sore') {
        if ($time >= '15' and $time < '18') {
            return true;
        }
    } else if (strtolower($rangetimearrival) == 'malam') {
        if ($time >= '18' and $time < '3') {
            return true;
        }
    } else {
        return false;
    }
}
