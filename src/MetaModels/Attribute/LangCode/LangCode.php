<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package     MetaModels
 * @subpackage  AttributeLangcode
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author      Andreas Isaak <andy.jared@googlemail.com>
 * @author      Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author      David Maack <maack@men-at-work.de>
 * @author      Oliver Hoff <oliver@hofff.com>
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */

namespace MetaModels\Attribute\LangCode;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use MetaModels\Attribute\BaseSimple;
use MetaModels\Render\Template;

/**
 * This is the MetaModelAttribute class for handling langcodes.
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
        return array_merge(
            parent::getAttributeSettingNames(),
            array(
                'langcodes',
                'filterable',
                'searchable',
                'sortable',
                'flag',
                'mandatory',
                'includeBlankOption'
            )
        );
    }

    /**
     * Include the TL_ROOT/system/config/languages.php file and return the contained $languages variable.
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getRealLanguages()
    {
        // @codingStandardsIgnoreStart - Include is required here, can not switch to require_once.
        include(TL_ROOT . '/system/config/languages.php');
        // @codingStandardsIgnoreEnd

        return $languages;
    }

    /**
     * Retrieve all language names in the given language.
     *
     * @param string $language The language key.
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getLanguageNames($language = null)
    {
        $dispatcher = $GLOBALS['container']['event-dispatcher'];

        $event = new LoadLanguageFileEvent('languages', $language, true);
        $dispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, $event);

        return $GLOBALS['TL_LANG']['LNG'];
    }

    /**
     * Retrieve all language names.
     *
     * This method takes the fallback language into account.
     *
     * @return string[]
     */
    protected function getLanguages()
    {
        $loadedLanguage = $this->getMetaModel()->getActiveLanguage();
        $languageValues = $this->getLanguageNames($loadedLanguage);
        $languages      = $this->getRealLanguages();
        $keys           = array_keys($languages);
        $aux            = array();
        $real           = array();

        // Fetch real language values.
        foreach ($keys as $key) {
            if (isset($languageValues[$key])) {
                $aux[$key]  = utf8_romanize($languageValues[$key]);
                $real[$key] = $languageValues[$key];
            }
        }

        // Add needed fallback values.
        $keys = array_diff($keys, array_keys($aux));
        if ($keys) {
            $loadedLanguage = $this->getMetaModel()->getFallbackLanguage();
            $fallbackValues = $this->getLanguageNames($loadedLanguage);
            foreach ($keys as $key) {
                if (isset($fallbackValues[$key])) {
                    $aux[$key]  = utf8_romanize($fallbackValues[$key]);
                    $real[$key] = $fallbackValues[$key];
                }
            }
        }

        $keys = array_diff($keys, array_keys($aux));
        if ($keys) {
            foreach ($keys as $key) {
                $aux[$key]  = utf8_romanize($languages[$key]);
                $real[$key] = $languages[$key];
            }
        }

        asort($aux);
        $return = array();
        foreach (array_keys($aux) as $key)
        {
            $return[$key] = $real[$key];
        }

        // Switch back to the original FE language to not disturb the frontend.
        if ($loadedLanguage != $GLOBALS['TL_LANGUAGE'])
        {
            $dispatcher = $GLOBALS['container']['event-dispatcher'];
            
            $event = new LoadLanguageFileEvent('languages', null, true);
            $dispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, $event);
        }

        return $return;
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
            $this->getLanguageNames(),
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
        $countries = $this->getLanguages();

        return $countries[$strLangValue];
    }
}
