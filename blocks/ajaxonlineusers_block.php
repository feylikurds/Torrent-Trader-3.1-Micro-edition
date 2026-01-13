<?php

///////////AJAX online users/////////////////
     if(isset($CURUSER) && $CURUSER['id']>0){

//USERS ONLINE

begin_frame("Users Online");

?>



<script type="text/javascript">

refreshdiv_uldiv();

</script>

<table align=center width=100%><tr>

<td align=left>

<div name="uldiv" id="uldiv"></div>

</td>

</tr>

</table>

<?php

end_frame();

}

///////////END AJAX online users/////////////
?>