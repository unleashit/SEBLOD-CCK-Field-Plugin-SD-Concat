<?php
// No Direct Access
defined( '_JEXEC' ) or die;

jimport( 'joomla.plugin.plugin' );
jimport( 'cck.construction.field.generic' );
jimport( 'cck.rendering.rendering' );
//jimport( 'cck.development.field' );

// Plugin Class
class plgCCK_FieldSd_Field_Concat extends plgCCK_FieldGeneric
{
	protected static $type	=	'sd_field_concat';
	protected static $path;
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Construct
	
	// onCCK_FieldConstruct
	public function onCCK_FieldConstruct( $type, &$data = array() )
	{
		if ( self::$type != $type ) {
			return;
		}
		parent::g_onCCK_FieldConstruct( $data );
	}
	

	// onCCK_FieldPrepareForm
	public function onCCK_FieldPrepareForm( &$field, $value = '', &$config = array(), $inherit = array(), $return = false )
	{	
		
		if ( self::$type != $field->type ) {
			return;
		}

		parent::g_onCCK_FieldPrepareForm( $field, $config );
		
		// Init
		if ( count( $inherit ) ) {
			$id		=	( @$inherit['id'] != '' ) ? $inherit['id'] : $field->name;
			$name	=	( @$inherit['name'] != '' ) ? $inherit['name'] : $field->name;
		} else {
			$id		=	$field->name;
			$name	=	$field->name;
		}
		$value		=	( $value != '' ) ? $value : $field->defaultvalue;
		$value		=	( $value != ' ' ) ? $value : '';
		$value		=	htmlspecialchars( $value );
		
		$options2	=	CCK_Dev::fromJSON( $field->options2 );
				
		// Validate
		$validate	=	'';
		if ( $config['doValidation'] > 1 ) {
			plgCCK_Field_ValidationRequired::onCCK_Field_ValidationPrepareForm( $field, $id, $config );
			parent::g_onCCK_FieldPrepareForm_Validation( $field, $id, $config );
			if ( $field->minlength > 0 ) {
				$field->validate[]	=	'minSize['.$field->minlength.']';
			}
			$validate	=	( count( $field->validate ) ) ? ' validate['.implode( ',', $field->validate ).']' : '';
		}
		
		// Prepare
		
		// Set
		if ( ! $field->variation ) {
			$field->form	=	$form;
			if ( $field->script ) {
				parent::g_addScriptDeclaration( $field->script );
			}
		} else {
			parent::g_getDisplayVariation( $field, $field->variation, $value, $value, $form, $id, $name, '<input' );
		}
		$field->value	=	$value;
		
		// Return
		if ( $return === true ) {
			return $field;
		}
	}
	
	// onCCK_FieldPrepareStore
	public function onCCK_FieldPrepareStore( &$field, $value = '', &$config = array(), $inherit = array(), $return = false )
	{
		if ( self::$type != $field->type ) {
			return;
		}
		
		#############################################################
		// SD FIELD CONCAT - SIMON DOWDLES - ONESTUCKPIXEL.COM
		#############################################################
		
		$user = JFactory::getUser();
		$options2 = CCK_Dev::fromJSON( $field->options2 );
		$sdFieldList = @$options2['sd_core_field_concat_list'];
		$sdFieldSeparator = (@$options2['sd_core_field_concat_separator'] > '' ? @$options2['sd_core_field_concat_separator'] : ' ');
		$sdFieldList = ( preg_match("^[,]^",$sdFieldList) ? explode(",",$sdFieldList) : $sdFieldList );
		
		if(is_array($sdFieldList)):
			
			$sdConcatValue = '';
			$sdNumFields = count($sdFieldList);
			$sdNumStep = 0;
			
			foreach($sdFieldList as $sdField):
				$sdNumStep++;
				$sdField = trim($sdField);
				if(preg_match("^[\[\]]^", $sdField)):
					$sdField = str_replace(array('[',']'), array('',''), $sdField);
					($sdField > '' ? $sdConcatValue .= $sdField.($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
				elseif(preg_match("^[\$]^", $sdField)):
					$sdFind = array('$date', '$time', '$username', '$userid');
					$sdReplace = array(date('Y-m-d'), date('H:m:s'), ($user->username > '' ? $user->username : ''), ($user->id > '' ? $user->id : ''));
					$sdField = str_replace($sdFind, $sdReplace, $sdField);
					($sdField > '' ? $sdConcatValue .= $sdField.($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
				elseif(preg_match("^[#(.*)#]^", $sdField)):
					$sdField = trim(str_replace('#','',$sdField));
					($sdField > '' ? $sdConcatValue .= trim($config['post'][$sdField]).($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
				endif;
			endforeach;

		else:
			null;
		endif;

		$value = $sdConcatValue;
		
		##############################################################
		// END SD FIELD CONCAT - SIMON DOWDLES - ONESTUCKPIXEL.COM
		##############################################################
		
		// Init
		if ( count( $inherit ) ) {
			$name	=	( @$inherit['name'] != '' ) ? $inherit['name'] : $field->name;
		} else {
			$name	=	$field->name;
		}
		
		// Validate
		parent::g_onCCK_FieldPrepareStore_Validation( $field, $name, $value, $config );
		
		// Set or Return
		if ( $return === true ) {
			return $value;
		}
		$field->value = $value;
		
		parent::g_onCCK_FieldPrepareStore( $field, $name, $value, $config );
	
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Render
	
	// onCCK_FieldRenderContent
	public static function onCCK_FieldRenderContent( $field, &$config = array() )
	{
		return parent::g_onCCK_FieldRenderContent( $field, 'html' );
	}
	
	// onCCK_FieldRenderForm
	public static function onCCK_FieldRenderForm( $field, &$config = array() )
	{		
		return parent::g_onCCK_FieldRenderForm( $field );
	}

}

?>