<!--PHP setcookie('name', json_encode(array)) and json_decode($_COOKIE['name'], true)-->
<!--//JS document.cookie = ("name" + "=" + JSON.stringify(array)) and JSON.parse(document.cookie("name"))-->
<?php
if (isset($_GET['send'])){
	$conn = pg_connect("host=dbclass.cs.unca.edu port=5432 dbname=cmartens user=cmartens password=Pin1tr11Star");
	$allCarts = pg_fetch_all_columns(pg_query($conn, "SELECT cart FROM battery_measurements;"));
	$graphTables = array("battery_measurements", "battery_percentages");
	$chartTables = array("cart_location");

	$graphingCols = array();
	for ($i = 0; $i < count($graphTables); $i ++){
		$result = pg_query($conn, "SELECT * FROM ".$graphTables[$i]." LIMIT 1;");
		for ($j = 0; $j < pg_num_fields($result); $j ++){
			array_push($graphingCols, pg_field_name($result, $j));
		}
	}
	$graphingCols = array_unique($graphingCols);
	array_splice($graphingCols, array_search("cart", $graphingCols), 1);

	$chartingCols = array();
	for ($i = 0; $i < count($chartTables); $i ++){
		$result = pg_query($conn, "SELECT * FROM ".$chartTables[$i]." LIMIT 1;");
		for ($j = 0; $j < pg_num_fields($result); $j ++){
			array_push($chartingCols, pg_field_name($result, $j));
		}
	}
	$chartingCols = array_unique($chartingCols);
	array_splice($chartingCols, array_search("cart", $chartingCols), 1);

	$chosenCarts = array();
	foreach ($allCarts as $temp){
		if (isset($_GET[$temp])){
			array_push($chosenCarts, $temp);
		}
	}
	if (count($chosenCarts) == 0){
		echo "Please go back to the previous page and select all carts you wish to look at";
		exit;
	}

	$graphingData = array(array());
	array_push($graphingData, array());
	foreach ($graphingCols as $temp){
		if (isset($_GET[$temp])){
			array_push($graphingData[0], $temp);
		}
	}
	$chartingData = array(array());
	foreach ($chartingCols as $temp){
		if (isset($_GET[$temp])){
			array_push($chartingData[0], $temp);
		}
	}

	$graphDone = 0;
	$chartDone = 0;
	if (count($chartingData[0]) == 0){
		if (count($graphingData[0]) == 0) {
			echo "Please go back to the previous page and select all information you wish to look at";
			exit;
		}else {
			$graphDone = 1;
		}
	}else {
		$chartDone = 1;
		if (count($graphingData[0]) != 0){
			$graphDone = 1;
		}
	}

	//Graphing
	for ($i = 0; $i < count($graphingData[0]); $i ++){
		array_push($graphingData, array());
	}
	$tableIndex = 0;
	for ($i = 0; $i < count($graphingData[0]); $i ++){
		foreach ($chosenCarts as $tempCart){
			$result = pg_query($conn, "SELECT ".$graphingData[0][$i]." FROM ".$graphTables[$tableIndex]." WHERE cart LIKE '".$tempCart."';");
			if (!$result){
				if (++$tableIndex == count($graphTables)){
					break;
				}
				$result = pg_query($conn, "SELECT ".$graphingData[0][$i]." FROM ".$graphTables[$tableIndex]." WHERE cart LIKE '".$tempCart."';");
			}
			$result = pg_fetch_row($result);
			if ($result[0] == ""){
				$result[0] = 0;
			}
			array_push($graphingData[$i+2], $result[0]);
		}
		if ($tableIndex == count($graphTables)){
			break;
		}
	}

	//Charting
	for ($i = 0; $i < count($chartingData[0]); $i ++){
		array_push($chartingData, array());
	}
	$tableIndex = 0;
	for ($i = 0; $i < count($chartingData[0]); $i ++){
		foreach ($chosenCarts as $tempCart){
			$result = pg_query($conn, "SELECT ".$chartingData[0][$i]." FROM ".$chartTables[$tableIndex]." WHERE cart LIKE '".$tempCart."';");
			if (!$result){
				if (++$tableIndex == count($chartTables)){
					break;
				}
				$result = pg_query($conn, "SELECT ".$chartingData[0][$i]." FROM ".$chartTables[$tableIndex]." WHERE cart LIKE '".$tempCart."';");
			}
			$result = pg_fetch_row($result);
			if ($result[0] == ""){
				$result[0] = 0;
			}
			array_push($chartingData[$i+1], $result[0]);
		}
		if ($tableIndex == count($chartTables)){
			break;
		}
	}

	//Combining the arrays into a string
	/*
	for ($i = 0; $i < count($graphingData); $i ++){
		$graphingData[$i] = implode(",",$graphingData[$i]);
	}
	$graphingData = implode("|",$graphingData);
	*/

	//setcookie("toJS", $graphingData);
	//setcookie("toPHP", $graphingData);
}
?>
<!DOCTYPE html>
<!-- Skeleton -->
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Mobile Microgrid Admin</title>
		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	</head>
	<?php
		//$conn = pg_connect("host=fleet-management-database.csg5vowywacr.us-east-2.rds.amazonaws.com port=5432 dbname=postgres user=postgres password=FleetRocks");
		$conn = pg_connect("host=dbclass.cs.unca.edu port=5432 dbname=cmartens user=cmartens password=Pin1tr11Star");
		//$conn = pg_connect("host=avl.cs.unca.edu port=5432 dbname=cmartens user=cmartens password=cmartens");

		if (!$conn){
			echo "An error has occured. 1";
			exit;
		}
		$allCarts = pg_fetch_all_columns(pg_query($conn, "SELECT cart FROM battery_measurements;"));
		if (!$allCarts){
			echo "An error has occured. 2";
			exit;
		}
		//Only change the tables variables, everything grabs data from the tables
		//Currently assuming that the 'cart' column is the key, and removing it
		$graphTables = array("battery_measurements", "battery_percentages");
		$chartTables = array("cart_location");

		$graphingCols = array();
		for ($i = 0; $i < count($graphTables); $i ++){
			$result = pg_query($conn, "SELECT * FROM ".$graphTables[$i]." LIMIT 1;");
			for ($j = 0; $j < pg_num_fields($result); $j ++){
				array_push($graphingCols, pg_field_name($result, $j));
			}
		}
		$graphingCols = array_unique($graphingCols);
		array_splice($graphingCols, array_search("cart", $graphingCols), 1);

		$chartingCols = array();
		for ($i = 0; $i < count($chartTables); $i ++){
			$result = pg_query($conn, "SELECT * FROM ".$chartTables[$i]." LIMIT 1;");
			for ($j = 0; $j < pg_num_fields($result); $j ++){
				array_push($chartingCols, pg_field_name($result, $j));
			}
		}
		$chartingCols = array_unique($chartingCols);
		array_splice($chartingCols, array_search("cart", $chartingCols), 1);
	?>
	<body>
		<table style="width:100%">
			<tr>
				<td style="width:16%">
					<p>Information</p>
					<form id="infoForm" action="Capstone_UI.php" method="get">
						<input type="submit" name="send"><br>
						<?php foreach(array_merge($graphingCols, $chartingCols) as $colName){ ?>
							<input type="checkbox" id="<?php echo $colName ?>" name="<?php echo $colName ?>">
							<label for="<?php echo $colName ?>"><?php echo ucwords(str_replace("_", " ", $colName)); ?></label><br>
						<?php } ?>
						</td>
						<td style="width:10%">
						<p>Carts</p>
						<?php foreach($allCarts as $cartName) { ?>
							<input type="checkbox" id="<?php echo $cartName ?>" name="<?php echo $cartName ?>">
							<label for="<?php echo $cartName ?>"><?php echo ucwords($cartName); ?></label><br>
						<?php } ?>
						</td>
					</form>
				<td>
					<p>Battery</p>
					<?php if ($graphDone == 1){ ?>
						<table>
							<tr>
								<th>Cart</th>
								<?php for ($i = 0; $i < count($graphingData[0]); $i ++){ ?>
									<th><?php echo ucwords(str_replace("_", " ", $graphingData[0][$i])) ?></th>
								<?php } ?>
							</tr>
							<?php for ($i = 0; $i < count($chosenCarts); $i ++){ ?>
								<tr>
									<td><?php echo ucwords($chosenCarts[$i]) ?>
									<?php for ($j = 0; $j < count($graphingData[0]); $j ++){ ?>
										<td><?php echo $graphingData[$j+2][$i] ?>
									<?php } ?>
								</tr>
							<?php } ?>
						</table>
					<?php } ?>
				</td>
				<td>
					<p>Cart</p>
					<?php if ($chartDone == 1){ ?>
						<table>
							<tr>
								<th>Cart</th>
								<?php for ($i = 0; $i < count($chartingData[0]); $i ++){ ?>
									<th><?php echo ucwords(str_replace("_", " ", $chartingData[0][$i])) ?></th>
								<?php } ?>
							</tr>
							<?php for ($i = 0; $i < count($chosenCarts); $i ++){ ?>
								<tr>
									<td><?php echo ucwords($chosenCarts[$i]) ?>
									<?php for ($j = 0; $j < count($chartingData[0]); $j ++){ ?>
										<td><?php echo $chartingData[$j+1][$i] ?>
									<?php } ?>
								</tr>
							<?php } ?>
						</table>
					<?php } ?>
				</td>
			</tr>
		</table>
	</body>
</html>