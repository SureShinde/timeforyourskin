<?php

/**
 * Provides the same interface as Translations, but doesn't do anything
 */
class I10n_NoopTranslations
{
    var $entries = array();
    var $headers = array();

    function add_entry($entry)
    {
        return true;
    }

    function set_header($header, $value)
    {
    }

    function set_headers($headers)
    {
    }

    function get_header($header)
    {
        return false;
    }

    function translate_entry(&$entry)
    {
        return false;
    }

    /**
     * @param string $singular
     * @param string $context
     */
    function translate($singular, $context = null)
    {
        return $singular;
    }

    function select_plural_form($count)
    {
        return 1 == $count ? 0 : 1;
    }

    function get_plural_forms_count()
    {
        return 2;
    }

    /**
     * @param string $singular
     * @param string $plural
     * @param int $count
     * @param string $context
     */
    function translate_plural($singular, $plural, $count, $context = null)
    {
        return 1 == $count ? $singular : $plural;
    }

    function merge_with(&$other)
    {
    }
}
