<?php
	require_once("backend/functions.php");
	dbconn(false);
	
	$seriesid = $_GET['id'];
	$result = SQL_Query_exec("select name,overview,banner,firstaired,banner,dayofweek,airtime,genre,status,rating from series where tvdbid=$seriesid");
	$row=mysqli_fetch_array($result);
	$result2 = SQL_Query_exec("select season from episodes where seriesid=$seriesid group by season");
	
	stdhead("View Series Information: ".$row['name']);
	begin_frame($row['name']." Information");
?>
	<center><img src="series/banners/<?=$row['banner']?>"/></center>
	<table>
		<tr>
			<td><b>Show Name:</b></td>
			<td><?=$row['name']?></td>
		</tr>
		<tr>
			<td><b>Overview</b></td>
			<td><?=$row['overview']?></td>
		</tr>
		<tr>
			<td><b>Genre</b></td>
			<td><?=$row['genre']?></td>
		</tr>
		<tr>
			<td><b>First Aired</b></td>
			<td><?=$row['firstaired']?></td>
		</tr>
		<tr>
			<td><b>Air Day Of Week</b></td>
			<td><?=$row['dayofweek']?></td>
		</tr>
		<tr>
			<td><b>Air Time</b></td>
			<td><?=$row['airtime']?></td>
		</tr>
		<tr>
			<td><b>Status</b></td>
			<td><?=$row['status']?></td>
		</tr>
		<tr>
			<td><b>Rating</b></td>
			<td><?=$row['rating']?></td>
		</tr>
	</table>
	<h1 style="font-size:20px;">Seasons</h1>
<?php
	while($row2=mysqli_fetch_array($result2)) {
		if($row2['season'] == 0)
			$season = "Special";
		else
			$season = $row2['season'];
		print "<a href=\"series-viewseason.php?id=".$seriesid."&season=".$row2['season']."\" style=\"font-weight:bold; font-size: 14px;\">$season</a>&nbsp;&nbsp;";
	}
	print "<a href=\"series-viewseason.php?id=".$seriesid."\" style=\"font-weight:bold; font-size: 14px;\">All</a>";
	print "<br><br>";
	print "<a href=\"series.php\">Back to Series Index</a>";
	end_frame();
	stdfoot();
?>