
<?php

require_once 'login.php';

/* this php page allows registered users to upload .txt file
    or text entry to encrypt or decrypt data using a cipher of their
    choice (DT, RC4, or SS) */

//sanitizing functions for any inputs
function sanMySQL($connection, $str) {
  $str = sanStr($str);
  $str = $connection->real_escape_string($str);
  return $str;
}

function sanStr($str) {
  if(get_magic_quotes_gpc()) {
    $str = stripslashes($str);
  }

  $str = strip_tags($str);
  $str = htmlentities($str);
  return $str;
}



ini_set('session.use_only_cookies', 1);
ini_set('session.save_path', '/home/hibozahara/mysessions/');
session_start();
if (!isset($_SESSION['initiated'])) {
  session_regenerate_id();
  $_SESSION['initiated'] = 1;
  
}


/* preventing session hijacking */

if ($_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])) {

  $conn = new mysqli($hn, $un, $pw, $db);
  if($conn->connect_error) {
    die("MySQL connection error <br>" .$conn->connect_error);
  }


  /* cipher 1 SS; require key to be at least 3 (more secure than 1 char key)
  */

  function SS($inp, $key, $choice) {

    if (strlen($key) > 26 || strlen($key) < 3) {
      die("Key should be 3-26 characters");
    }

    $alpha = 'abcdefghijklmnopqrstuvwxyz';
    $curr = $alpha;
    $last= deleteDup($key);

    for ($x = 0; $x < strlen($last); $x++) {
      if (strpos($curr, $last[$x]) !== false) {
        $curr = str_replace($last[$x], '', $curr);
      }
    }
    $last.= $curr;
    $cipher = '';
    $inp = strtolower($inp);
    for ($x = 0; $x < strlen($inp); $x++) {
      if ($inp[$x] === " ") $cipher .= " ";
      else {
        if ($choice === "Encrypt") {
          $ind = strpos($alpha, $inp[$x]);
          $cipher .= $last[$ind];
        }
        else if ($choice === "Decrypt") {
          $ind = strpos($last, $inp[$x]);
          $cipher .= $alpha[$ind];
        }
      }
    }
    return $cipher;
  }

  //taking duplicates into consideration for SS cipher
    function deleteDup($entry) {
      $res = '';
      for ($x = 0; $x < strlen($entry); $x++) {
        for ($y = $x+ 1; $y<  strlen($entry); $y++) {
          if ($entry[$x] === $entry[$y])
          continue 2;
        }
        $res .= $entry[$x];
      }
      return $res;
    }



  /*****************************FIX random characters that print  */
define("RC4", 'rc4');

    //cipher 2 - RC4
    function RC4($inp) {
      $key = RC4;
      $out = '';
      $arr = array();

      for ($x = 0; $x < 256; $x++) {
        $arr[$x] = $x;
      }
      $y = 0;
      for ($x = 0; $x < 256; $x++) {
        $y = ($y + $arr[$x] + ord($key[$x % strlen($key)])) % 256;
        //swap
        $temp = $arr[$x];
        $arr[$x] = $arr[$y];
        $arr[$y] = $temp;

      }
      $a = 0;
      $b = 0;

      for ($x = 0; $x < strlen($inp); $x++) {
        $a = ($a + 1) % 256;
        $b = ($b + $arr[$a]) % 256;
        //swap
        $temp = $arr[$a];
        $arr[$a] = $arr[$b];
        $arr[$b] = $temp;

        $out .= $inp[$x] ^ chr($arr[($arr[$a] + $arr[$b]) % 256]);

        //echo mb_detect_encoding($out);

        //exluding any non alpha characters.
        //$out = preg_replace('/[^a-z]/i', 'a',  $out);

      }
      return $out;

    }



//cipher 3 - DT

function dT($inp, $key, $choice) {

  $cipher_res = '';
  $alpha = 'abcdefghijklmnopqrstuvwxyz';
  $row = strlen($inp) / strlen($key);
  $cnt = 0;

  if (strlen($key) < 3) {
    die("Key should be 3-26 characters");
  }

  if ($choice === "Decrypt") {
    $ind = 0;
    $tmp = array_fill(0, strlen($key), '');

    $col = array_fill(0, strlen($key), "");
    for ($x = 0; $x < strlen($key); $x++) {
      $col[$x] = substr($inp, $row * $i, $row);
    }

    while ($ind < strlen($key)) {
      if (!empty(strpos($key, $alpha[$cnt])) || strpos($key, $alpha[$cnt]) === 0){
        $tmp[strpos($key, $alpha[$cnt])] = $col[$ind++];
        $key[strpos($key, $alpha[$cnt])] = ".";
      }
      else $cnt++;
    }
    for ($x= 0; $x< $row; $x++) {
      for ($y= 0; $y< strlen($key); $y++) {
        $cipher_res .= $tmp[$y][$x];
      }
    }
  }

  else if ($choice === "Encrypt") {
    while (strlen($inp) % strlen($key) !== 0) {
      $inp .= 'x';
    }
    for ($x= 0; $x< strlen($key); $x++) {
      while ($cnt < strlen($alpha)) {
        $ind = strpos($key, $alpha[$cnt]);
        if (!empty($ind) || $ind === 0) {
          $key[strpos($key, $alpha[$cnt])] = ".";
          break;
        }
        else $cnt++;
      }
      for ($y= 0; $y< $row; $y++)
      {
        $cipher_res .= $inp[$y* strlen($key) + $ind];
      }
    }
  }

  return $cipher_res;
}


  echo <<<_END
  <style>

  body textarea, select, button, input{
    border: none;
    border-radius: 6px;
  }

  body{
    font-family: Verdana;
    color: black;
    text-align: center;
    background-color: #B0E0E6;
  }

  #logout button{
    margin-top: -347px;
    margin-left: 255px;
    position:fixed;
    font-family: Verdana;
    border: none;
    border-radius: 6px;
  }
  #logout button:hover{
    background-color: #778899;
  }
  </style>

  <body><form method='post' action='main.php' enctype='multipart/form-data'>
  Upload <input type='file' name='filename'> <br> <br>
  <textarea name="input" style=" width:350px; height:200px;" placeholder="Enter text or upload .txt file :) "></textarea><br><br>
  <select name="cipher" value="Select a Cipher">
  <option value="picked">Pick a Cipher</option>
  <option value="dT">Double Transposition</option>
  <option value="rc4">RC4</option>
  <option value="SS">Simple Substitution</option>

  </select><br><br>
  Key: <input type="text" name="key">
  <button type="submit" name="eButton">Encrypt</button>
  <button type="submit" name="dButton">Decrypt</button>
  </form>
  _END;

  echo <<<_END
  <html>
  <form method='post' action='logout.php' enctype='multipart/form-data' id="logout">
  <button type="submit">Sign Out</button>
  </form>
  _END;


  $inp = '';
  $output = '';
  $choice = '';
  $cipher = '';
  $key = '';
  /* checking if either a file was uploaded or if text was entered in the given box */
    if(!empty($_FILES['filename']['name'])) {
      $fname = $_FILES['filename']['name'];
      $fname = strtolower(preg_replace("[^A-Za-z0-9]", "", $fname));

      if($_FILES['filename']['type'] == 'text/plain') {
        $myfile = fopen($fname, 'r') or die("<br>Unable to open file<br>");
        $inp = sanMySQL($conn, fgets($myfile));
        fclose($myfile);
      }
      else {
        die("<br>Text files only<br>");
      }
    }

    else if(isset($_POST['input'])) {
      $inp = sanMySQL($conn, $_POST['input']);
    }

    else {
      die("<br>No file or text<br>");
    }
    echo "</body></html>";


//Decrypt or Encrypt is selected?

  if(isset($_POST['eButton']))
    $choice = 'Encrypt';
  else if(isset($_POST['dButton']))
    $choice = 'Decrypt';

  // Key input?
  if (!isset($_POST['key']))
    echo "<br>Input a unique key<br>";
  else $key = sanMySQL($conn, $_POST['key']);


  //DT, Rc4, or SS?
  if (isset($_POST['cipher']))
    $cipher = sanMySQL($conn, $_POST['cipher']);

	if($cipher !== 'picked'){
		if($cipher === 'SS') $output = SS($inp, $key, $choice);
		else if($cipher === 'dT') $output = dT($inp, $key, $choice);
		else if($cipher === 'rc4') $output = rc4($inp);
		echo "Your ciphertext/plaintext result:<br><br>".$output;
	}
	else die("<br>Choose your cipher first!<br>");

  //DB query insertion

    $user = $_SESSION['uname'];
    $query = "INSERT INTO cipherstorage (uname, textInput, cipher, output, uKey, method) VALUES ('$user','$inp', '$cipher', '$out', '$key', '$choice')";
    $res = $conn->query($query);

    $conn->close();

  }

  else echo " Please Login or Register";
