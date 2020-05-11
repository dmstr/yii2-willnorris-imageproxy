<?php


namespace dmstr\willnorrisImageproxy;

use Yii;

class Url
{
    public static function image($imageSource, $preset = null)
    {
        // sanitize input
        $preset = trim($preset, "/");
        $baseUrl = trim(Yii::$app->settings->get('imgBaseUrl', 'app.frontend'), "/");
        $prefix = trim(Yii::$app->settings->get('imgHostPrefix', 'app.frontend'), "/");
        $imageSourceFull = $imageSource . Yii::$app->settings->get('imgHostSuffix', 'app.frontend');

        // build remote URL
        $remoteUrl = implode('/', array_filter([$prefix, $imageSourceFull]));

        // add HMAC sign key to preset when using imageproxy, see also https://github.com/willnorris/imageproxy#examples
        if (getenv('IMAGEPROXY_SIGNATURE_KEY')) {
            $key = getenv('IMAGEPROXY_SIGNATURE_KEY');
            $preset .= ',s' . strtr(
                    base64_encode(hash_hmac('sha256', $remoteUrl, $key, 1)),
                    '/+',
                    '_-'
                );
        }
        return implode('/', array_filter([$baseUrl, $preset, $remoteUrl]));
    }
}
