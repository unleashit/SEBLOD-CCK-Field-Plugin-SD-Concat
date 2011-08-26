<?php
/**
* @version 			Seblod 2.0 Core $Id$
* @package			Seblod (CCK for Joomla)
* @author       	http://www.seblod.com
* @copyright		Copyright (C) 2011 Seblod. All Rights Reserved.
* @license 			GNU General Public License version 2 or later; see _LICENSE.php
**/

// No Direct Access
defined( '_JEXEC' ) or die;

jimport( 'cck.base.install.install' );

// Script Class
class plgCCK_FieldSd_Field_ConcatInstallerScript
{
	protected $cck;
	protected $core;
	
	// install
	function install( $parent )
	{
		if ( $this->core === true ) {
			return;
		}
		
		// Publish
		$db		=	JFactory::getDBO();
		$query	=	'UPDATE #__extensions SET enabled = 1 WHERE type = "'.$this->cck->type.'" AND element = "'.$this->cck->element.'"';
		$query	=	( $this->cck->group ) ? $query.' AND folder = "'.$this->cck->group.'"' : $query;
		$db->setQuery( $query );
		$db->query();
	}
	
	// uninstall
	function uninstall( $parent )
	{
	}
	
	// update
	function update( $parent )
	{
	}
	
	// preflight
	function preflight( $type, $parent )
	{
		$app		=	JFactory::getApplication();
		$this->core	=	$app->get( 'cck_core' );
		if ( $this->core === true ) {
			return;
		}
	
		$this->cck	=	CCK_Install::init( $parent );
	}
	
	// postflight
	function postflight( $type, $parent )
	{
		if ( $this->core === true ) {
			return;
		}
		
		CCK_Install::import( $parent, 'install' );
	}
}
?>