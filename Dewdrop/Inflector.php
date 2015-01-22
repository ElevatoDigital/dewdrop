<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use Dewdrop\Paths;

/**
 * Some utility functions that make it easy to convert from one text format
 * to another (e.g. from CamelCase to under_scores).
 *
 * This tool is used in various places to created default human-friendly
 * titles from database table or column names, to convert class names to
 * paths, etc.
 *
 * This is more or less a direct port of the PEAR Text_Inflector package.
 */
class Inflector
{
    /**
     * Pluralizes English nouns.
     *
     * @access public
     * @static
     * @param string $word English noun to pluralize
     * @return string Plural noun
     */
    public function pluralize($word)
    {
        $plurals = array(
            '/(quiz)$/i'               => '\1zes',
            '/^(ox)$/i'                => '\1en',
            '/([m|l])ouse$/i'          => '\1ice',
            '/(matr|vert|ind)ix|ex$/i' => '\1ices',
            '/(x|ch|ss|sh)$/i'         => '\1es',
            '/([^aeiouy]|qu)y$/i'      => '\1ies',
            '/(hive)$/i'               => '\1s',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/sis$/i'                  => 'ses',
            '/([ti])um$/i'             => '\1a',
            '/(buffal|tomat)o$/i'      => '\1oes',
            '/(bu)s$/i'                => '\1ses',
            '/(alias|status)/i'        => '\1es',
            '/(octop|vir)us$/i'        => '\1i',
            '/(ax|test)is$/i'          => '\1es',
            '/s$/i'                    => 's',
            '/$/'                      => 's'
        );

        $uncountables = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

        $irregular = array(
            'person' => 'people',
            'man'    => 'men',
            'child'  => 'children',
            'sex'    => 'sexes',
            'move'   => 'moves'
        );

        $lowercasedWord = strtolower($word);

        foreach ($uncountables as $uncountable) {
            if (substr($lowercasedWord, (-1 * strlen($uncountable))) === $uncountable) {
                return $word;
            }
        }

        foreach ($irregular as $plural => $singular) {
            if (preg_match('/(' . $plural . ')$/i', $word, $arr)) {
                return preg_replace(
                    '/(' . $plural . ')$/i',
                    substr($arr[0], 0, 1) . substr($singular, 1),
                    $word
                );
            }
        }

        foreach ($plurals as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return false;
    }

    /**
    * Singularizes English nouns.
    *
    * @param string $word English noun to singularize
    * @return string Singular noun.
    */
    public function singularize($word)
    {
        $singulars = array (
            '/(quiz)zes$/i'                                                    => '\\1',
            '/(matr)ices$/i'                                                   => '\\1ix',
            '/(vert|ind)ices$/i'                                               => '\\1ex',
            '/^(ox)en/i'                                                       => '\\1',
            '/(alias|status)es$/i'                                             => '\\1',
            '/([octop|vir])i$/i'                                               => '\\1us',
            '/(cris|ax|test)es$/i'                                             => '\\1is',
            '/(shoe)s$/i'                                                      => '\\1',
            '/(o)es$/i'                                                        => '\\1',
            '/(bus)es$/i'                                                      => '\\1',
            '/([m|l])ice$/i'                                                   => '\\1ouse',
            '/(x|ch|ss|sh)es$/i'                                               => '\\1',
            '/(m)ovies$/i'                                                     => '\\1ovie',
            '/(s)eries$/i'                                                     => '\\1eries',
            '/([^aeiouy]|qu)ies$/i'                                            => '\\1y',
            '/([lr])ves$/i'                                                    => '\\1f',
            '/(tive)s$/i'                                                      => '\\1',
            '/(hive)s$/i'                                                      => '\\1',
            '/([^f])ves$/i'                                                    => '\\1fe',
            '/(^analy)ses$/i'                                                  => '\\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
            '/([ti])a$/i'                                                      => '\\1um',
            '/(n)ews$/i'                                                       => '\\1ews',
            '/s$/i'                                                            => ''
        );

        $uncountables = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep','sms');

        $irregulars = array(
            'person' => 'people',
            'man'    => 'men',
            'child'  => 'children',
            'sex'    => 'sexes',
            'move'   => 'moves'
        );

        $lowercasedWord = strtolower($word);

        foreach ($uncountables as $uncountable) {
            if (substr($lowercasedWord, (-1 * strlen($uncountable))) == $uncountable) {
                return $word;
            }
        }

        foreach ($irregulars as $singular => $plural) {
            if (preg_match('/(' . $plural . ')$/i', $word, $arr)) {
                return preg_replace(
                    '/('.$plural.')$/i',
                    substr($arr[0], 0, 1) . substr($singular, 1),
                    $word
                );
            }
        }

        foreach ($singulars as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return $word;
    }

    /**
     * Get the plural form of a word if first parameter is greater than 1
     *
     * @param integer $number
     * @param string $word
     * @return string Pluralized string when number of items is greater than 1
     */
    public function conditionalPlural($number, $word)
    {
        return $number > 1 ? $this->pluralize($word) : $word;
    }

    /**
     * Generate a list, separated by commas and the specified conjunction,
     * depending on the number of items provided.
     *
     * @param array $items
     * @param string $conjunction
     * @return string
     */
    public function listWithConjunction(array $items, $conjunction = 'and')
    {
        if (1 === count($items)) {
            return current($items);
        }

        $last = array_pop($items);

        if (1 === count($items)) {
            $list = current($items);
        } else {
            $list = implode(', ', $items);
        }

        return $list . ' ' . $conjunction . ' ' . $last;
    }

    /**
     * Converts an underscored or CamelCase word into a English
     * sentence.
     *
     * The titleize function converts text like "WelcomePage",
     * "welcome_page" or  "welcome page" to this "Welcome
     * Page".
     * If second parameter is set to 'first' it will only
     * capitalize the first character of the title.
     *
     * @access public
     * @param string $word Word to format as tile
     * @param string $uppercase If set to 'first' it will only uppercase the
     *                          first character. Otherwise it will uppercase all
     *                          the words in the title.
     * @return string Text formatted as title
     */
    public function titleize($word, $uppercase = '')
    {
        $uppercase = $uppercase == 'first' ? 'ucfirst' : 'ucwords';
        return $uppercase($this->humanize($this->underscore($word)));
    }

    /**
     * Returns given word as CamelCased
     *
     * Converts a word like "send_email" to "SendEmail". It
     * will remove non alphanumeric character from the word, so
     * "who's online" will be converted to "WhoSOnline"
     *
     * @access public
     * @see variablize
     * @param string $word Word to convert to camel case
     * @return string UpperCamelCasedWord
     */
    public function camelize($word)
    {
        if (preg_match_all('/\/(.?)/', $word, $got)) {
            foreach ($got[1] as $k => $v) {
                $got[1][$k] = '::'.strtoupper($v);
            }

            $word = str_replace($got[0], $got[1], $word);
        }

        return str_replace(
            ' ',
            '',
            ucwords(
                preg_replace('/[^A-Z^a-z^0-9^:]+/', ' ', $word)
            )
        );
    }

    /**
     * Converts a word "into_it_s_underscored_version"
     *
     * Convert any "CamelCased" or "ordinary Word" into an
     * "underscored_word".
     *
     * This can be really useful for creating friendly URLs.
     *
     * @access public
     * @param string $word Word to underscore
     * @return string Underscored word
     */
    public function underscore($word)
    {
        return strtolower(
            preg_replace(
                '/[^A-Z^a-z^0-9^\/]+/',
                '_',
                preg_replace(
                    '/([a-z\d])([A-Z])/',
                    '\1_\2',
                    preg_replace(
                        '/([A-Z]+)([A-Z][a-z])/',
                        '\1_\2',
                        preg_replace('/::/', '/', $word)
                    )
                )
            )
        );
    }

    /**
     * Just like underscore(), but with hyphens.
     *
     * @param string $word
     * @return string
     */
    public function hyphenize($word)
    {
        return str_replace('_', '-', $this->underscore($word));
    }

    /**
     * Returns a human-readable string from $word
     *
     * Returns a human-readable string from $word, by replacing
     * underscores with a space, and by upper-casing the initial
     * character by default.
     *
     * If you need to uppercase all the words you just have to
     * pass 'all' as a second parameter.
     *
     * @access public
     * @param string $word String to "humanize"
     * @param string $uppercase If set to 'all' it will uppercase all the words
     * instead of just the first one.
     * @return string Human-readable word
     */
    public static function humanize($word, $uppercase = '')
    {
        $uppercase = $uppercase == 'all' ? 'ucwords' : 'ucfirst';
        return $uppercase(str_replace('_', ' ', preg_replace('/_id$/', '', $word)));
    }

    /**
     * Same as camelize but first char is lowercased
     *
     * Converts a word like "send_email" to "sendEmail". It
     * will remove non alphanumeric character from the word, so
     * "who's online" will be converted to "whoSOnline"
     *
     * @access public
     * @see camelize
     * @param string $word Word to lowerCamelCase
     * @return string Returns a lowerCamelCasedWord
     */
    public function variablize($word)
    {
        $word = $this->camelize($word);
        return strtolower($word[0]) . substr($word, 1);
    }

    /**
     * Converts a class name to its table name according to rails
     * naming conventions.
     *
     * Converts "Person" to "people"
     *
     * @access public
     * @see classify
     * @param string $className Class name for getting related table_name.
     * @return string
     */
    public function tableize($className)
    {
        return $this->pluralize($this->underscore($className));
    }

    /**
     * Converts a table name to its class name according to Rails
     * naming conventions.
     *
     * Converts "people" to "Person"
     *
     * @access public
     * @see tableize
     * @param string $tableName Table name for getting related ClassName.
     * @return string
     */
    public function classify($tableName)
    {
        return $this->camelize($this->pluralize($tableName));
    }

    /**
     * Converts number to its ordinal English form.
     *
     * This method converts 13 to 13th, 2 to 2nd ...
     *
     * @access public
     * @param integer $number Number to get its ordinal value
     * @return string Ordinal representation of given string.
     */
    public function ordinalize($number)
    {
        if (in_array(($number % 100), range(11, 13))) {
            return $number . 'th';
        } else {
            switch (($number % 10)) {
                case 1:
                    return $number.'st';
                case 2:
                    return $number.'nd';
                case 3:
                    return $number.'rd';
                default:
                    return $number.'th';
            }
        }
    }

    /**
     * Transforms a string to its unaccented version.
     * This might be useful for generating "friendly" URLs.
     *
     * @param string $text
     * @return string
     */
    public function unaccent($text)
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    }

    /**
     * Convert the supplied text to a format friendly for URL segments.
     *
     * @param string $text
     * @return string
     */
    public function urlize($text)
    {
        return trim($this->underscore($this->unaccent($text)), '_');
    }
}
