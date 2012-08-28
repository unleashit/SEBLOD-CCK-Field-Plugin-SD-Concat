<?php
/*
 * @Name		SD Field Concat
 * @Author		Simon Dowdles
 * @Website		www.onestuckpixel.com
 *
 **/

// No Direct Access
defined( '_JEXEC' ) or die;

//jimport( 'joomla.plugin.plugin' );
//jimport( 'cck.construction.field.generic' );

// Plugin Class
class plgCCK_FieldSd_Field_Concat extends JCckPluginField
{
	protected static $type	=	'sd_field_concat';
	protected static $friendly	=	1;
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
	
	// onCCK_FieldPrepareContent
	public function onCCK_FieldPrepareContent( &$field, $value = '', &$config = array() )
	{
		if ( self::$type != $field->type ) {
			return;
		}
		parent::g_onCCK_FieldPrepareContent( $field, $config );
		
		$field->value	=	$value;
	}
	
	// onCCK_FieldPrepareForm
	public function onCCK_FieldPrepareForm( &$field, $value = '', &$config = array(), $inherit = array(), $return = false )
	{		
		if ( self::$type != $field->type ) {
			return;
		}
		self::$path	=	parent::g_getPath( self::$type.'/' );
		parent::g_onCCK_FieldPrepareForm( $field, $config );
		
		// Init
		if ( count( $inherit ) ) {
			$id		=	( @$inherit['id'] != '' ) ? $inherit['id'] : $field->name;
			$name	=	( @$inherit['name'] != '' ) ? $inherit['name'] : $field->name;
		} else {
			$id		=	$field->name;
			$name	=	$field->name;
		}
		$value	=	( $value != '' ) ? $value : $field->defaultvalue;
		$value	=	( $value != ' ' ) ? $value : '';
		$value	=	htmlspecialchars( $value, ENT_QUOTES );
		
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
		//$class	=	'textarea text'.$validate.' '.$field->css;
		//$form	=	'<input style="padding:10px;border:1px dotted #ccc;color:#666;width:250px;" id="'.$id.'" value="'.$value.'" />';
		$form = '';
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
		
		#################################################################
		// SD FIELD CONCAT - SIMON DOWDLES - http://www.simondowdles.com
		#################################################################
		
		$user = JFactory::getUser();
		$options2 = JCckDev::fromJSON( $field->options2 );
		$sdFieldList = @$options2['sd_core_field_concat_list'];
		$processOnEdit = @$options2['sd_core_field_concat_process_edit'];
		$isNew = $config['id'] == 0 ? true : false;
		if(!$isNew && !$processOnEdit){
			return;
		}
		$SdJtext = @$options2['sd_core_field_concat_enable_jtext'];
		$SdUserDateFormat = @$options2['sd_core_field_concat_date_format'];
		$SdDateFormat = ($SdUserDateFormat !== '' ? $SdUserDateFormat : 'Y-m-d');
		$sdFieldSeparator = (@$options2['sd_core_field_concat_separator'] > '' ? @$options2['sd_core_field_concat_separator'] : ' ');
		$sdFieldSeparator = ($sdFieldSeparator == 'ns' ? '' : $sdFieldSeparator);
		$sdFieldList = ( preg_match("^[||]^",$sdFieldList) ? explode("||",$sdFieldList) : $sdFieldList );
		
		$sdFieldTextValue = (@$options2['sd_core_field_concat_text_value'] > '' ? @$options2['sd_core_field_concat_text_value'] : 'v');
		
		if(is_array($sdFieldList)):
			
			$sdConcatValue = '';
			$sdNumFields = count($sdFieldList);
			$sdNumStep = 0;
	
			foreach($sdFieldList as $sdField):
				$sdNumStep++;
				$sdField = trim($sdField);
				
				if(preg_match("^<(.*?)>^", $sdField, $matches)):
				$aitable = $matches[1];
				/* Next Auto Increment */
				$sebid = $config['pk'];
				if(!$sebid > 0){	// Are we creating a new article? Yes

					$inifile = dirname(__FILE__).DS.'increments.ini';
					if(JFile::exists($inifile)){
						chmod($inifile, 0644);
							$ini = parse_ini_file($inifile);
							$db = JFactory::getDbo();
							$pfx = $db->getPrefix();
							// get auto increment of users desired table
							$db->setQuery("SHOW TABLE STATUS LIKE '".$pfx.$aitable."'");
							$db->query();
							$dbresult = $db->loadAssoc();
							$ai = $dbresult['Auto_increment'];
							// get auto increment of content table for this ID
							$db->setQuery("SHOW TABLE STATUS LIKE '".$pfx."content'");
							$db->query();
							$dbresult = $db->loadAssoc();
							$artid = $dbresult['Auto_increment'];
							$ini[$artid] = $ai;
							$fopen = fopen($inifile, 'w');
							$ini_in = "";
							foreach($ini as $key=>$val){
								$ini_in .= $key."=".$val."\r\n";
							}
							fwrite($fopen, $ini_in);
							fclose($fopen);						
					};
				}else{ // We are updating an article
					if($aitable > ''){
						$sebid = $config['pk'];
						$db = JFactory::getDbo();
						$pfx = $db->getPrefix();
						$db->setQuery("SHOW TABLE STATUS LIKE '".$pfx.$aitable."'");
						$db->query();
						$dbresult = $db->loadAssoc();
						$ai = $dbresult['Auto_increment'];
						$db->setQuery("SHOW TABLE STATUS LIKE '".$pfx."content'");
						$db->query();
						$dbresult = $db->loadAssoc();
						$content_ai = $dbresult['Auto_increment'];
						$inifile = dirname(__FILE__).DS.'increments.ini';
						if(JFile::exists($inifile)){
							chmod($inifile, 0644);
							$ini = parse_ini_file($inifile); //get ini values as array
							if(array_key_exists($sebid, $ini)){
								if($ini[$sebid] > '' && !$ini[$sebid] == 0){
									$ai = $ini[$sebid];
								}else{
									$ini_in = "";
									if(!is_array($ini)){
										$ini = array();
									}
									$ini[$sebid] = $ai; //$ai for next auto increment
									foreach($ini as $key=>$value){
										$ini_in .= $key."=".$value."\r\n";
									}
									$fopen = fopen($inifile, 'w');
									fwrite($fopen, $ini_in);
									fclose($fopen);	
								}
							}else{
								$ini_in = "";
								$ini[$sebid] = $ai; //$ai for next auto increment
								foreach($ini as $key=>$value){
									$ini_in .= $key."=".$value."\r\n";
								}
								$fopen = fopen($inifile, 'w');
								fwrite($fopen, $ini_in);
								fclose($fopen);	
							}
						};
						
					}else{
						$ai = "";
					};
				};
				
				$sdField = $ai;
				($sdField > '' ? $sdConcatValue .= $sdField.($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
				
				/* End Next Auto Increment */
				
				elseif(preg_match("^[\[\]]^", $sdField)):
					$sdField = str_replace(array('[',']'), array('',''), $sdField);
					$sdField = ($SdJtext == 1 ? JText::_('PLG_CCK_FIELD_SD_FIELD_CONCAT_'.str_replace(array(';',':','`','~','}','{','>','<','!','=',',','-'),'',trim(strtoupper(str_replace(' ', '_', $sdField))))) : $sdField);
					($sdField > '' ? $sdConcatValue .= $sdField.($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
				elseif(preg_match("^[\$]^", $sdField)):
					$sdFind = array('$date', '$time', '$username', '$userid');
					$sdReplace = array(date($SdDateFormat), date('H:m:s'), ($user->username > '' ? $user->username : ''), ($user->id > '' ? $user->id : ''));
					$sdField = str_replace($sdFind, $sdReplace, $sdField);
					($sdField > '' ? $sdConcatValue .= $sdField.($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
				elseif(preg_match("^[#(.*)#]^", $sdField)):
					$sdField = trim(str_replace('#','',$sdField));
					$sdFieldTmp = JCckDevField::getObject($sdField);
					if($sdFieldTmp->type == 'select_simple' || $sdFieldTmp->type == 'select_dynamic' || $sdFieldTmp->type == 'select_dynamic_cascade' || ($sdFieldTmp->type == 'radio' && !is_array($config['post'][$sdField])) || ($sdFieldTmp->type == 'checkbox' && !is_array($config['post'][$sdField])) || $sdFieldTmp->type == 'select_dynamic_cascade'){
						
						switch($sdFieldTextValue){
								case 't':
									// Le select dynamic
									if($sdFieldTmp->type == 'select_dynamic' || $sdFieldTmp->type == 'select_dynamic_cascade'){
										// JSON > Array
										$fieldOpt2 = JCckDev::fromJSON($sdFieldTmp->options2);
										// Set Query to get select dynamic criteria (thanks Lionel!)
										$query = 'SELECT '.$fieldOpt2['name'].' FROM '.$fieldOpt2['table'].' WHERE '.$fieldOpt2['value'].'="'.$config['post'][$sdField].'"';
										// Get database object as string (thanks SEBLOD devs!)
										$dynamicText =  JCckDatabase::loadResult($query);
										if(trim($dynamicText) > ''):
											$sdConcatValue .= $dynamicText;
											$sdConcatValue .= ($sdNumStep < $sdNumFields ? $sdFieldSeparator : null);
										endif;
										$sdFieldTmp = '';
									}else{ // if not dynamic
										$sdFieldTmp = parent::g_getOptionText( trim($config['post'][$sdField]), $sdFieldTmp->options, '', $config );
										if(trim($sdFieldTmp) > ''):
											$sdConcatValue .= $sdFieldTmp;
											$sdConcatValue .= ($sdNumStep < $sdNumFields ? $sdFieldSeparator : null);
										endif;
										$sdFieldTmp = '';
									};
								break;
								
								case 'v':
									// Le select dynamic
									if($sdFieldTmp->type == 'select_dynamic' || $sdFieldTmp->type == 'select_dynamic_cascade'){
										// JSON > Array
										$fieldOpt2 = JCckDev::fromJSON($sdFieldTmp->options2);
										// Set Query to get select dynamic criteria (thanks Lionel!)
										$query = 'SELECT '.$fieldOpt2['value'].' FROM '.$fieldOpt2['table'].' WHERE '.$fieldOpt2['value'].'="'.$config['post'][$sdField].'"';
										// Get database object as string (thanks SEBLOD devs!)
										$dynamicText =  JCckDatabase::loadResult($query);
										if(trim($dynamicText) > ''):
											$sdConcatValue .= $dynamicText;
											$sdConcatValue .= ($sdNumStep < $sdNumFields ? $sdFieldSeparator : null);
										endif;
										$sdFieldTmp = '';
									}else{
										($sdField > '' && trim($config['post'][$sdField]) > '' ? $sdConcatValue .= trim($config['post'][$sdField]).($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
										$sdFieldTmp = '';
									}
								break;
								
								default:
									($sdField > '' && trim($config['post'][$sdField]) > '' ? $sdConcatValue .= trim($config['post'][$sdField]).($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
									$sdFieldTmp = '';
								break;
							};

						}elseif($sdFieldTmp->type == 'select_multiple' || ($sdFieldTmp->type == 'checkbox' && is_array($config['post'][$sdField])) || ($sdFieldTmp->type == 'radio' && is_array($config['post'][$sdField]))){
							switch($sdFieldTextValue){
								case 't':
									$SdValHolder = "";
									$SdArrayCount = count($config['post'][$sdField]);
									$s = 0;
									foreach($config['post'][$sdField] as $selectOption){
										$s++;
										$tempsep = "";
										$sdFieldSeparator == "ns" || $sdFieldSeparator == "" ? $tempsep = " " : $tempsep = $sdFieldSeparator;
										$SdValHolder .= parent::g_getOptionText( trim($selectOption), $sdFieldTmp->options, '', $config ).($s !== $SdArrayCount ? $tempsep : NULL);
									}
									if(str_replace(' ','', $SdValHolder) > ''):
										$sdConcatValue .= $SdValHolder;
									endif;
									$SdValHolder = '';
								break;
								
								case 'v':
									$SdValHolder = "";
									$SdValHolder = "";
									$SdArrayCount = count($config['post'][$sdField]);
									$s = 0;
									foreach($config['post'][$sdField] as $selectOption){
										$s++;
										$tempsep = "";
										$sdFieldSeparator == "ns" || $sdFieldSeparator == "" ? $tempsep = " " : $tempsep = $sdFieldSeparator;
										$SdValHolder .= trim($selectOption).($s !== $SdArrayCount ? $tempsep : NULL);
									}
									if(str_replace(' ','', $SdValHolder) > ''):
										$sdConcatValue .= $SdValHolder;
									endif;
									$SdValHolder = '';
								break;
								
								default:
									//($sdField > '' ? $sdConcatValue .= trim($config['post'][$sdField]).($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
								break;
							};
						}else{
						($sdField > '' && trim($config['post'][$sdField]) > '' ? $sdConcatValue .= trim($config['post'][$sdField]).($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
					};				
				endif;
			endforeach;
		else: 
			/* SINGLE FIELDS*/
				$sdConcatValue = '';
				$sdNumFields = 1;
				$sdNumStep = 0;
				$sdField = trim($sdFieldList);
				if(preg_match("^<(.*?)>^", $sdField, $matches)):
				$aitable = $matches[1];
				/* Next Auto Increment */
				$sebid = $config['pk'];
				if(!$sebid > 0){	// Are we creating a new article? Yes
				echo "we are add new, ";
					$inifile = dirname(__FILE__).DS.'increments.ini';
					if(JFile::exists($inifile)){
						chmod($inifile, 0644);
							$ini = parse_ini_file($inifile);
							$db = JFactory::getDbo();
							$pfx = $db->getPrefix();
							// get auto increment of users desired table
							$db->setQuery("SHOW TABLE STATUS LIKE '".$pfx.$aitable."'");
							$db->query();
							$dbresult = $db->loadAssoc();
							$ai = $dbresult['Auto_increment'];
							// get auto increment of content table for this ID
							$db->setQuery("SHOW TABLE STATUS LIKE '".$pfx."content'");
							$db->query();
							$dbresult = $db->loadAssoc();
							$artid = $dbresult['Auto_increment'];
							$ini[$artid] = $ai;
							$fopen = fopen($inifile, 'w');
							$ini_in = "";
							foreach($ini as $key=>$val){
								$ini_in .= $key."=".$val."\r\n";
							}
							fwrite($fopen, $ini_in);
							fclose($fopen);						
					};
				}else{ // We are updating an article
					if($aitable > ''){
						$sebid = $config['pk'];
						$db = JFactory::getDbo();
						$pfx = $db->getPrefix();
						$db->setQuery("SHOW TABLE STATUS LIKE '".$pfx.$aitable."'");
						$db->query();
						$dbresult = $db->loadAssoc();
						$ai = $dbresult['Auto_increment'];
						$db->setQuery("SHOW TABLE STATUS LIKE '".$pfx."content'");
						$db->query();
						$dbresult = $db->loadAssoc();
						$content_ai = $dbresult['Auto_increment'];
						$inifile = dirname(__FILE__).DS.'increments.ini';
						if(JFile::exists($inifile)){
							chmod($inifile, 0644);
							$ini = parse_ini_file($inifile); //get ini values as array
							if(array_key_exists($sebid, $ini)){
								if($ini[$sebid] > '' && !$ini[$sebid] == 0){
									$ai = $ini[$sebid];
								}else{
									$ini_in = "";
									if(!is_array($ini)){
										$ini = array();
									}
									$ini[$sebid] = $ai; //$ai for next auto increment
									foreach($ini as $key=>$value){
										$ini_in .= $key."=".$value."\r\n";
									}
									$fopen = fopen($inifile, 'w');
									fwrite($fopen, $ini_in);
									fclose($fopen);	
								}
							}else{
								$ini_in = "";
								$ini[$sebid] = $ai; //$ai for next auto increment
								foreach($ini as $key=>$value){
									$ini_in .= $key."=".$value."\r\n";
								}
								$fopen = fopen($inifile, 'w');
								fwrite($fopen, $ini_in);
								fclose($fopen);	
							}
						};
						
					}else{
						$ai = "";
					};
				};
				
				$sdField = $ai;
				($sdField > '' ? $sdConcatValue .= $sdField.($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
				/* End Next Auto Increment */
				elseif(preg_match("^[\[\]]^",$sdField )):
					$sdField = str_replace(array('[',']'), array('',''), $sdField );
					$sdField = ($SdJtext == 1 ? JText::_('PLG_CCK_FIELD_SD_FIELD_CONCAT_'.str_replace(array(';',':','`','~','}','{','>','<','!','=',',','-'),'',trim(strtoupper(str_replace(' ', '_', $sdField))))) : $sdField);
					($sdField > '' ? $sdConcatValue .= $sdField.($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
				elseif(preg_match("^[\$]^", $sdField)):
					$sdFind = array('$date', '$time', '$username', '$userid');
					$sdReplace = array(date($SdDateFormat), date('H:m:s'), ($user->username > '' ? $user->username : ''), ($user->id > '' ? $user->id : ''));
					$sdField = str_replace($sdFind, $sdReplace, $sdField);
					($sdField > '' ? $sdConcatValue .= $sdField.($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
				elseif(preg_match("^[#(.*)#]^", $sdField)):
					$sdField = trim(str_replace('#','',$sdField));
					$sdFieldTmp = JCckDevField::getObject($sdField);
					if($sdFieldTmp->type == 'select_simple' || $sdFieldTmp->type == 'select_dynamic' || ($sdFieldTmp->type == 'radio' && !is_array($config['post'][$sdField])) || ($sdFieldTmp->type == 'checkbox' && !is_array($config['post'][$sdField])) || $sdFieldTmp->type == 'select_dynamic_cascade'){
							switch($sdFieldTextValue){
								case 't':
									if($sdFieldTmp->type == 'select_dynamic' || $sdFieldTmp->type == 'select_dynamic_cascade'){
										// JSON > Array
										$fieldOpt2 = JCckDev::fromJSON($sdFieldTmp->options2);
										// Set Query to get select dynamic criteria (thanks Lionel!)
										$query = 'SELECT '.$fieldOpt2['name'].' FROM '.$fieldOpt2['table'].' WHERE '.$fieldOpt2['value'].'="'.$config['post'][$sdField].'"';
										// Get database object as string (thanks SEBLOD devs!)
										$dynamicText =  JCckDatabase::loadResult($query);
										if(trim($dynamicText) > ''):
											$sdConcatValue .= $dynamicText;
											$sdConcatValue .= ($sdNumStep < $sdNumFields ? $sdFieldSeparator : null);
										endif;
										$sdFieldTmp = '';
									}else{
										$sdFieldTmp = parent::g_getOptionText( trim($config['post'][$sdField]), $sdFieldTmp->options, '', $config );
										if(trim($sdFieldTmp) > ''):
											$sdConcatValue .= $sdFieldTmp;
										endif;
										$sdFieldTmp = '';
									}
								break;
								
								case 'v':
									if($sdFieldTmp->type == 'select_dynamic' || $sdFieldTmp->type == 'select_dynamic_cascade'){
										// JSON > Array
										$fieldOpt2 = JCckDev::fromJSON($sdFieldTmp->options2);
										// Set Query to get select dynamic criteria (thanks Lionel!)
										$query = 'SELECT '.$fieldOpt2['value'].' FROM '.$fieldOpt2['table'].' WHERE '.$fieldOpt2['value'].'="'.$config['post'][$sdField].'"';
										// Get database object as string (thanks SEBLOD devs!)
										$dynamicText =  JCckDatabase::loadResult($query);
										if(trim($dynamicText) > ''):
											$sdConcatValue .= $dynamicText;
											$sdConcatValue .= ($sdNumStep < $sdNumFields ? $sdFieldSeparator : null);
										endif;
										$sdFieldTmp = '';
									}else{
										($sdField > '' && trim($config['post'][$sdField]) > '' ? $sdConcatValue .= trim($config['post'][$sdField]).($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
									}
								break;
								
								default:
									($sdField > '' && trim($config['post'][$sdField]) > '' ? $sdConcatValue .= trim($config['post'][$sdField]).($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
								break;
							};
					}elseif($sdFieldTmp->type == 'select_multiple' || ($sdFieldTmp->type == 'checkbox' && is_array($config['post'][$sdField])) || ($sdFieldTmp->type == 'radio' && is_array($config['post'][$sdField]))){
							switch($sdFieldTextValue){
								case 't':
									$SdValHolder = "";
									$SdArrayCount = count($config['post'][$sdField]);
									$s = 0;
									foreach($config['post'][$sdField] as $selectOption){
										$s++;
										$tempsep = "";
										$sdFieldSeparator == "ns" || $sdFieldSeparator == "" ? $tempsep = " " : $tempsep = $sdFieldSeparator;
										$SdValHolder .= parent::g_getOptionText( trim($selectOption), $sdFieldTmp->options, '', $config ).($s !== $SdArrayCount ? $tempsep : NULL);
									}
									if(trim($SdValHolder) > ''):
										$sdConcatValue .= $SdValHolder;
									endif;
									$SdValHolder = '';
								break;
								
								case 'v':
									$SdValHolder = "";
									$SdArrayCount = count($config['post'][$sdField]);
									$s = 0;
									foreach($config['post'][$sdField] as $selectOption){
										$s++;
										$tempsep = "";
										$sdFieldSeparator == "ns" || $sdFieldSeparator == "" ? $tempsep = " " : $tempsep = $sdFieldSeparator;
										$SdValHolder .= trim($selectOption).($s !== $SdArrayCount ? $tempsep : NULL);
									}
									if(trim($sdConcatValue) > ''):
										$sdConcatValue .= $SdValHolder;
									endif;
									$SdValHolder = '';
								break;
								
								default:
									//($sdField > '' ? $sdConcatValue .= trim($config['post'][$sdField]).($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
								break;
							};
						}
					else{
						($sdField > '' && trim($config['post'][$sdField]) > '' ? $sdConcatValue .= trim($config['post'][$sdField]).($sdNumStep < $sdNumFields ? $sdFieldSeparator : null) : null);
						
					};				
				endif;
				/* END SINGLE FEILD */
		endif;
		// The final straw, the big guy, the heavy lifter!!!
		$value = $sdConcatValue;
		
		####################################################################
		// END SD FIELD CONCAT - SIMON DOWDLES - http://www.simondowdles.com
		####################################################################
		
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
		return parent::g_onCCK_FieldRenderContent( $field );
	}
	
	// onCCK_FieldRenderForm
	public static function onCCK_FieldRenderForm( $field, &$config = array() )
	{		
		return parent::g_onCCK_FieldRenderForm( $field );
	}
	
	// onCCK_FieldBeforeStore
	public static function onCCK_FieldBeforeStore( $process, &$fields, &$storages, &$config = array() )
	{
	}
	
	// onCCK_FieldAfterStore
	public static function onCCK_FieldAfterStore( $process, &$fields, &$storages, &$config = array() )
	{
	}
	
	// onCCK_FieldPrepareSearch
	public function onCCK_FieldPrepareSearch( &$field, $value = '', &$config = array(), $inherit = array(), $return = false )
	{
		if ( self::$type != $field->type ) {
			return;
		}
		
		// Prepare
		self::onCCK_FieldPrepareForm( $field, $value, $config, $inherit, $return );
		
		// Return
		if ( $return === true ) {
			return $field;
		}
	}

}

?>