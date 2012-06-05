<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage AttributeLangCode
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * This is the MetaModelAttribute class for handling text fields.
 * 
 * @package	   MetaModels
 * @subpackage AttributeLangCode
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelAttributeLangCode extends MetaModelAttributeSimple
{
	/////////////////////////////////////////////////////////////////
	// interface IMetaModelAttributeSimple
	/////////////////////////////////////////////////////////////////

	public function getSQLDataType()
	{
		return 'varchar(2) NOT NULL default \'\'';
	}

	public function getAttributeSettingNames()
	{
		return array_merge(parent::getAttributeSettingNames(), array(
			'langcodes'
		));
	}

	public function getFieldDefinition()
	{
		$arrFieldDef=parent::getFieldDefinition();
		$arrFieldDef['inputType'] = 'select';
		$arrFieldDef['options'] = $this->get('langcodes');
		return $arrFieldDef;
	}


	public function parseValue($arrRowData, $strOutputFormat = 'html')
	{
		$arrResult = parent::parseValue($arrRowData, $strOutputFormat);
		// TODO: add loading of language file languages.php and render the value accordingly.
		// Sadly Controller::getLanguages and loadLanguageFile() are protected and not static in 2.11
		$arrResult['html'] = $arrRowData[$this->getColName()];
		return $arrResult;
	}
}

?>