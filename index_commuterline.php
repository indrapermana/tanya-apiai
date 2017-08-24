<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

function getData($db, $query) {
    $stmt = $db->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$host = '127.0.0.1';
$db = 'commuterline';
$username = 'root';
$password = '';

$params = json_decode(file_get_contents('php://input'), TRUE);
//$barang = $_SERVER['barang'];
$result = $params['result'];
//$text = 'anda memesan ' . json_encode($result['parameters']);
$param = $result['parameters'];
$to = $param['to'];
$from = $param['from'];
$rangetimearrival = $param['rangetimearrival'];
$query = "";

if (!$to) {
    $to = $_REQUEST['to'];
}
if (!$from) {
    $from = $_REQUEST['from'];
}
if (!$rangetimearrival) {
    $rangetimearrival = $_REQUEST['rangetimearrival'];
}

$filter = "";
if(strtolower($rangetimearrival) == 'subuh'){
    $filter = " AND waktu_datang BETWEEN '03:00:00' AND '05:59:00'";
}else if(strtolower($rangetimearrival) == 'pagi'){
    $filter = " AND waktu_datang BETWEEN '06:00:00' AND '10:59:00'";
}else if(strtolower($rangetimearrival) == 'siang'){
    $filter = " AND waktu_datang BETWEEN '11:00:00' AND '14:59:00'";
}else if(strtolower($rangetimearrival) == 'sore'){
    $filter = " AND waktu_datang BETWEEN '15:00:00' AND '17:59:00'";
}else if(strtolower($rangetimearrival) == 'malam'){
    $filter = " AND waktu_datang BETWEEN '18:00:00' AND '02:59:00'";
}

if($to and $from){
    $query = "SELECT * from jadwalkrl WHERE stasiun_persinggahan='$to' AND stasiun_keberangkatan='$from' AND keterangan != 'BATAL' $filter";
}else if($to and !$from){
    $query = "SELECT * from jadwalkrl WHERE stasiun_persinggahan='$to' AND keterangan != 'BATAL' $filter";
}else if(!$to and $from){
    $query = "SELECT * from jadwalkrl WHERE stasiun_keberangkatan='$from' AND keterangan != 'BATAL' $filter";
}else{
     $query = "SELECT * from jadwalkrl WHERE stasiun_persinggahan='$to' AND keterangan != 'BATAL' $filter";
}

$query .= " ORDER BY waktu_datang ASC";
//echo $query;exit;
//$dsn = "pgsql:host=$host;port=5432;dbname=$db;user=$username;password=$password";
//$db = "mysql:host=$host;dbname=$db; user=$username;password=$password";
$data = [];
$string = "";
try {
    // create a PostgreSQL database connection
    $db = new PDO('mysql:host=localhost;dbname=commuterline', 'root', 'password');
    // display a message if connected to the PostgreSQL successfully
    if ($db) {
        $stmt = $db->query($query) or die(print_r($db->errorInfo()));
        $no = 1;
        $string = "datang : ";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
           // $string .= $no .". No.KA=". $row['no_ka'] . ",Ket.=" .$row['keterangan'] .",Berangkat=".$row['stasiun_keberangkatan'].",Waktu Datang:". $row['waktu_datang'].",Waktu berangkat=". $row['waktu_berangkat']."<br>";
            $string .= $row['waktu_datang'].",";
            $no++;
        }
    }
} catch (PDOException $e) {
    // report error message
    echo $e->getMessage();
}
if($no == 1){
    $string = "tidak ada jadwal";
}
$out = [
    'speech' => $string,
    'displayText' => $string,
    'data' => NULL,
    'contextOut' => NULL,
    'source' => 'webhook'];
header('Content-type: application/json');
echo json_encode($out);
?>