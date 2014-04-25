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
 * @package    MetaModels
 * @subpackage AttributeLangcode
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class LangCode extends BaseSimple
{
	/**
	 * {@inheritDoc}
	 */
	protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings = null)
	{
		parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);
		$objTemplate->value = $this->resolveValue($arrRowData[$this->getColName()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSQLDataType()
	{
		return 'varchar(5) NOT NULL default \'\'';
	}

	/**
	 * {@inheritDoc}
	 */
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

	/**
	 * {@inheritDoc}
	 */
	public function getFieldDefinition($arrOverrides = array())
	{
		$arrFieldDef                   = parent::getFieldDefinition($arrOverrides);
		$arrFieldDef['inputType']      = 'select';
		$arrFieldDef['eval']['chosen'] = true;
		$arrFieldDef['options']        = array_intersect_key(
			// FIXME: Get rid of deprecated \MetaModels\Helper\ContaoController.
			ContaoController::getInstance()->getLanguages(),
			array_flip((array)$this->get('langcodes'))
		);
		return $arrFieldDef;
	}

	/**
	 * Resolve a language code to the real language name in either the currently active language or the fallback.
	 *
	 * @param string $strLangValue The language code to resolve.
	 *
	 * @return string
	 */
	protected function resolveValue($strLangValue)
	{
		$strLangCode = $this->getMetaModel()->getActiveLanguage();

		// Set the desired language.
		ContaoController::getInstance()->loadLanguageFile('languages', $strLangCode, true);
		if (strlen($GLOBALS['TL_LANG']['LNG'][$strLangValue]))
		{
			$strResult = $GLOBALS['TL_LANG']['LNG'][$strLangValue];
		} else {
			$strLangCode = $this->getMetaModel()->getFallbackLanguage();
			// Set the fallback language.
			ContaoController::getInstance()->loadLanguageFile('languages', $strLangCode, true);
			if (strlen($GLOBALS['TL_LANG']['LNG'][$strLangValue]))
			{
				$strResult = $GLOBALS['TL_LANG']['LNG'][$strLangValue];
			} else {
				// Use english as last resort.
				// @codingStandardsIgnoreStart - Contao requires to include the file, we can not use require_once here.
				include(TL_ROOT . '/system/config/languages.php');
				$strResult = $languages[$strLangValue];
				// @codingStandardsIgnoreEnd
			}
		}
		// Switch back to the original FE language to not disturb the frontend.
		if ($strLangCode != $GLOBALS['TL_LANGUAGE'])
		{
			ContaoController::getInstance()->loadLanguageFile('languages', false, true);
		}
		return $strResult;
	}
}
