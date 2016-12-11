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
    function htmlcache_filename($withDirectory = true)
    {
        $protocol = 'http://';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }

        $host = $_SERVER['HTTP_HOST'];
        if (empty($host) && !empty($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        }

        $uri = $_SERVER['REQUEST_URI'];

        $fileName = md5($protocol . $host . $uri) . '.html';
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
}
