<?php


namespace dmstr\willnorrisImageproxy;

use Yii;

class Url
{

    /**
     * internal cache var that is used to get params from settings or env only once
     *
     * @var array
     */
    protected static $_paramCache = [];

    public static function image($imageSource, $preset = '')
    {
        // sanitize input
        $imageSource = ltrim($imageSource, '/');
        if (empty($imageSource)) {
            return null;
        }
        $preset = trim($preset, "/");
        $baseUrl = static::getBaseUrl();
        $prefix = static::getPrefix();
        $imageSourceFull = $imageSource . static::getSuffix();
        $signatureKey = static::getSignatureKey();

        // build remote URL
        $remoteUrl = implode('/', array_filter([$prefix, $imageSourceFull]));

        // add HMAC sign key to preset when using imageproxy, see also https://github.com/willnorris/imageproxy#examples
        if ($signatureKey) {
            $preset .= ',s' . strtr(
                    base64_encode(hash_hmac('sha256', $remoteUrl, $signatureKey, 1)),
                    '/+',
                    '_-'
                );
        }
        return implode('/', array_filter([$baseUrl, $preset, $remoteUrl]));
    }

    /**
     * if set, will be used as HMAC sign key for imageproxy preset
     *
     * @return string|null
     */
    protected static function getSignatureKey()
    {
        if (!isset(static::$_paramCache['signatureKey'])) {
            static::$_paramCache['signatureKey'] = getenv('IMAGEPROXY_SIGNATURE_KEY');
        }
        return static::$_paramCache['signatureKey'];
    }

    /**
     * baseUrl for image src urls
     *
     * @return string|null
     */
    protected static function getBaseUrl()
    {
        if (!isset(static::$_paramCache['baseUrl'])) {
            static::$_paramCache['baseUrl'] = trim(Yii::$app->settings->get('imgBaseUrl', 'app.frontend'), "/");
        }
        return static::$_paramCache['baseUrl'];
    }

    /**
     * prefix used for imageSource
     *
     * @return string|null
     */
    protected static function getPrefix()
    {
        if (!isset(static::$_paramCache['prefix'])) {
            static::$_paramCache['prefix'] = trim(Yii::$app->settings->get('imgHostPrefix', 'app.frontend'), "/");
        }
        return static::$_paramCache['prefix'];
    }

    /**
     * suffix that will be appended to imageUrls
     *
     * @return string|null
     */
    protected static function getSuffix()
    {
        if (!isset(static::$_paramCache['suffix'])) {
            static::$_paramCache['suffix'] = Yii::$app->settings->get('imgHostSuffix', 'app.frontend');
        }
        return static::$_paramCache['suffix'];
    }

}
