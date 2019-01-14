<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Alert error</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"></script>
<style>
  #map {height: 600px; width: 100%;}
  .link_gmap{ margin-top: 20px; }
</style>
</head>
<!--情報：34.649070, 135.758757, 電気：34.648256, 135.759291-->

<body>

<?php
const GPS_FILE_NAME = 'gps.csv';
const CENTER_LAT = '34.649070'; // マップの中心座標
const CENTER_LON = '135.758757';
// 引数チェック
if( isset($_POST['msg_type']) and isset($_POST['latitude']) and isset($_POST['longitude']) ) {
	write_data( $_POST['msg_type'], $_POST['latitude'], $_POST['longitude'] );
} else {
	print_map();
}

// 台車からの通知動作
function write_data( $msg_type, $longitude, $latitude ) {
	$fp = fopen(GPS_FILE_NAME, 'w');
	if ($fp){
		if (flock($fp, LOCK_EX)){
			date_default_timezone_set('Asia/Tokyo');
			$str_time = date("Ymd His");
			$data = "$str_time, $msg_type, $latitude, $longitude";
			if( fwrite($fp, $data) === FALSE ) {
				error( "ファイル書き込みに失敗しました", $msg_type, $latitude, $longitude );
			}
			flock($fp, LOCK_UN);
	  }
	} else {
		error( "ファイルオープンに失敗しました", $msg_type, $latitude, $longitude );
	}
	fclose($fp);
	print("<div>success</div>");
}

// マップの表示
function print_map() {
	$c_latitude = CENTER_LAT;
	$c_longitude = CENTER_LON;
	if( file_exists(GPS_FILE_NAME) ) {
		$lines = file(GPS_FILE_NAME);
		if( count($lines)>0 ){
			$data = explode(",", trim($lines[0]), 4); // 4つまでのデータしか読まない（4つ以上はすべて4つ目の変数に入る）
			$str_time  = $data[0];
			$msg_type  = $data[1];
			$latitude  = trim($data[2]);
			$longitude = trim($data[3]);
			print<<<EOEU
<h1>$msg_type at $str_time</h1>
<div id="map"></div>
<script>
  var map = L.map('map');
  L.tileLayer('https://cyberjapandata.gsi.go.jp/xyz/std/{z}/{x}/{y}.png', {
    attribution: "<a href='https://maps.gsi.go.jp/development/ichiran.html' target='_blank'>地理院タイル</a>"
  }).addTo(map);
  map.setView([$c_latitude, $c_longitude], 18);
  var marker = L.marker([$latitude, $longitude]).addTo(map).bindPopup("<strong>HELP ME!!!</strong>").openPopup();
</script>
<div class="link_gmap">Googleマップで見る：<a href="https://www.google.com/maps?q=$latitude,$longitude" target="_blank">$latitude,$longitude</a></div>
EOEU;
		}
	} else {
		print("<h1>no error</h1>");
		print(GPS_FILE_NAME);
	}
}

?>

</body>
</html>

