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
	<?php echo CCK_Dev::renderLegend( JText::_( 'COM_CCK_CONSTRUCTION' ), JText::_( 'PLG_CCK_FIELD_'.$this->item->type.'_DESC' ) ); ?>
    <ul class="adminformlist adminformlist-2cols">
		<?php
		echo CCK_Dev::renderForm('sd_core_field_concat_list', @$options2['sd_core_field_concat_list'], $config);
		echo CCK_Dev::renderForm('sd_core_field_concat_separator', @$options2['sd_core_field_concat_separator'], $config);
		echo CCK_Dev::renderSpacer( JText::_( 'COM_CCK_STORAGE' ), JText::_( 'COM_CCK_STORAGE_DESC' ) );
		echo CCK_Dev::getForm( 'core_storage', $this->item->storage, $config );
        ?>
    </ul>
</div>
