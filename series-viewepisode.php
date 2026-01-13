<?php
	require_once("backend/functions.php");
	dbconn(false);
	
	$episodeid = $_GET['id'];
	$result = SQL_Query_exec("select name,number,season,gueststars,overview,firstaired from episodes where tvdbid=$episodeid");
	$row=mysqli_fetch_array($result);
	$result2 = SQL_Query_exec("select series.name as series, series.tvdbid as sid, banner from series join episodes on episodes.seriesid = series.tvdbid where episodes.tvdbid=$episodeid");
	$row2=mysqli_fetch_array($result2);
	
	stdhead("View Episode Information: ".$row2['series']);
	begin_frame($row2['series']." : ".$row['name']);
?>
	<center><img src="series/banners/<?=$row2['banner']?>"/></center>
	<table>
		<tr>
			<td><b>Show:</b></td>
			<td><?=$row2['series']?></td>
		</tr>
		<tr>
			<td><b>Episode Name:</b></td>
			<td><?=$row['name']?></td>
		</tr>
		<tr>
			<td><b>Season:</b></td>
			<td><?=$row['season']?></td>
		</tr>
		<tr>
			<td><b>Episode Nr:</b></td>
			<td><?=$row['number']?></td>
		</tr>
		<tr>
			<td><b>Guest Stars</b></td>
			<td><?=$row['gueststars']?></td>
		</tr>
		<tr>
			<td><b>Overview</b></td>
			<td><?=$row['overview']?></td>
		</tr>
		<tr>
			<td><b>Air Date</b></td>
			<td><?=$row['firstaired']?></td>
		</tr>
	</table><br>
		<a href="series-view.php?id=<?=$row2['sid']?>">Back to <?=$row2['series']?></a><br>
<?php
	end_frame();
	stdfoot();
?>