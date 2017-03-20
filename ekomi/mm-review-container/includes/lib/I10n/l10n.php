<?php
require_once dirname(__FILE__) . '/Translations.php';
require_once dirname(__FILE__) . '/NoopTranslations.php';
require_once dirname(__FILE__) . '/Streams.php';

/**
 * eKomi Translation API
 *
 * @package eKomi
 * @subpackage i18n
 */

/**
 * Get the current locale.
 *
 * If the locale is defined, return it, else, return default one.
 *
 * @since 1.5.0
 *
 * @return string The locale of the app.
 */
function get_locale()
{
    // EKOMI_APP_LOCALE was defined in config.
    if (defined('EKOMI_APP_LOCALE')) {
        $locale = EKOMI_APP_LOCALE;
    }

    // Allow to overwrite the defined language
    if (isset($_REQUEST['languageLocale'])) {
        $locale = $_REQUEST['languageLocale'];
    }

    if (empty($locale)) {
        $locale = 'en_US';
    }

    return $locale;
}

/**
 * Retrieve the translation of $text.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * *Note:* Don't use {@see translate()} directly, use `{@see ___()} or related functions.
 *
 * @since 2.2.0
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text
 */
function translate($text, $domain = 'ekomi')
{
    $translations = get_translations_for_domain($domain);

    return $translations->translate($text);
}

/**
 * Retrieve the translation of $text in the context defined in $context.
 *
 * If there is no translation, or the text domain isn't loaded the original
 * text is returned.
 *
 * @since 2.8.0
 *
 * @param string $text Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text on success, original text on failure.
 */
function translate_with_gettext_context($text, $context, $domain = 'ekomi')
{
    $translations = get_translations_for_domain($domain);
    $translations = $translations->translate($text, $context);

    /**
     * Filter text with its translation based on context information.
     *
     * @since 2.8.0
     *
     * @param string $translations Translated text.
     * @param string $text Text to translate.
     * @param string $context Context information for the translators.
     * @param string $domain Text domain. Unique identifier for retrieving translated strings.
     */
    return $translations;
}

/**
 * Retrieve the translation of $text. If there is no translation,
 * or the text domain isn't loaded, the original text is returned.
 *
 * Conflict with Magento!
 *
 * @since 2.1.0
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text.
 */
function ___($text, $domain = 'ekomi')
{
    return translate($text, $domain);
}

/**
 * Retrieve the translation of $text and escapes it for safe use in an attribute.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * @since 2.8.0
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text on success, original text on failure.
 */
function esc_attr___($text, $domain = 'ekomi')
{
    return esc_attr(translate($text, $domain));
}

/**
 * Retrieve the translation of $text and escapes it for safe use in HTML output.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * @since 2.8.0
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text
 */
function esc_html___($text, $domain = 'ekomi')
{
    return esc_html(translate($text, $domain));
}

/**
 * Display translated text.
 *
 * @since 1.2.0
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 */
function _e($text, $domain = 'ekomi')
{
    echo translate($text, $domain);
}

/**
 * Display translated text that has been escaped for safe use in an attribute.
 *
 * @since 2.8.0
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 */
function esc_attr_e($text, $domain = 'ekomi')
{
    echo esc_attr(translate($text, $domain));
}

/**
 * Display translated text that has been escaped for safe use in HTML output.
 *
 * @since 2.8.0
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 */
function esc_html_e($text, $domain = 'ekomi')
{
    echo esc_html(translate($text, $domain));
}

/**
 * Retrieve translated string with gettext context.
 *
 * Quite a few times, there will be collisions with similar translatable text
 * found in more than two places, but with different translated context.
 *
 * By including the context in the pot file, translators can translate the two
 * strings differently.
 *
 * @since 2.8.0
 *
 * @param string $text Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated context string without pipe.
 */
function _x($text, $context, $domain = 'ekomi')
{
    return translate_with_gettext_context($text, $context, $domain);
}

/**
 * Display translated string with gettext context.
 *
 * @since 3.0.0
 *
 * @param string $text Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated context string without pipe.
 */
function _ex($text, $context, $domain = 'ekomi')
{
    echo _x($text, $context, $domain);
}

/**
 * Translate string with gettext context, and escapes it for safe use in an attribute.
 *
 * @since 2.8.0
 *
 * @param string $text Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text
 */
function esc_attr_x($text, $context, $domain = 'ekomi')
{
    return esc_attr(translate_with_gettext_context($text, $context, $domain));
}

/**
 * Translate string with gettext context, and escapes it for safe use in HTML output.
 *
 * @since 2.9.0
 *
 * @param string $text Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text.
 */
function esc_html_x($text, $context, $domain = 'ekomi')
{
    return esc_html(translate_with_gettext_context($text, $context, $domain));
}

/**
 * Retrieve the plural or single form based on the supplied amount.
 *
 * If the text domain is not set in the $I10n list, then a comparison will be made
 * and either $plural or $single parameters returned.
 *
 * If the text domain does exist, then the parameters $single, $plural, and $number
 * will first be passed to the text domain's ngettext method. Then it will be passed
 * to the 'ngettext' filter hook along with the same parameters. The expected
 * type will be a string.
 *
 * @since 2.8.0
 *
 * @param string $single The text that will be used if $number is 1.
 * @param string $plural The text that will be used if $number is not 1.
 * @param int $number The number to compare against to use either $single or $plural.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Either $single or $plural translated text.
 */
function _n($single, $plural, $number, $domain = 'ekomi')
{
    $translations = get_translations_for_domain($domain);
    $translation = $translations->translate_plural($single, $plural, $number);

    /**
     * Filter text with its translation when plural option is available.
     *
     * @since 2.2.0
     *
     * @param string $translation Translated text.
     * @param string $single The text that will be used if $number is 1.
     * @param string $plural The text that will be used if $number is not 1.
     * @param string $number The number to compare against to use either $single or $plural.
     * @param string $domain Text domain. Unique identifier for retrieving translated strings.
     */
    return $translation;
}

/**
 * Retrieve the plural or single form based on the supplied amount with gettext context.
 *
 * This is a hybrid of _n() and _x(). It supports contexts and plurals.
 *
 * @since 2.8.0
 *
 * @param string $single The text that will be used if $number is 1.
 * @param string $plural The text that will be used if $number is not 1.
 * @param int $number The number to compare against to use either $single or $plural.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Either $single or $plural translated text with context.
 */
function _nx($single, $plural, $number, $context, $domain = 'ekomi')
{
    $translations = get_translations_for_domain($domain);
    $translation = $translations->translate_plural($single, $plural, $number, $context);

    /**
     * Filter text with its translation while plural option and context are available.
     *
     * @since 2.8.0
     *
     * @param string $translation Translated text.
     * @param string $single The text that will be used if $number is 1.
     * @param string $plural The text that will be used if $number is not 1.
     * @param string $number The number to compare against to use either $single or $plural.
     * @param string $context Context information for the translators.
     * @param string $domain Text domain. Unique identifier for retrieving translated strings.
     */
    return $translation;
}

/**
 * Register plural strings in POT file, but don't translate them.
 *
 * Used when you want to keep structures with translatable plural
 * strings and use them later.
 *
 * Example:
 *
 *     $messages = array(
 *        'post' => _n_noop( '%s post', '%s posts' ),
 *        'page' => _n_noop( '%s pages', '%s pages' ),
 *     );
 *     ...
 *     $message = $messages[ $type ];
 *     $usable_text = sprintf( translate_nooped_plural( $message, $count ), $count );
 *
 * @since 2.5.0
 *
 * @param string $singular Single form to be i18ned.
 * @param string $plural Plural form to be i18ned.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return array array($singular, $plural)
 */
function _n_noop($singular, $plural, $domain = null)
{
    return array(
        0 => $singular,
        1 => $plural,
        'singular' => $singular,
        'plural' => $plural,
        'context' => null,
        'domain' => $domain
    );
}

/**
 * Register plural strings with context in POT file, but don't translate them.
 *
 * @since 2.8.0
 * @param string $singular
 * @param string $plural
 * @param string $context
 * @param string|null $domain
 * @return array
 */
function _nx_noop($singular, $plural, $context, $domain = null)
{
    return array(
        0 => $singular,
        1 => $plural,
        2 => $context,
        'singular' => $singular,
        'plural' => $plural,
        'context' => $context,
        'domain' => $domain
    );
}

/**
 * Translate the result of _n_noop() or _nx_noop().
 *
 * @since 3.1.0
 *
 * @param array $nooped_plural Array with singular, plural and context keys, usually the result of _n_noop() or _nx_noop()
 * @param int $count Number of objects
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings. If $nooped_plural contains
 *                              a text domain passed to _n_noop() or _nx_noop(), it will override this value.
 * @return string Either $single or $plural translated text.
 */
function translate_nooped_plural($nooped_plural, $count, $domain = 'ekomi')
{
    if ($nooped_plural['domain']) $domain = $nooped_plural['domain'];

    if ($nooped_plural['context']) return _nx($nooped_plural['singular'], $nooped_plural['plural'], $count, $nooped_plural['context'], $domain);
    else return _n($nooped_plural['singular'], $nooped_plural['plural'], $count, $domain);
}

/**
 * Load a .mo file into the text domain $domain.
 *
 * If the text domain already exists, the translations will be merged. If both
 * sets have the same string, the translation from the original value will be taken.
 *
 * On success, the .mo file will be placed in the $I10n global by $domain
 * and will be a MO object.
 *
 * @since 1.5.0
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @param string $mofile Path to the .mo file.
 * @return bool True on success, false on failure.
 */
function load_textdomain($domain, $mofile)
{
    global $l10n;

    if (!is_readable($mofile)) return false;

    $mo = new I10n_MO();


    if (!$mo->import_from_file($mofile)) return false;

    if (isset($l10n[$domain])) $mo->merge_with($l10n[$domain]);

    $l10n[$domain] = &$mo;

    //var_dump($l10n[$domain]->entries);
    return true;
}

/**
 * Load default translated strings based on locale.
 *
 * Loads the .mo file in WP_LANG_DIR constant path from WordPress root.
 * The translated (.mo) file is named based on the locale.
 *
 * @see load_textdomain()
 *
 * @since 1.5.0
 *
 * @param string $locale Optional. Locale to load. Default is the value of {@see get_locale()}.
 * @return bool Whether the textdomain was loaded.
 */
function load_default_textdomain($domain, $path = false)
{
    $locale = get_locale();

    // Load the textdomain according to the theme
    $path = rtrim($path, '/\\');
    $mofile = $path . DIRECTORY_SEPARATOR . "{$locale}.mo";

    if ($loaded = load_textdomain($domain, $mofile)) return $loaded;

    // Otherwise, load from the languages directory
    //$mofile = WP_LANG_DIR . "/themes/{$domain}-{$locale}.mo";
    return $mofile;
}

/**
 * Return the Translations instance for a text domain.
 *
 * If there isn't one, returns empty Translations instance.
 *
 * @since 2.8.0
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return NoopTranslations A Translations instance.
 */
function get_translations_for_domain($domain)
{
    global $l10n;

    if (!isset($l10n[$domain])) {
        $l10n[$domain] = new I10n_NoopTranslations;
    }

    return $l10n[$domain];
}

/**
 * Get installed translations.
 *
 * Looks in the wp-content/languages directory for translations of
 * plugins or themes.
 *
 * @since 3.7.0
 *
 * @param string $type What to search for. Accepts 'plugins', 'themes', 'core'.
 * @return array Array of language data.
 */
function wp_get_installed_translations($type)
{
    if ($type !== 'themes' && $type !== 'plugins' && $type !== 'core') return array();

    $dir = 'core' === $type ? '' : "/$type";

    if (!is_dir(WP_LANG_DIR)) return array();

    if ($dir && !is_dir(WP_LANG_DIR . $dir)) return array();

    $files = scandir(WP_LANG_DIR . $dir);
    if (!$files) return array();

    $language_data = array();

    foreach ($files as $file) {
        if ('.' === $file[0] || is_dir($file)) {
            continue;
        }
        if (substr($file, -3) !== '.po') {
            continue;
        }
        if (!preg_match('/(?:(.+)-)?([A-Za-z_]{2,6}).po/', $file, $match)) {
            continue;
        }
        if (!in_array(substr($file, 0, -3) . '.mo', $files)) {
            continue;
        }

        list(, $textdomain, $language) = $match;
        if ('' === $textdomain) {
            $textdomain = 'default';
        }
        $language_data[$textdomain][$language] = wp_get_pomo_file_data(WP_LANG_DIR . "$dir/$file");
    }
    return $language_data;
}

/**
 * Extract headers from a PO file.
 *
 * @since 3.7.0
 *
 * @param string $po_file Path to PO file.
 * @return array PO file headers.
 */
function wp_get_pomo_file_data($po_file)
{
    $headers = get_file_data($po_file, array(
        'POT-Creation-Date' => '"POT-Creation-Date',
        'PO-Revision-Date' => '"PO-Revision-Date',
        'Project-Id-Version' => '"Project-Id-Version',
        'X-Generator' => '"X-Generator',
    ));
    foreach ($headers as $header => $value) {

        // Remove possible contextual '\n' and closing double quote.
        $headers[$header] = preg_replace('~(\\\n)?"$~', '', $value);
    }
    return $headers;
}