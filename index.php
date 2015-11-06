<?php require_once('library/menu.php');?>

<h1>Calculate Rate</h1>
<form method="post" action="rate.php">
	<table>
		<tr>
			<td>Package Length (inch)</td>
			<td><input type="text" required name="Length"></td>
		</tr>
		<tr>
			<td>Package Width (inch)</td>
			<td><input type="text" required name="Width"></td>
		</tr>
		<tr>
			<td>Package Height (inch)</td>
			<td><input type="text" required name="Height"></td>
		</tr>
		<tr>
			<td>Package Weight (lb)</td>
			<td><input type="text" required name="Weight"></td>
		</tr>
		<tr>
			<td>Receiver Name</td>
			<td><input type="text" required name="PersonName"></td>
		</tr>
		<tr>
			<td>Receiver Company</td>
			<td><input type="text" required name="CompanyName"></td>
		</tr>
		<tr>
			<td>Receiver Phone</td>
			<td><input type="text" required name="PhoneNumber"></td>
		</tr>
		<tr>
			<td>Receiver Address</td>
			<td><input type="text" required value="Address Line 1" name="StreetLines"></td>
		</tr>
		<tr>
			<td>Receiver City</td>
			<td><input type="text" required name="City" value="Herndon"></td>
		</tr>
		<tr>
			<td>Receiver State</td>
			<td><input type="text" required name="StateOrProvinceCode" value="VA"></td>
		</tr>
		<tr>
			<td>Receiver Zip</td>
			<td><input type="text" required name="PostalCode" value="20171"></td>
		</tr>
		<tr>
			<td>Receiver Country</td>
			<td><input type="text" required name="CountryCode" value="US"></td>
		</tr>
		<tr>
			<td>Service Type</td>
			<td>
				<select required name="ServiceType">
					<option value="STANDARD_OVERNIGHT">STANDARD_OVERNIGHT</option>
					<option value="PRIORITY_OVERNIGHT">PRIORITY_OVERNIGHT</option>
					<option value="FEDEX_GROUND">FEDEX_GROUND</option>
					<option value="FEDEX_FREIGHT_ECONOMY">FEDEX_FREIGHT_ECONOMY</option>
					<option value="INTERNATIONAL_PRIORITY">INTERNATIONAL_PRIORITY</option>
					<option value="SMART_POST">SMART_POST</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" name="submit">
			</td>
		</tr>

	</table>
</form>
