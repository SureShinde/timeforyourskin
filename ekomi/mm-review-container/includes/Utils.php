<?php

/**
 * A class full of utility functions that can be used as a standalone
 */
class Utils
{

    /**
     * Check if the request is an AJAX request
     */
    public static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower(
            $_SERVER['HTTP_X_REQUESTED_WITH']
        ) == 'xmlhttprequest';
    }

    /**
     * Merge user defined arguments into defaults array.
     *
     * This function is used throughout WordPress to allow for both string or array
     * to be merged into another array.
     * Can be passed URL-query style: type=post&posts_per_page=5&cat=1
     * Or as an array definition: array( 'type' => 'post', 'posts_per_page' => 5, 'cat' => '1' )
     *
     * @param string|array $args Value to merge with $defaults
     * @param string|array $defaults Optional. Array that serves as the defaults. Default empty.
     * @return array Merged user defined values with defaults.
     */
    public static function parse_args($args, $defaults = '')
    {
        if (is_object($args)) {
            $r = get_object_vars($args);
        } elseif (is_array($args)) {
            $r = &$args;
        } else {
            self::parse_str($args, $r);
        }

        if (is_array($defaults)) {
            return array_merge($defaults, $r);
        }

        return $r;
    }

    /**
     * Parses a string into variables to be stored in an array.
     *
     * Uses {@link http://www.php.net/parse_str parse_str()} and stripslashes if
     * {@link http://www.php.net/magic_quotes magic_quotes_gpc} is on.
     *
     * @since 2.2.1
     *
     * @param string $string The string to be parsed.
     * @param array $array Variables will be stored in this array.
     */
    public static function parse_str($string, &$array)
    {
        parse_str($string, $array);
        if (get_magic_quotes_gpc()) {
            $array = self::stripslashes_deep($array);
        }

        /**
         * Filter the array of variables derived from a parsed string.
         *
         * @since 2.3.0
         *
         * @param array $array The array populated with variables.
         */

        return $array;
    }

    /**
     * Convert time
     *
     * @param $str
     * @return string
     */
    public static function get_iso_time($str)
    {
        $timeZone = 'Europe/Berlin';
        $dateTime = new DateTime($str, new DateTimeZone($timeZone));

        return $dateTime->format(DateTime::ISO8601);
    }

    /**
     * Navigates through an array and removes slashes from the values.
     *
     * If an array is passed, the array_map() function causes a callback to pass the
     * value back to the function. The slashes from this value will removed.
     *
     * @since 2.0.0
     *
     * @param mixed $value The value to be stripped.
     * @return mixed Stripped value.
     */
    public static function stripslashes_deep($value)
    {
        if (is_array($value)) {
            $value = array_map('stripslashes_deep', $value);
        } elseif (is_object($value)) {
            $vars = get_object_vars($value);
            foreach ($vars as $key => $data) {
                $value->{$key
                } = self::stripslashes_deep($data);
            }
        } elseif (is_string($value)) {
            $value = stripslashes($value);
        }

        return $value;
    }

    /**
     * Serialize data, if needed.
     *
     * @since 2.0.5
     *
     * @param string|array|object $data Data that might be serialized.
     * @return mixed A scalar data
     */
    public static function maybe_serialize($data)
    {
        if (is_array($data) || is_object($data)) {
            return serialize($data);
        }

        // Double serialization is required for backward compatibility.
        // See https://core.trac.wordpress.org/ticket/12930
        if (self::is_serialized($data, false)) {
            return serialize($data);
        }

        return $data;
    }

    /**
     * Unserialize value only if it was serialized.
     *
     * @since 2.0.0
     *
     * @param string $original Maybe unserialized original, if is needed.
     * @return mixed Unserialized data can be any type.
     */
    public static function maybe_unserialize($original)
    {
        if (self::is_serialized($original)) // don't attempt to unserialize data that wasn't serialized going in
            return @unserialize($original);
        return $original;
    }

    /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @since 2.0.5
     *
     * @param string $data Value to check to see if was serialized.
     * @param bool $strict Optional. Whether to be strict about the end of the string. Default true.
     * @return bool False if not serialized and true if it was.
     */
    public static function is_serialized($data, $strict = true)
    {
        // if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's' :
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            // or else fall through
            case 'a' :
            case 'O' :
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';

                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
                break;
        }

        return false;
    }

    /**
     * Returns a proper json response.
     */
    public static function jsonDebugDie($state, $message = '', $debug = array())
    {
        echo json_encode(
            array(
                'state' => $state,
                'message' => $message,
                'debug' => json_encode($debug),
                '_POST' => $_POST
            )
        );
        die();
    }

    /**
     * Safely filter arrays in php 5.2+
     *
     * In PHP 5.3 we can use anonymous functions (closures) inside the
     * native filter_array() but they are not supported in 5.2 and also
     * it is slower
     *
     * @param array $haystack
     * @param array $needle
     * @return bool
     */
    public static function isReviewInDatabase($haystack, $needle)
    {
        foreach ($haystack as $item) {
            // Ignore if both have mismatch: order_id
            if ($item['order_id'] !== $needle['order_id']) {
                continue;
            }

            // Ignore if both have mismatch: product_id
            if ($item['product_id'] !== $needle['product_id']) {
                continue;
            }

            // Ignore if both have mismatch: timestamp
            if ($item['timestamp'] !== $needle['submitted']) {
                continue;
            }

            return true;
        }

        return false;
    }


    /**
     * Trims text to a certain number of characters.
     *
     * @since 2.0.0
     *
     * @param string $text
     * @param int $count
     * @param null $more
     *
     * @return string
     */
    public static function getExcerpt($text, $count = 256, $more = null)
    {
        if (null === $more) {
            $more = _x('&hellip;', 'This is an HTML entity; it stands for Horizontal Ellipsis. It looks like 3 dots...');
        }

        // Filter chars
        $excerptNoTags = strip_tags($text);
        $excerpt = substr($excerptNoTags, 0, $count);

        // Add more?
        if (strlen($excerptNoTags) > $count) {
            $excerpt = $excerpt . $more;
        }

        return $excerpt;
    }

    /**
     * Universal function for checking session status.
     *
     * @return bool
     */
    public static function isSessionStarted()
    {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
        return FALSE;
    }
}
