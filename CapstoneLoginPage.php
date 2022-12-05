<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Mobile Microgrid Login Page</title>
	</head>
	<body>
		<p>Please enter your login information</p>
		<form id="loginForm" action="validateMMLogin.php" method="get">
			<label for="username">Username: </label>
			<input type="text" id="username" name="username"><br>
			<label for="password">Password: </label>
			<input type="password" id="password" name="password"><br>
			<input type="submit" name="send"><br>
		</form>
	</body>
</html>