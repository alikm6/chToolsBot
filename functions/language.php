<?php

require __DIR__ . "/../class/pomo/mo.php";

/**
 * Retrieve the translation of $text.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * *Note:* Don't use translate() directly, use __() or related functions.
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 * @return string Translated text.
 * @since 5.5.0 Introduced gettext-{$domain} filter.
 *
 * @since 2.2.0
 */
function translate($text, $domain = 'default')
{
    $translations = get_translations_for_domain($domain);

    return $translations->translate($text);
}

/**
 * Retrieve the translation of $text in the context defined in $context.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * *Note:* Don't use translate_with_gettext_context() directly, use _x() or related functions.
 *
 * @param string $text Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string Translated text on success, original text on failure.
 * @since 2.8.0
 * @since 5.5.0 Introduced gettext_with_context-{$domain} filter.
 *
 */
function translate_with_gettext_context($text, $context, $domain = 'default')
{
    $translations = get_translations_for_domain($domain);

    return $translations->translate($text, $context);
}

/**
 * Retrieve the translation of $text.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 * @return string Translated text.
 * @since 2.1.0
 *
 */
function __($text, $domain = 'default')
{
    return translate($text, $domain);
}

/**
 * Display translated text.
 *
 * @param string $text Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 * @since 1.2.0
 *
 */
function _e($text, $domain = 'default')
{
    echo translate($text, $domain);
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
 * @param string $text Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string Translated context string without pipe.
 * @since 2.8.0
 *
 */
function _x($text, $context, $domain = 'default')
{
    return translate_with_gettext_context($text, $context, $domain);
}

/**
 * Display translated string with gettext context.
 *
 * @param string $text Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string Translated context string without pipe.
 * @since 3.0.0
 *
 */
function _ex($text, $context, $domain = 'default')
{
    echo _x($text, $context, $domain);
}

/**
 * Translates and retrieves the singular or plural form based on the supplied number.
 *
 * Used when you want to use the appropriate form of a string based on whether a
 * number is singular or plural.
 *
 * Example:
 *
 *     printf( _n( '%s person', '%s people', $count, 'text-domain' ), number_format_i18n( $count ) );
 *
 * @param string $single The text to be used if the number is singular.
 * @param string $plural The text to be used if the number is plural.
 * @param int $number The number to compare against to use either the singular or plural form.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                       Default 'default'.
 * @return string The translated singular or plural form.
 * @since 5.5.0 Introduced ngettext-{$domain} filter.
 *
 * @since 2.8.0
 */
function _n($single, $plural, $number, $domain = 'default')
{
    $translations = get_translations_for_domain($domain);

    return $translations->translate_plural($single, $plural, $number);
}

/**
 * Translates and retrieves the singular or plural form based on the supplied number, with gettext context.
 *
 * This is a hybrid of _n() and _x(). It supports context and plurals.
 *
 * Used when you want to use the appropriate form of a string with context based on whether a
 * number is singular or plural.
 *
 * Example of a generic phrase which is disambiguated via the context parameter:
 *
 *     printf( _nx( '%s group', '%s groups', $people, 'group of people', 'text-domain' ), number_format_i18n( $people ) );
 *     printf( _nx( '%s group', '%s groups', $animals, 'group of animals', 'text-domain' ), number_format_i18n( $animals ) );
 *
 * @param string $single The text to be used if the number is singular.
 * @param string $plural The text to be used if the number is plural.
 * @param int $number The number to compare against to use either the singular or plural form.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string The translated singular or plural form.
 * @since 2.8.0
 * @since 5.5.0 Introduced ngettext_with_context-{$domain} filter.
 *
 */
function _nx($single, $plural, $number, $context, $domain = 'default')
{
    $translations = get_translations_for_domain($domain);

    return $translations->translate_plural($single, $plural, $number, $context);
}

/**
 * Translates and retrieves the singular or plural form of a string that's been registered
 * with _n_noop() or _nx_noop().
 *
 * Used when you want to use a translatable plural string once the number is known.
 *
 * Example:
 *
 *     $message = _n_noop( '%s post', '%s posts', 'text-domain' );
 *     ...
 *     printf( translate_nooped_plural( $message, $count, 'text-domain' ), number_format_i18n( $count ) );
 *
 * @param array $nooped_plural Array with singular, plural, and context keys, usually the result of _n_noop() or _nx_noop().
 * @param int $count Number of objects.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings. If $nooped_plural contains
 *                              a text domain passed to _n_noop() or _nx_noop(), it will override this value. Default 'default'.
 * @return string Either $single or $plural translated text.
 * @since 3.1.0
 *
 */
function translate_nooped_plural($nooped_plural, $count, $domain = 'default')
{
    if ($nooped_plural['domain']) {
        $domain = $nooped_plural['domain'];
    }

    if ($nooped_plural['context']) {
        return _nx($nooped_plural['singular'], $nooped_plural['plural'], $count, $nooped_plural['context'], $domain);
    } else {
        return _n($nooped_plural['singular'], $nooped_plural['plural'], $count, $domain);
    }
}

/**
 * Load a .mo file into the text domain $domain.
 *
 * If the text domain already exists, the translations will be merged. If both
 * sets have the same string, the translation from the original value will be taken.
 *
 * On success, the .mo file will be placed in the $l10n global by $domain
 * and will be a MO object.
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @param string $mofile Path to the .mo file.
 * @return bool True on success, false on failure.
 * @since 1.5.0
 *
 * @global MO[] $l10n An array of all currently loaded text domains.
 * @global MO[] $l10n_unloaded An array of all text domains that have been unloaded again.
 *
 */
function load_textdomain($domain, $mofile)
{
    global $l10n, $l10n_unloaded;

    $l10n_unloaded = (array)$l10n_unloaded;

    if (!is_readable($mofile)) {
        return false;
    }

    $mo = new MO();
    if (!$mo->import_from_file($mofile)) {
        return false;
    }

    if (isset($l10n[$domain])) {
        $mo->merge_with($l10n[$domain]);
    }

    unset($l10n_unloaded[$domain]);

    $l10n[$domain] = &$mo;

    return true;
}

/**
 * Unload translations for a text domain.
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return bool Whether textdomain was unloaded.
 * @global MO[] $l10n_unloaded An array of all text domains that have been unloaded again.
 *
 * @since 3.0.0
 *
 * @global MO[] $l10n An array of all currently loaded text domains.
 */
function unload_textdomain($domain)
{
    global $l10n, $l10n_unloaded;

    $l10n_unloaded = (array)$l10n_unloaded;

    if (isset($l10n[$domain])) {
        unset($l10n[$domain]);

        $l10n_unloaded[$domain] = true;

        return true;
    }

    return false;
}

/**
 * Return the Translations instance for a text domain.
 *
 * If there isn't one, returns empty Translations instance.
 *
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return Translations|NOOP_Translations A Translations instance.
 * @since 2.8.0
 *
 * @global MO[] $l10n
 *
 */
function get_translations_for_domain($domain)
{
    global $l10n;
    if (isset($l10n[$domain])) {
        return $l10n[$domain];
    }

    static $noop_translations = null;
    if (null === $noop_translations) {
        $noop_translations = new NOOP_Translations;
    }

    return $noop_translations;
}