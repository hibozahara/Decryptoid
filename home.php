
<?php

require_once 'login.php';

/* this php page allows already users to sign in,
		or new users to choose to navigate to register */

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


$conn = new mysqli($hn, $un, $pw, $db);
if($conn->connect_error) {
    die("MySQL connection error <br>" .$conn->connect_error);
}

/* Javascript, CSS, & HTML code within PHP Code. */

echo <<<_END
<head>
<script>

//check if uname || pass fields are empty

function valUname(inp){
	var entry = inp.trim();
	if(entry == "")
    return 'Username field is empty\\n';

	return "";
}

function valPwdword(inp){
	var entry = inp.trim();
  if(entry == "")
    return 'Password field is empty.\\n';

	return "";
}

function valAcc(fields){
	var err = valUname(fields.name.value);
	err += valPwdword(fields.pass.value);
	if(err == "")
		return true;
	else {
		alert(err);
		return false;
	}
}

</script>



<style>

body{
  font-family: Verdana;
  color: black;
  background-color: #D8BFD8;
  text-align: center;
}

#user{
	height: 30px;
	width: 180px;

}
#password{
	height: 30px;
	width: 180px;
	margin-top: 30px;
}

input{
	border: none;
	border-radius: 6px;
	margin: 5px;
	width: 300px;
	left: 50%;
	margin-left: -83px;
	position:fixed;
}

#reg h3{
	font-family: Verdana;
	top: 80%;
	left: 50%;
	margin-left: -80px;
	margin-top: -63px;
	position:fixed;
}
#reg button{
	font-family: Verdana;
	border: none;
	border-radius: 6px;
	margin: 5px;
	width: 180px;
	height: 30px;
	top: 85%;
	left: 60%;
	margin-left: -160px;
	position:fixed;
}
#reg button:hover{
	background-color: #778899;
	color: black;
}

#msg{
	font-weight: bold;
	font-style: italic;
  position: fixed;
  top: 62%;
  left: 27%;
}

#lbutton{
	font-family: Verdana;
	width: 180px;
	height: 30px;
	left: 52%;
	margin-top: 80px;
	margin-left: -100px;
	position:fixed;
}
#lbutton:hover{
	background-color: #778899;
	color: black;
}

</style>
</head>

<h1>A Decryptoid Just for You</h1>
<h3>Sign in here.</h3>
<form method="post" enctype="multipart/form-data" onsubmit="return valAcc(this)"><pre>
<input type="text" name="name" placeholder="Username" id="user">
<input type="password" name="pass" placeholder="Password" id="password">
<input type="submit" value="Login" id="lbutton">
</pre> </form>
_END;


echo <<<_END
<form method='post' action='register.php' enctype='multipart/form-data' id="reg"><pre>
<h3><br><br>Not signed up?</h3>
<button type="submit">Sign Up</button>
</pre></form>
_END;


if(isset($_POST['name']) && isset($_POST['pass'])) {
	$user = sanMySQL($conn, $_POST['name']);
	$pass = sanMySQL($conn, $_POST['pass']);

	$query = "SELECT * FROM user WHERE uname ='$user'";
	$res = $conn->query($query);

	$row = $res->fetch_array(MYSQLI_ASSOC);

	$s = $row['salt0'];
	$t = $row['salt1'];
	$token = hash('ripemd128', "$s$pass$t");

/* ensure that inputted credentials match db content */

	if ($token == $row['pword']) {
		$_SESSION['uname'] = $user;
		//set session to a day like shown in class slides
		ini_set('session.gc_maxlifetime', 60*60*24);
		$_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

		header("Location: main.php");
	}
	else echo '<div id="msg">Invalid username or password. Try again!</div>';

}

$conn->close();
$res->close();
