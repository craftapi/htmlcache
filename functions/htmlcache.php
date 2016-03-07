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
 * @version   1.0.4
 */

if (!function_exists('htmlcache_filename')) {
    function htmlcache_filename($withDirectory = true)
    {
        $_SERVER['REQUEST_URI'] = strtolower(trim($_SERVER['REQUEST_URI'], '/'));
        if (empty($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = 'index';
        }
        $fileName = preg_replace('/__(.+)?/i', '_', preg_replace('/[^a-z0-9]/i', '_', $_SERVER['REQUEST_URI'])) . '.cached.html';
        if ($withDirectory) {
            $fileName = htmlcache_directory() . $fileName;
        }
        return $fileName;
    }

    function htmlcache_directory()
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . '_cached' . DIRECTORY_SEPARATOR;
    }

    function htmlcache_indexEnabled($enabled = true)
    {
        $replaceWith = '/*HTMLCache Begin*/require_once str_replace(\'index.php\', \'../plugins\' . DIRECTORY_SEPARATOR . \'htmlcache\' . DIRECTORY_SEPARATOR . \'functions\' . DIRECTORY_SEPARATOR . \'htmlcache.php\', $path);htmlcache_checkCache();/*HTMLCache End*/';
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
        $file = htmlcache_filename(true);
        if (file_exists($file)) {
            if (file_exists($settingsFile = htmlcache_directory() . 'settings.json')) {
                $settings = json_decode(file_get_contents($settingsFile), true);
            }
            else {
                $settings = ['cacheDuration' => 3600];
            }
            if (time() - ($fmt = filemtime($file)) >= $settings['cacheDuration']) {
                unlink($file);
                return;
            }
            $content = file_get_contents($file);

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

                // Since it's most likely HTML, display small footprint
                echo PHP_EOL . '<!-- Cached ' . ($direct ? 'direct ' : 'later ') . date('Y-m-d H:i:s', $fmt) . ', displayed ' . date('Y-m-d H:i:s') . ' -->';
            }

            // Exit the response if called directly
            if ($direct) {
                exit;
            }
        }
    }
}
