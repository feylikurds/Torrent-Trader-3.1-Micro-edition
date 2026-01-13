
                    </TD>
                    <!-- END MAIN CONTENT AREA -->
                    <?php if ($site_config["RIGHTNAV"]){ ?>
                    <!-- RIGHT COLUMN -->
                    <TD vAlign="top" width="180"><?php rightblocks(); ?>
                    </TD>
                    <!-- END RIGHT COLUMN -->
                    <?php } //RIGHTNAV ON/OFF END ?>
                  </TR>
              </TABLE></td>
          </tr>
      </table>
      <table id="footer" width="100%" border="0" cellspacing="0" cellpadding="5">
        <tr>
          <td align="center">
			<?php if ($CURUSER){
            //
            // *************************************************************************************************************************************
            //			PLEASE DO NOT REMOVE THE POWERED BY LINE, SHOW SOME SUPPORT! WE WILL NOT SUPPORT ANYONE WHO HAS THIS LINE EDITED OR REMOVED!
            // *************************************************************************************************************************************
            printf (T_("POWERED_BY_TT")." -|- ", $site_config["ttversion"]);
            $totaltime = array_sum(explode(" ", microtime(true))) - $GLOBALS['tstart'];
            printf(T_("PAGE_GENERATED_IN"), $totaltime);
            print ("<br /><a href='rss.php' target=\"_blank\"><img src='".$site_config["SITEURL"]."/images/icon_rss.gif' border='0' width='13' height='13' alt='' /></a> -|- <a href='rss.php' target=\"_blank\">".T_("RSS_FEED")."</a> -|- <a href='rss.php?custom=1'>".T_("FEED_INFO")."");
            //
            // *************************************************************************************************************************************
            //			PLEASE DO NOT REMOVE THE POWERED BY LINE, SHOW SOME SUPPORT! WE WILL NOT SUPPORT ANYONE WHO HAS THIS LINE EDITED OR REMOVED!
            // *************************************************************************************************************************************
            
			}?>
          </td>
        </tr>
      </table>
      <br /></td>
    <td class="NB-right"><img src="themes/NB-41/images/blank.gif" width="14" height="200" /></td>
  </tr>
</table>
<script src="<?php echo $site_config["SITEURL"];?>/scripts/anon.js" type="text/javascript"></script>
<script type="text/javascript"><!--
protected_links = "<?php echo $site_config["SITEURL"];?>";

auto_anonymize();
//--></script>
</body>
</html>
