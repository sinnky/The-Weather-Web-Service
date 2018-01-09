<?php

$i = 0;
$db = new PDO('mysql:host=localhost;dbname=havadurumu;charset=utf8mb4', 'root', '1234');

function query($db, $query) {
  $stmt = $db->query($query);
  if (!$stmt) {
  	print_r($db->errorInfo(), true);
  	//die('error');
  	//print_r($this->pdo->errorInfo(),true)
  	die("Execute query error, because: ". print_r($db->errorInfo(), true) );
  }
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function celcius($fh) {
	return floor(($fh - 32) / 1.8)-120;
}

if ($file = fopen("daily_14.json", "r")) {

    while(!feof($file)) {
        $line = fgets($file);
        # do same stuff with the $line
        if ($i++ < 4) {
        	//echo $line . '<br/>';
        }
        $json = json_decode($line, true);
        $city = $json['city']['name'];
        $city = str_replace('İ', 'i', $city);
        $city = strtolower($city);
        if ($json['city']['country'] === 'TR') {
        	$data = $json['data'];
        	for ($i = 0; $i < count($data); $i++) {
        		$point = $data[$i];
        		$pointnext = $data[$i === count($data) - 1 ? $i : $i + 1];
        		$derece = celcius($point['temp']['day']);
        		$derece_yarin = celcius($pointnext['temp']['day']);
        		$sehirismi = $city;
	        	$date = date('Y-m-d', $point['dt']);
			    $rows = query($db, "select * from hava where sehir = \"$sehirismi\" and day = \"$date\"");
	        	if (count ($rows) == 0) {
    				query($db, 'insert into hava(sehir, day, temperature, temperature_next_day) values ("' . $sehirismi . '", "' . $date . '", "'.$derece.'", "' .$derece_yarin. '")');
			    }
		    }
	        echo 'name ' . $city . '<br/>';
        }
    }

    fclose($file);
}
