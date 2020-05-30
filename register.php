
<?php

/* this php page allows new users
  to register to the page */

require_once 'login.php';

//sanitizing functions for any inputs (username, email, pass)
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

/* function that generates random salt, which
is later used to generate 2 different salts that is hashed with the password
*/

function generateSalt() {
  $s_len = 5;
  $mysalt = "";
  $alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";

  for ($i = 0; $i < $s_len; $i++) {
    $rand = rand(0, strlen($alpha));
    $mysalt .= $alpha[$rand-1];
  }

  return $mysalt;
}


//username should be at least 6 chars. constant
define("userlen", 6);

//establishing a new db connection
$conn = new mysqli($hn, $un, $pw, $db);
if($conn->connect_error) {
  die("MySQL connection error <br>" .$conn->connect_error);
}
$userlen = userlen;


/* Javascript, CSS, & HTML code within PHP Code.
JS code to validate user inputs for new account creation - check for appropriate length, characters..
HTML used for the input fields and Button
CSS for styling the webpage */

echo<<<_END
<html>
<head>
<script>

//email verfication.. ensure input is not empty & only uses letters, #'s, @, -, .
function valEmail(inp){
  var entry = inp.trim();
  if(entry == "")
    return 'Email field is empty\\n';
  else if(/[^a-zA-Z0-9@_\-\.]/.test(inp))
    return 'Only use the appropriate symbols for email\\n';

//generic validation... someinput@someinput.someinput - if not the format then decline entry
  else if(!/\S+@\S+\.\S+/.test(inp))
  return 'Enter your email in the format: "test@test.com"\\n';
  return "";
}

function valUname(inp){
  var entry = inp.trim();
  if(entry == "")
    return 'Username field is empty\\n';

  //username picked must be 6 chars or >
  else if(inp.length < $userlen)
    return 'Please choose a username that is at least $userlen characters!\\n';

  //check if the username picked uses the allowed symbols
  else if(/[^a-zA-Z0-9-_]/.test(inp))
    return 'Allowed for username: Letters, numbers,-, _\\n';
  return "";
}

function valPwd(inp){
  var entry = inp.trim();
  if(entry == "")
    return 'Password field is empty.\\n';

  //Check that the password contains at least one uppercase, one lowercase and one number 0-9
  else if(!/[a-z]/.test(inp) || !/[A-Z]/.test(inp) || !/[0-9]/.test(inp)|| !/[$&+,:;=?@#|'<>.^*()%!-]/.test(inp))
  return 'Password Requirements: At least \\n 1 special symbol \\n 1 uppercase letter \\n 1 lowercase letter \\n 1 number \\n';
  return "";
}

function valAcc(fields){
  var err = valEmail(fields.email.value);
  err += valUname(fields.name.value);
  err += valPwd(fields.pass.value);
  if(err == "")
    return true;
  else {
    alert(err);
    return false;
  }
}

</script>


<style>

input{
  border: none;
  border-radius: 6px;
  margin: 5px;
  width: 225px;
  left: 50%;
  margin-left: -100px;
  position:fixed;
}

#email{
  height: 30px;
}

#user{
  height: 30px;
  margin-top: 32px;
}

#password{
  height: 30px;
  margin-top: 60px;
}

#bbutton{
  font-family: Verdana;
  width: 80px;
	height: 30px;
  left: 50%;
  margin-top: -190px;
  margin-left: -365px;
  position:fixed;
}

#bbutton:hover{
  background-color: #778899;
	color: black;
}

#cbutton{
  font-family: Verdana;
  width: 180px;
	height: 30px;
  left: 50%;
  margin-top: 100px;
  margin-left: -80px;
  position:fixed;
}

#cbutton:hover{
  background-color: #778899;
	color: black;
}

#newUser h3{
  font-family: Verdana;
  top: 80%;
  left: 50%;
  margin-left: -50px;
  margin-top: -70px;
  position:fixed;
}

#newUser button{
  font-family: Verdana;
  border: none;
  border-radius: 6px;
  margin: 5px;
  width: 300px;
  height: 30px;
  top: 80%;
  left: 50%;
  margin-left: -150px;
  position:fixed;
}

#newUser button:hover{
  background-color: lightgreen;
  color: black;
}

#invalid{
  position: fixed;
  top: 80%;
  left: 45%;
}

body{
  font-family: Verdana;
  color: black;
  background-color: #FFB6C1;
  text-align: center;
  margin-top: 2%;
}
</style>

</head>
<h1> Welcome! </h1>
<h3> Register below. <br></h3>
<form method="post" enctype="multipart/form-data" onsubmit="return valAcc(this)"><pre>
<input type="text" name="email" placeholder="Email" id="email">
<input type="text" name="name" placeholder="Username" id="user">
<input type="password" name="pass" placeholder="Password" id="password"> <br>
<input type="submit" value="Register" id="cbutton">
</pre> </form>
<div id="invalid"> </div>
_END;

//go back to 'home'
echo <<<_END
  <form action="home.php" method="post" enctype="multipart/form-data">
  <input type="submit" value="Back" id = "bbutton" ame="home"></form>
_END;


if (isset($_POST['email']) && isset($_POST['name']) && isset($_POST['pass'])) {
  $email = sanMySQL($conn, $_POST['email']);
  $user = sanMySQL($conn, $_POST['name']);
  $pass = sanMySQL($conn, $_POST['pass']);


  /* FILTER_VALIDATE_EMAIL ensures a valid email was inputted
  checking characters of username - which should only have letters, #'s, and . (if wanted)'. */
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die('<div id="invalid">This is not a valid email.</div>');
  if (preg_match('/[!@#$%^&*()+=?<>"":;-_]/', $user))die('<div id="invalid"> Only must use letters a-z, digits 0-9, and '.' </div>');
  echo "</html>";

  $s = generateSalt();
  $t = generateSalt();
  $token = hash('ripemd128', "$s$pass$t");

  $query = "INSERT INTO user (uname, email, pword, salt0, salt1) VALUES ('$user', '$email', '$token', '$s', '$t')";
  $res = $conn->query($query);

  if(!$res) die('<div id="invalid">User exists in db</div>');

  /* upon successful account creation, user is navigated to the home page to login in*/
  header("Location: home.php");

}

$conn->close();
