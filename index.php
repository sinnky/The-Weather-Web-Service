<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
  <title>Weather Web Service</title>
</head>
<body>
  <div class="container">
    <center><h1>WEATHER WEB SERVICE</h1></center>
  <P>Please enter city names in lowercase and English characters.</P>
  <form action="index.php" method="GET"> 
    <input type="textarea" name="sehir" placeholder="City" />
    <input type="submit" name="gonder" value="Search"/>
  </form>
  </div>

  <?php

  ini_set('display_errors', 1);
  error_reporting(E_ERROR | E_PARSE);

  if(isset($_GET['sehir'])) { 
// ara fonk. iki değişken arasındaki istediğimiz veriyi almamıza yarar 
    function ara($bas, $son, $yazi)  { 
      @preg_match_all('/' . preg_quote($bas, '/') .
      '(.*?)'. preg_quote($son, '/').'/i', $yazi, $m);
      return @$m[1];
    }
// veri tabanına  kayıt online olarak

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

    $city = $_GET['sehir'];
  $city = str_replace('ğ', 'g', $city);
  $city = str_replace('ç', 'c', $city);
  $city = str_replace('ö', 'o', $city);
  $city = str_replace('ü', 'u', $city);
  $city = str_replace('ı', 'i', $city);
  $sehirismi = $city;

    $db = new PDO('mysql:host=localhost;dbname=havadurumu;charset=utf8mb4', 'root', '1234');// veritabanında root adında şifresi olmayan dp değişkeni oluşturup veritabanına erişim
    $date = date('Y-m-d'); // 2017-01-01 // veri tbanaına kayt tarihi
    $rows = query($db, "select * from hava where sehir = \"$sehirismi\" and day = \"$date\""); // 

    $link = "http://www.havadurumux.net/".$sehirismi."-hava-durumu/"; 

    $icerik = file_get_contents($link); // linkteki içeriğin hepsini alır 
    $derece = 'can not fetch'; 
    $derece_yarin = 'can not fetch';

    if ($icerik === FALSE) { // internet olmama durumu
      $start = date('Y-m-d', strtotime("-1 year +1 day")); 
      $rows = query($db, "select * from hava where sehir = \"$sehirismi\"".' and day > "'.$start.'"'); // veritbındn ver i çekme ortlma

      if (count($rows) > 0) {
        $avgrows = query($db, 'select floor(avg(temperature)) temperature, floor(avg(temperature_next_day)) temperature_next_day from hava where sehir = "'.$sehirismi.'" and day > "'.$start.'"');
        $derece = $avgrows[0]['temperature'];
        $derece_yarin = $rows[0]['temperature_next_day'];
        echo $sehirismi . '</br>';
      } else {
          die('CAN NOT FETCH DATA');
      }
    } else {
      echo $_GET['sehir']." wheather condition"."<br>";
      $derece = ara('<span>','</span>',$icerik);
      $derece_yarin = $derece[1];
      $derece = $derece[0];

      $derece = str_replace('°', '', $derece);
      $derece = trim(str_replace('&deg;', '', $derece));
      $derece_yarin = str_replace('°', '', $derece_yarin);
      $derece_yarin = trim(str_replace('&deg;', '', $derece_yarin));
    }

    echo 'current temperture = '.$derece.'&deg;<br/> tomorrows temperature = '.$derece_yarin.'&deg;';

    // save data into the db
    if (count ($rows) == 0) {
      query($db, 'insert into hava(sehir, day, temperature, temperature_next_day) values ("' . $sehirismi . '", "' . $date . '", "'.$derece.'", "' .$derece_yarin. '")');
    }
  }
  
  ?>
</body>
</html>
