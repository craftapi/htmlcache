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
 * @version   1.0.5
 */

if (!function_exists('htmlcache_filename')) {
    function htmlcache_filename($withDirectory = true)
    {
        $uri = strtolower($_SERVER['HTTP_HOST'] . trim($_SERVER['REQUEST_URI'], '/') . $_SERVER['QUERY_STRING']);
        if (empty($uri)) {
            $uri = 'index';
        }
        $fileName = preg_replace('/__(.+)?/i', '_', preg_replace('/[^a-z0-9]/i', '_', $uri)) . '.cached.html';
        if ($withDirectory) {
            $fileName = htmlcache_directory() . $fileName;
        }
        return $fileName;
    }

    function htmlcache_directory()
    {
        if (function_exists('craft')) {
            return craft()->path->getTempPath() . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . '_cached.';
        }
        // Fallback to default directory
        return dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . '_cached.';
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
        }
        else {
            file_put_contents($file, str_replace($replaceWith, '', $contents));
        }
    }

    function htmlcache_checkCache($direct = true)
    {
        if (defined('NOHTMLCACHE')) {
            return false;
        }
        if (isset($_SERVER['NOHTMLCACHE'])) {
            return false;
        }
        //echo '-not-defined-nohtmlcache-';
        $file = htmlcache_filename(true);
        if (file_exists($file)) {
            $settings = htmlcache_getSettings();
            if (time() - ($fmt = filemtime($file)) >= $settings->cacheDurationIndex) {
                unlink($file);
                return;
            }
            $content = file_get_contents($file);
            if (empty($content)) {
                unlink($file);
                return false;
            }

            // Do something with the content?
            //echo $content;

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
            }
            else {
                if ($direct) {
                    header('Content-type:text/html;charset=UTF-8');
                }
                // Output the content
                echo $content;
            }

            // Exit the response if called directly
            if ($direct) {
                exit;
            }
        }
        return true;
    }

    function htmlcache_getSettings() 
    {
        if (file_exists($settingsFile = htmlcache_directory() . 'settings.json')) {
            $settings = json_decode(file_get_contents($settingsFile));
        }
        else if (function_exists('craft')) {
            $settings = json_decode(json_encode(craft()->plugins->getPlugin('htmlcache')->getSettings()));
        }
        else {
            $settings = json_decode('{
                "cacheDurationIndex": 3600,
                "cacheDurationBlock": 3600,
                "enableIndex":0,
                "enableGeneral":1,
                "enableBlocksOnDev":0
            }');
        }
        return $settings;
    }
}
