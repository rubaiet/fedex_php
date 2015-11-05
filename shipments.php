<?php 
	require_once('include/menu.php');
	require_once('include/db_connect.php');
?>

<h1>All shipments</h1>

<p>Fedex do not permit track for test shipping. How ever we can provide this default traking: <a href="track.php?number=123456789012">Track</a></p>

<?php
	$sql = "SELECT * FROM shipments";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) 
	{
?>
		<table  border="1">
			<tr>
				<td>id</td>
				<td>Package Length (inch)</td>
				<td>Package Width (inch)</td>
				<td>Package Height (inch)</td>
				<td>Package Weight (lb)</td>
				<td>Receiver</td>
				<td>Service Type</td>
				<td>Tracking</td>
				<td>Track Now</td>
			</tr>
			<?php while($row = $result->fetch_assoc()): ?>
				<tr>
					<td><?php echo $row["id"]; ?></td>
					<td><?php echo $row["length"]; ?></td>
					<td><?php echo $row["width"]; ?></td>
					<td><?php echo $row["height"]; ?></td>
					<td><?php echo $row["weight"]; ?></td>
					<td>
						<?php echo $row["receiver"]; ?>
						<br>
						<?php echo $row["receiver_company"]; ?>
						<br>
						<?php echo $row["receiver_phone"]; ?>
						<br>
						<?php echo $row["receiver_address"]; ?>
					</td>
					<td><?php echo $row["service_type"]; ?></td>
					<td><?php echo $row["token"]; ?></td>
					<td><a href="track.php?number=<?php echo $row["token"]; ?>">Track</a></td>
				</tr>
			<?php endwhile;?>
		</table>
<?php
	}
	else 
	{
		echo "0 results";
	}
