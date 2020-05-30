
<?php

ini_set('session.use_only_cookies', 1);
ini_set('session.save_path', '/home/decryptoid/sessions');

session_start();
destroy_session_and_data();

header("Location: home.php");

//function that ends session and destroys data
function destroy_session_and_data() {
	$_SESSION = array();
	setcookie(session_name(), '', time() - 2592000, '/');
	session_destroy();
}
