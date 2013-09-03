<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package     MetaModels
 * @subpackage  AttributeLangcode
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */

namespace MetaModels\Attribute\LangCode;

use MetaModels\Attribute\BaseSimple;
use MetaModels\Helper\ContaoController;
use MetaModels\Render\Template;

/**
 * This is the MetaModelAttribute class for handling text fields.
 *
 * @package	   MetaModels
 * @subpackage AttributeLangcode
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class LangCode extends BaseSimple
{
	/**
	 * when rendered via a template, this returns the values to be stored in the template.
	 */
	protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings = null)
	{
		parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);
		$objTemplate->value = $this->resolveValue($arrRowData[$this->getColName()]);
	}

	/////////////////////////////////////////////////////////////////
	// interface IMetaModelAttributeSimple
	/////////////////////////////////////////////////////////////////

	public function getSQLDataType()
	{
		return 'varchar(5) NOT NULL default \'\'';
	}

	public function getAttributeSettingNames()
	{
		return array_merge(parent::getAttributeSettingNames(), array(
			'langcodes',
			'filterable',
			'searchable',
			'sortable',
			'flag',
			'mandatory',
			'includeBlankOption'
		));
	}

	public function getFieldDefinition($arrOverrides = array())
	{
		$arrFieldDef=parent::getFieldDefinition($arrOverrides);
		$arrFieldDef['inputType'] = 'select';
		$arrFieldDef['options'] = array_intersect_key(
			ContaoController::getInstance()->getLanguages(),
			array_flip((array) $this->get('langcodes'))
		);
		$arrFieldDef['eval']['chosen'] = true;
		return $arrFieldDef;
	}

	protected function resolveValue($strLangValue)
	{
		$strLangCode = $this->getMetaModel()->getActiveLanguage();

		// set the desired language.
		ContaoController::getInstance()->loadLanguageFile('languages', $strLangCode, true);
		if (strlen($GLOBALS['TL_LANG']['LNG'][$strLangValue]))
		{
			$strResult = $GLOBALS['TL_LANG']['LNG'][$strLangValue];
		} else {
			$strLangCode = $this->getMetaModel()->getFallbackLanguage();
			// set the fallback language.
			ContaoController::getInstance()->loadLanguageFile('languages', $strLangCode, true);
			if (strlen($GLOBALS['TL_LANG']['LNG'][$strLangValue]))
			{
				$strResult = $GLOBALS['TL_LANG']['LNG'][$strLangValue];
			} else {
				// use english as last resort.
				include(TL_ROOT . '/system/config/languages.php');
				$strResult = $languages[$strLangValue];
			}
		}
		// switch back to the original FE language to not disturb the frontend.
		if ($strLangCode != $GLOBALS['TL_LANGUAGE'])
		{
			ContaoController::getInstance()->loadLanguageFile('languages', false, true);
		}
		return $strResult;
	}
}
