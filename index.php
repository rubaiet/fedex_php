<?php require_once('include/menu.php');?>

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
					<option value="FEDEX_FIRST_FREIGHT">FEDEX FIRST FREIGHT</option>
					<option value="FEDEX_FREIGHT_ECONOMY">FEDEX FREIGHT ECONOMY</option>
					<option value="FEDEX_FREIGHT_PRIORITY">FEDEX FREIGHT PRIORITY</option>
					<option value="FEDEX_GROUND">FEDEX GROUND</option>
					<option value="FEDEX_NEXT_DAY_AFTERNOON">FEDEX NEXT DAY AFTERNOON</option>
					<option value="FEDEX_NEXT_DAY_EARLY_MORNING">FEDEX NEXT DAY EARLY MORNING</option>
					<option value="FEDEX_NEXT_DAY_END_OF_DAY">FEDEX NEXT DAY END OF DAY</option>
					<option value="FEDEX_NEXT_DAY_FREIGHT">FEDEX NEXT DAY FREIGHT</option>
					<option value="FEDEX_NEXT_DAY_MID_MORNING">FEDEX NEXT DAY MID MORNING</option>
					<option value="FIRST_OVERNIGHT">FIRST OVERNIGHT</option>
					<option value="GROUND_HOME_DELIVERY">GROUND HOME DELIVERY</option>
					<option value="INTERNATIONAL_ECONOMY">INTERNATIONAL ECONOMY</option>
					<option value="INTERNATIONAL_ECONOMY_FREIGHT">INTERNATIONAL ECONOMY FREIGHT</option>
					<option value="INTERNATIONAL_FIRST">INTERNATIONAL FIRST</option>
					<option value="INTERNATIONAL_PRIORITY">INTERNATIONAL PRIORITY</option>
					<option value="INTERNATIONAL_PRIORITY_FREIGHT">INTERNATIONAL PRIORITY FREIGHT</option>
					<option value="PRIORITY_OVERNIGHT">PRIORITY OVERNIGHT</option>
					<option value="SAME_DAY">SAME DAY</option>
					<option value="SAME_DAY_CITY">SAME DAY CITY</option>
					<option value="SMART_POST">SMART POST</option>
					<option value="STANDARD_OVERNIGHT">STANDARD OVERNIGHT</option>
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
