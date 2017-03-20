<?php

/**
 * Created by PhpStorm.
 * User: Abdel
 * Date: 7/9/2015
 * Time: 9:03 PM
 */
class eKomiAPI
{
    /**
     * Dynamically call current API methodes.
     *
     * Used initially to make it possible for scripts to get reviews, by specefying
     * either shop or product reviews.
     *
     * @param string $method Methode to call withing the eKomiAPI class.
     * @param array $args Arguments to pass to the called methode.
     *
     * @return mixed called methode's return data.
     */
    public static function apiCall(
        $method,
        $args
    )
    {

        return call_user_func_array(__CLASS__ . '::' . $method, $args);
    }

    /**
     * Call API and get product.
     *
     * Delivers the mail settings for one shop, including the mail text in html
     * and plain and a link to the certificate page.
     *
     * @param string $ekomiInterfaceID
     * @param string $ekomiInterfacePassword
     * @param string $version
     * @param string $type
     * @param string $charset
     * @param string $content
     *
     * @return mixed the value encoded in <i>json</i> in appropriate PHP type.
     */
    public static function apiGetSettings(
        $ekomiInterfaceID,
        $ekomiInterfacePassword,
        $version = 'PRC-2.3',
        $type = 'json',
        $charset = 'utf-8',
        $content = 'request'
    )
    {

        $URL = 'http://api.ekomi.de/v3/getSettings?auth=' . $ekomiInterfaceID . '|' . $ekomiInterfacePassword . '&version=' . $version . '&charset=' . $charset . '&content=' . $content . '&type=' . $type;

        return json_decode(file_get_contents($URL), true);
    }

    /**
     * Get a snapshot of the reviews statistics for the last year.
     *
     * Delivers data which can be used for creating a custom widget
     * Internal info : This call uses a 12 months average for shops that are listed in �shopsWithMovingAverageHack� in
     * ekomi_settings table and returns the all-time average for shops which are not. As of v3, it no longer returns
     * hotel-specific data (fb_avg_room, rating_room etc.)
     *
     * @param string $ekomiInterfaceID Interface-ID
     * @param string $ekomiInterfacePassword Interface-Password
     * @param string $version Script-Version. For self-build. Precede with �cust-�
     * @param string $type Output type. Either �csv� (default) or �json�
     * @param string $charset Character encoding. Either �iso� (default) or �utf-8� .
     *
     * @return mixed the value encoded in <i>json</i> in appropriate PHP type.
     */
    public static function apiGetSnapshot(
        $ekomiInterfaceID,
        $ekomiInterfacePassword,
        $version = 'PRC-2.3',
        $type = 'json',
        $charset = 'utf-8'
    )
    {
        // Api results
        $URL = 'http://api.ekomi.de/v3/getSnapshot?auth=' . $ekomiInterfaceID . '|' . $ekomiInterfacePassword . '&version=' . $version . '&type=' . $type . '&charset=' . $charset;

        return json_decode(file_get_contents($URL), true);
    }


    /**
     * Call API and get product.
     *
     * Returns description, meta data and all-time average rating for a product
     * which has been uploaded with putProduct. Can be used to verify all information
     * stored through putProduct has been uploaded correctly.
     *
     * @param string $ekomiInterfaceID
     * @param string $ekomiInterfacePassword
     * @param $productID
     * @param string $version
     * @param string $type
     * @param string $charset
     *
     * @return mixed the value encoded in <i>json</i> in appropriate PHP type.
     */
    public static function apiGetProduct(
        $ekomiInterfaceID,
        $ekomiInterfacePassword,
        $productID,
        $version = 'PRC-2.3',
        $type = 'json',
        $charset = 'utf-8'
    )
    {
        $URL = 'http://api.ekomi.de/v3/getProduct?auth=' . $ekomiInterfaceID . '|' . $ekomiInterfacePassword . '&version=' . $version . '&product=' . $productID . '&type=' . $type . '&charset=' . $charset;

        return json_decode(file_get_contents($URL), true);
    }

    /**
     * Call API and get shop reviews.
     *
     * Delivers the reviews for a shop, including the rating, the text the
     * clients sent and comments from the shop owners.
     *
     * @param string $ekomiInterfaceID
     * @param string $ekomiInterfacePassword
     * @param string $version
     * @param string $type
     * @param string $charset
     *
     * @return mixed the value encoded in <i>json</i> in appropriate PHP type.
     */
    public static function getFeedback(
        $ekomiInterfaceID,
        $ekomiInterfacePassword,
        $version = 'PRC-2.3',
        $type = 'json',
        $charset = 'utf-8'
    )
    {

        $URL = 'http://api.ekomi.de/v3/getFeedback?auth=' . $ekomiInterfaceID . '|' . $ekomiInterfacePassword . '&version=' . $version . '&charset=' . $charset . '&type=' . $type;

        return json_decode(file_get_contents($URL), true);
    }

    /**
     * Call API and get product feedbacks.
     *
     * Delivers the reviews for a shop, including the rating, the text the
     * clients sent and comments from the shop owners.
     *
     * @param string $interfaceID
     * @param string $interfacePassword
     * @param string $range
     * @param string $type
     * @param string $charset
     *
     * @return mixed the value encoded in <i>json</i> in appropriate PHP type.
     */
    public static function apiGetProductfeedback($interfaceID, $interfacePassword, $range = 'all', $type = 'json', $charset = 'utf-8')
    {
        $URL = 'http://api.ekomi.de/v3/getProductfeedback?interface_id=' . $interfaceID . '&interface_pw=' . $interfacePassword . '&type=' . $type . '&charset=' . $charset . '&range=' . $range;

        // Get the reviews
        $reviews = file_get_contents($URL);

        // log the results
        if (!$reviews) {
        }

        return json_decode($reviews, true);
    }

}