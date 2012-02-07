<?php
/**
* @version 			Seblod 2.0 More $Revision: 12 $
* @package			Seblod (CCK for Joomla)
* @author       	http://www.seblod.com
* @copyright		Copyright (C) 2011 Seblod. All Rights Reserved.
* @license 			GNU General Public License version 2 or later; see _LICENSE.php
**/

// No Direct Access
defined( '_JEXEC' ) or die;
?>

<?php
$options2	=	CCK_Dev::fromJSON( $this->item->options2 );
//$config['doTranslation'] = 0;
?>

<div class="seblod">
	<?php
	$SdConcatDonate = "<div style=\"overflow:hidden;clear:both;padding:10px;margin:0 0 15px 0;border:1px dotted #666;background:#e1ebf2;\"><h6 style=\"font-size:14px;padding:0;margin:0 0 15px 0;color:#444;text-shadow:1px 1px 1px #fff;\">PLEASE SUPPORT FURTHER DEVELOPMENT BY DONATING SOMETHING SMALL</h6>";
	$SdConcatDonate.= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TCGRYXG5Y8GY6&lc=ZA&item_name=Simon%20Dowdles&item_number=sd_field_concat&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted" title="Donate Something" target="_blank" style="display:block;float:left;clear:both;padding:10px 12px;background:#4a98d3;color:#fff;text-align:center;font-weight:bold;-moz-border-radius:8px;-webkit-border-radius:8px;border-radius:8px;text-shadow:0 0 1px #2d80c1;font-size:12px;">Donate Via PayPal</a>';
	$SdConcatDonate.= "</div>";
	echo $SdConcatDonate;
	?>
	<?php echo CCK_Dev::renderLegend( JText::_( 'COM_CCK_CONSTRUCTION' ), JText::_( 'PLG_CCK_FIELD_'.$this->item->type.'_DESC' ) ); ?>
	<ul class="adminformlist adminformlist-2cols">
		<?php
		echo CCK_Dev::renderForm('sd_core_field_concat_list', @$options2['sd_core_field_concat_list'], $config);
		echo CCK_Dev::renderForm('sd_core_field_concat_separator', @$options2['sd_core_field_concat_separator'], $config);
		echo CCK_Dev::renderForm('sd_core_field_concat_date_format', @$options2['sd_core_field_concat_date_format'], $config);
		echo CCK_Dev::renderForm('sd_core_field_concat_enable_jtext', @$options2['sd_core_field_concat_enable_jtext'], $config);
		echo CCK_Dev::renderForm('sd_core_field_concat_text_value', @$options2['sd_core_field_concat_text_value'], $config);
		echo CCK_Dev::renderSpacer( JText::_( 'COM_CCK_STORAGE' ), JText::_( 'COM_CCK_STORAGE_DESC' ) );
		echo CCK_Dev::getForm( 'core_storage', $this->item->storage, $config );
        ?>
    </ul>
</div>
