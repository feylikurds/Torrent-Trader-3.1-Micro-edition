<?php
//
//  TorrentTrader v2.08
//      $LastChangedDate: 2011-11-01 00:47:02 (Tue, 27 Mar 2012) $
//      $LastChangedBy: BigMax $
//
//      Re-designed by: Nikkbu
//      http://www.torrenttrader.org
//


function textbbcode($form,$name,$content="") {
	//$form = form name
	//$name = textarea name
	//$content = textarea content (only for edit pages etc)
?>
<script type="text/javascript">

function BBTag(tag,s,text,form){
switch(tag)
    {
    case '[quote]':
	var start = document.forms[form].elements[text].selectionStart;
	var end = document.forms[form].elements[text].selectionEnd;
	if (start != end) {
		var body = document.forms[form].elements[text].value;
		var left = body.substring(0, start);
		var middle = "[quote]" + body.substring(start, end) + "[/quote]";
		var rightpos = start + body.substring(start, end).length;
		var right = body.substring(rightpos, end);
		document.forms[form].elements[text].value = left + middle + right;
	} else {
		document.forms[form].elements[text].value = document.forms[form].elements[text].value + "[quote][/quote]";
	}
        break;
    case '[img]':
	var start = document.forms[form].elements[text].selectionStart;
	var end = document.forms[form].elements[text].selectionEnd;
	if (start != end) {
		var body = document.forms[form].elements[text].value;
		var left = body.substring(0, start);
		var middle = "[img]" + body.substring(start, end) + "[/img]";
		var rightpos = start + body.substring(start, end).length;
		var right = body.substring(rightpos, end);
		document.forms[form].elements[text].value = left + middle + right;
	} else {
		document.forms[form].elements[text].value = document.forms[form].elements[text].value + "[img][/img]";
	}
        break;	
    case '[hide]':
	var start = document.forms[form].elements[text].selectionStart;
	var end = document.forms[form].elements[text].selectionEnd;
	if (start != end) {
		var body = document.forms[form].elements[text].value;
		var left = body.substring(0, start);
		var middle = "[hide]" + body.substring(start, end) + "[/hide]";
		var rightpos = start + body.substring(start, end).length;
		var right = body.substring(rightpos, end);
		document.forms[form].elements[text].value = left + middle + right;
	} else {
		document.forms[form].elements[text].value = document.forms[form].elements[text].value + "[hide][/hide]";
	}
        break;
    case '[video]':
	var start = document.forms[form].elements[text].selectionStart;
	var end = document.forms[form].elements[text].selectionEnd;
	if (start != end) {
		var body = document.forms[form].elements[text].value;
		var left = body.substring(0, start);
		var middle = "[img]" + body.substring(start, end) + "[/video]";
		var rightpos = start + body.substring(start, end).length;
		var right = body.substring(rightpos, end);
		document.forms[form].elements[text].value = left + middle + right;
	} else {
		document.forms[form].elements[text].value = document.forms[form].elements[text].value + "[video][/video]";
	}
        break;	
    case '[url]':
	var start = document.forms[form].elements[text].selectionStart;
	var end = document.forms[form].elements[text].selectionEnd;
	if (start != end) {
		var body = document.forms[form].elements[text].value;
		var left = body.substring(0, start);
		var middle = "[url]" + body.substring(start, end) + "[/url]";
		var rightpos = start + body.substring(start, end).length;
		var right = body.substring(rightpos, end);
		document.forms[form].elements[text].value = left + middle + right;
	} else {
		document.forms[form].elements[text].value = document.forms[form].elements[text].value + "[url][/url]";
	}
        break;
    case '[*]':
        document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[*]";
        break;
    case '[b]':
	var start = document.forms[form].elements[text].selectionStart;
	var end = document.forms[form].elements[text].selectionEnd;
	if (start != end) {
		var body = document.forms[form].elements[text].value;
		var left = body.substring(0, start);
		var middle = "[b]" + body.substring(start, end) + "[/b]";
		var rightpos = start + body.substring(start, end).length;
		var right = body.substring(rightpos, end);
		document.forms[form].elements[text].value = left + middle + right;
	} else {
		document.forms[form].elements[text].value = document.forms[form].elements[text].value + "[b][/b]";
	}
        break;
    case '[i]':
	var start = document.forms[form].elements[text].selectionStart;
	var end = document.forms[form].elements[text].selectionEnd;
	if (start != end) {
		var body = document.forms[form].elements[text].value;
		var left = body.substring(0, start);
		var middle = "[i]" + body.substring(start, end) + "[/i]";
		var rightpos = start + body.substring(start, end).length;
		var right = body.substring(rightpos, end);
		document.forms[form].elements[text].value = left + middle + right;
	} else {
		document.forms[form].elements[text].value = document.forms[form].elements[text].value + "[i][/i]";
	}
        break;
    case '[u]':
	var start = document.forms[form].elements[text].selectionStart;
	var end = document.forms[form].elements[text].selectionEnd;
	if (start != end) {
		var body = document.forms[form].elements[text].value;
		var left = body.substring(0, start);
		var middle = "[u]" + body.substring(start, end) + "[/u]";
		var rightpos = start + body.substring(start, end).length;
		var right = body.substring(rightpos, end);
		document.forms[form].elements[text].value = left + middle + right;
	} else {
		document.forms[form].elements[text].value = document.forms[form].elements[text].value + "[u][/u]";
	}
        break;
    }
    document.forms[form].elements[text].focus();
}

</script>
<br />
<table class='f-border' width='750px' border='0' align='center' cellpadding='6' cellspacing='0'>
  <tr class='f-title'>
    <td align='center' valign="middle"><table border="0" align="center" cellpadding="4" cellspacing="0">
        <tr>
          <td align="center"><input style="font-weight: bold;" type="button" name="bold" value="B" onclick="javascript: BBTag('[b]','bold','<?php echo $name; ?>','<?php echo $form; ?>')" /></td>
          <td align="center"><input style="font-style: italic;" type="button" name="italic" value="I" onclick="javascript: BBTag('[i]','italic','<?php echo $name; ?>','<?php echo $form; ?>')" /></td>
          <td align="center"><input style="text-decoration: underline;" type="button" name="underline" value="U" onclick="javascript: BBTag('[u]','underline','<?php echo $name; ?>','<?php echo $form; ?>')" /></td>
          <td align="center"><input type="button" name="li" value="List" onclick="javascript: BBTag('[*]','li','<?php echo $name; ?>','<?php echo $form; ?>')" /></td>
          <td align="center"><input type="button" name="quote" value="QUOTE" onclick="javascript: BBTag('[quote]','quote','<?php echo $name; ?>','<?php echo $form; ?>')" /></td>
          <td align="center"><input type="button" name="url" value="URL" onclick="javascript: BBTag('[url]','url','<?php echo $name; ?>','<?php echo $form; ?>')" /></td>
          <td align="center"><input type="button" name="img" value="IMG" onclick="javascript: BBTag('[img]','img','<?php echo $name; ?>','<?php echo $form; ?>')" /></td>
	          <td align="center"><input type="button" name="hide" value="HIDE" onclick="javascript: BBTag('[hide]','hide','<?php echo $name; ?>','<?php echo $form; ?>')" /></td>
	      <td align="center"><input type="button" name="video" value="VIDEO" onclick="javascript: BBTag('[video]','video','<?php echo $name; ?>','<?php echo $form; ?>')" /></td>
        </tr>
    </table></td>
    <td width="130" align="center"></td>
  </tr>
  <tr>
    <td class='bb-comment f-border' align='center' valign='top'><textarea name="<?php echo $name; ?>" rows="17" cols="70"><?php echo $content; ?></textarea></td>
    <td class='bb-btn f-border' width='130' align="center" valign='top'>
      <table border="0" cellpadding="3" cellspacing="3" align="center">
      <tr>
          <td width="26"><a href="javascript:SmileIT(':-)','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/smile.gif" border="0" alt=':-)' title=':-)' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':-))','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/smiley.gif" border="0" alt=':-))' title=':-))' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':-D','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/grin.gif" border="0" alt=':-D' title=':-D' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':lol:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/lol.gif" border="0" alt=':lol:' title=':lol:' /></a></td>  
      </tr>
      <tr>
          <td width="26"><a href="javascript:SmileIT(':w00t:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/w00t.gif" border="0" alt=':w00t:' title=':w00t:' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':-/','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/confused.gif" border="0" alt=':-/' title=':-/' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':-|','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/noexpression.gif" border="0" alt=':-|' title=':-|' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':-('<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/sad.gif" border="0" alt=':-(' title=':-(' /></a></td>    
      </tr>
	  <tr>
          <td width="26"><a href="javascript:SmileIT(':weep:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/weep.gif" border="0" alt=':weep:' title=':weep:' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':crazy:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/crazy.gif" border="0" alt=':crazy:' title=':crazy:' /></a></td> 
          <td width="26"><a href="javascript:SmileIT(':ilv:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/in-love.gif" border="0" alt=':ilv:' title=':ilv:' /></a></td> 
          <td width="26"><a href="javascript:SmileIT(':-S','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/sarcastic.gif" border="0" alt=':-S' title=':-S' /></a></td> 
      </tr>
      <tr>
          <td width="26"><a href="javascript:SmileIT(':-P','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/ras.gif" border="0" alt=':-P' title=':-P' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':-8)','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/cool.gif" border="0" alt=':-8)' title=':-8)' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':ok:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/ok.gif" border="0" alt=':ok:' title=':ok:' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':bad:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/bad.gif" border="0" alt=':bad:' title=':bad:' /></a></td>
      </tr>
      <tr>
          <td width="26"><a href="javascript:SmileIT(':+y','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/afirmative.gif" border="0" alt=':+y' title=':+y' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':-n','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/negative.gif" border="0" alt=':-n' title=':-n' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':angry:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/angry.gif" border="0" alt=':angry:' title=':angry:' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':evil:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/evil.gif" border="0" alt=':evil:' title=':evil:' /></a></td>
      </tr>
      <tr>
           <td width="26"><a href="javascript:SmileIT(':love:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/love.png" border="0" alt=':love:' title=':love:' /></a></td>
           <td width="26"><a href="javascript:SmileIT(':idea:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/idea.png" border="0" alt=':idea:' title=':idea:' /></a></td>
           <td width="26"><a href="javascript:SmileIT(':quest:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/question.gif" border="0" alt=':quest:' title=':quest:' /></a></td> 
           <td width="26"><a href="javascript:SmileIT(':!:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/important.png" border="0" alt=':!:' title=':!:' /></a></td> 
      </tr>
      <tr>
          <td width="26"><a href="javascript:SmileIT(':fbd:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/forbidden.png" border="0" alt=':fbd:' title=':fbd:' /></a></td>
          <td width="26"><a href="javascript:SmileIT(':warn:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/warn.png" border="0" alt=':warn:' title=':warn:' /></a></td> 
          <td width="26"><a href="javascript:SmileIT(':dis:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/disable.gif" border="0" alt=':dis:' title=':dis:' /></a></td> 
          <td width="26"><a href="javascript:SmileIT(':bomb:','<?php echo $form; ?>','<?php echo $name; ?>')"><img src="images/smilies/bomb.png" border="0" alt=':bomb:' title=':bomb:' /></a></td> 
      </tr>
      </table>
	  
      <div style="margin-top:15px"><a href="javascript:PopMoreSmiles('<?php echo $form; ?>','<?php echo $name; ?>');"><?php echo "[".T_("MORE_SMILIES")."]";?></a></div>
      
      <div style="margin-top:7px"><a href="javascript:PopMoreTags();"><?php echo "[".T_("MORE_TAGS")."]";?></a></div>
	  
    </td>
  </tr>
</table>
<br />
<?php
}
?>
