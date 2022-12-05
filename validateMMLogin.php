<?php
	$conn = pg_connect("host=dbclass.cs.unca.edu port=5432 dbname=cmartens user=cmartens password=Pin1tr11Star");

	if (!$conn){
		echo "An error has occured";
		exit;
	}

	//Sanatize inputs!!!
	//$myQuery = "SELECT password FROM users WHERE username LIKE '".$_GET['username']."';";
	$result = pg_fetch_all_columns(pg_query_params($conn, 'SELECT password FROM users WHERE username LIKE $1', array($_GET['username'])));
	
	$canLogin = 0;
	if (count($result) == 1){
		if ($result[0] == $_GET['password']){
			$canLogin = 1;
		}
	}
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Gateway Page</title>
	</head>
	<body>
		<?php if ($canLogin == 1){ ?>
			<a href="Capstone_UI.php">You've successfully logged in, click here to continue</a>
		<?php }else { ?>
			<a href="CapstoneLoginPage.php">We could not validate your information, click here to return to the login page</a>
		<?php } ?>
	</body>
</html>