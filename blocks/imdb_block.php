<?php
begin_block(T_("IMDB_SEARCH")); 
?>
<form method=get action=http://www.imdb.com/find? target=blank>
<center><img src="/images/imdb.png"width="60" height="60"></center>
<center><input type=text name="q" value='' maxlength=30 size=20></center>
<center><img src="/images/blank.gif" width="140" height="5"></center>
<center><input type=submit value=Search></center>
</form>
<?php
end_block();
?>
