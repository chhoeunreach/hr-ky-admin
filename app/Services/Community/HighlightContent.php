<?php

namespace App\Services\Community;

class HighlightContent
{
    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        static $purifier;
        if (!$purifier) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,ul,ol,li,blockquote,h2,h3,h4,a[href|title],span[style]');
            $config->set('CSS.AllowedProperties', ['text-decoration']);
            $config->set('URI.AllowedSchemes', ['https' => true, 'http' => true, 'mailto' => true]);
            $config->set('Cache.DefinitionImpl', null);
            $purifier = new \HTMLPurifier($config);
        }

        return $purifier->purify($html);
    }
}
