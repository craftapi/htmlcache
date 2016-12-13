<?php
/**
 * HTMLCache plugin for Craft CMS
 *
 * Managing HTMLCache, like a boss
 *
 * @author    Chris - CraftAPI
 * @copyright Copyright (c) 2016 CraftAPI
 * @link      https://github.com/craftapi
 * @package   HTMLCache
 * @since     1.0.4
 * @version   1.0.4.1
 */

if (!function_exists('htmlcache_filename')) {
    
    /**
     * Returns unique identifier based on the current requested domain and path
     *
     * Note: if you use nginx and you're forwarding to 
     * php-fpm/nginx/apache, note that that `HTTP_HOST` overrides `SERVER_NAME`
     *
     * @todo: v1.1 implement safe-to-cache URL-queries to make more generic fp-cache
     *
     * @param $withDirectory bool Return 
     * @returns $fileName string MD5 hash
     */
    function htmlcache_filename($withDirectory = true)
    {
        $pieces = [];
        $pieces['protocol'] = 'http://';
        if (
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
            $pieces['protocol'] = 'https://';
        }
        
        $pieces['host'] = 'localhost';
        if (!empty($_SERVER['SERVER_NAME'])) {
            $pieces['host'] = $_SERVER['SERVER_NAME'];
        }
        if (!empty($_SERVER['HTTP_HOST'])) {
            $pieces['host'] = $_SERVER['HTTP_HOST'];
        }
        if (!empty($_SERVER['HTTP_PORT'])) {
            $pieces['host'] .= $_SERVER['HTTP_PORT'];
        }
	
        $pieces['uri'] = '';
        if (!empty($_SERVER['REQUEST_URI'])) {
		    $pieces['uri'] = $_SERVER['REQUEST_URI'];
        }
        
        $extArray = array_merge([], explode('.', $pieces['uri']));
        $pieces['ext'] = 'html';
        if (
            is_array($extArray) 
            && count($extArray) 
            && in_array(
                end($extArray), 
                [
                    'css', 'js', 
                    'jpg', 'jpeg', 'gif', 'bmp', 'png'
                ]
            )
        ) {
            $pieces['ext'] = end($extArray);
        }

        $fileName = md5(implode('', $pieces)) . '.' . $pieces['ext'];
        
        if ($withDirectory) {
            $fileName = htmlcache_directory() . $fileName;
        }
        
        return $fileName;
    }

    function htmlcache_directory()
    {
        if (defined('CRAFT_STORAGE_PATH')) {
            return CRAFT_STORAGE_PATH . 'runtime' . DIRECTORY_SEPARATOR . 'htmlcache' . DIRECTORY_SEPARATOR;
        }
        // Fallback to default directory
        return dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'htmlcache' . DIRECTORY_SEPARATOR;
    }

    function htmlcache_indexEnabled($enabled = true)
    {
        $replaceWith = '/*HTMLCache Begin*/if (defined(\'CRAFT_PLUGINS_PATH\')) {require_once CRAFT_PLUGINS_PATH . DIRECTORY_SEPARATOR . \'htmlcache\' . DIRECTORY_SEPARATOR . \'functions\' . DIRECTORY_SEPARATOR . \'htmlcache.php\';} else {require_once str_replace(\'index.php\', \'../plugins\' . DIRECTORY_SEPARATOR . \'htmlcache\' . DIRECTORY_SEPARATOR . \'functions\' . DIRECTORY_SEPARATOR . \'htmlcache.php\', $path);}htmlcache_checkCache();/*HTMLCache End*/';
        $replaceFrom = 'require_once $path;';
        $file = $_SERVER['SCRIPT_FILENAME'];
        $contents = file_get_contents($file);

        if ($enabled) {
            if (stristr($contents, 'htmlcache') === false) {
                file_put_contents($file, str_replace($replaceFrom, $replaceWith . $replaceFrom, $contents));
            }
        } else {
            $beginning = '/*HTMLCache Begin*/';
            $end = '/*HTMLCache End*/';

            $beginningPos = strpos($contents, $beginning);
            $endPos = strpos($contents, $end);

            if ($beginningPos !== false && $endPos !== false) {
                $textToDelete = substr($contents, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
                file_put_contents($file, str_replace($textToDelete, '', $contents));
            }
        }
    }

    function htmlcache_checkCache($direct = true)
    {
        $file = htmlcache_filename(true);
        if (file_exists($file)) {
            if (file_exists($settingsFile = htmlcache_directory() . 'settings.json')) {
                $settings = json_decode(file_get_contents($settingsFile), true);
            } else {
                $settings = ['cacheDuration' => 3600];
            }
            if (time() - ($fmt = filemtime($file)) >= $settings['cacheDuration']) {
                unlink($file);
                return false;
            }
            $content = file_get_contents($file);

            // Check the content type
            $isJson = false;
            if ($content[0] == '[' || $content[0] == '{') {
                // JSON?
                @json_decode($content);
                if (json_last_error() == JSON_ERROR_NONE) {
                    $isJson = true;
                }
            }

            if ($isJson) {
                // Add extra JSON headers?
                if ($direct) {
                    header('Content-type:application/json');
                }
                echo $content;
            } else {
                if ($direct) {
                    // patch for #30
                    $fileExt = explode('.', $file);
                    $fileExt = end($fileExt);
                    // @todo v1.1 implement config.defaultMimetype
                    $contentType = 'text/html';//craft()->config->get('defaultMimetype', 'text/html');
                    // @todo v1.1 implement config.defaultMimetypeCharset
                    $contentCharset = 'UTF-8';//craft()->config->get('defaultMimetypeCharset', 'UTF-8');
                    // @todo v1.1 implement default ext=>charsets enabled/disabled
                    switch ($fileExt) {
                        case 'css':
                        case 'js':
                            $contentType = 'text/' . $fileExt;
                            break;
                            
                        case 'jpg':
                        case 'jpeg':
                        case 'bmp':
                        case 'gif':
                        case 'png':
                            $contentType = 'image/' . $fileExt;
                            break;
                    }
                    // @todo v1.1 implement config.disableHeaders
                    //if (!craft()->config->get('disableHeaders', false)) {
                    if (
                        isset($settings['disableHeaders']) && $settings['disableHeaders'] === true
                        || !isset($settings['disableHeaders'])
                    ) {
                        // @todo v1.1 implement config.disableCharset
                        if (
                            //!craft()->config->get('disableMimeCharset', false)
                            /*&&*/ strstr($contentType, 'text/') !== false 
                        ) {
                            $contentType .= ';' . $contentCharset;
                        }
                        header('Content-type:' . $contentType);
                    }
                }
                // Output the content
                echo $content;

                // @todo v1.1 implement test
                //if (craft()->config->get('enableFootprint', false)) {
                if (isset($settings['enableFootprint']) && $settings['enableFootprint'] === true) {
                    $ms = 0.00000000;
                    if (!empty($_SERVER['REQUEST_TIME_FLOAT'])) {
                        $ms = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 8);
                    }
                    echo PHP_EOL . '<!-- Cached ' . ($direct ? 'direct ' : 'later ') . date('Y-m-d H:i:s', $fmt) . ', displayed ' . date('Y-m-d H:i:s') . ', generated in ' . $ms . 's -->';
                }
            }

            // Exit the response if called directly
            if ($direct) {
                exit;
            }
        }
        return true;
    }
}
