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
 * @since     1.0.1
 * @version   1.0.3
 */

namespace Craft;

class Htmlcache_HtmlcacheService extends BaseApplicationComponent
{
    public function checkForCacheFile()
    {
        if (!$this->canCreateCacheFile()) {
            return;
        }

        $file = $this->getCacheFileName();
        if (file_exists($file)) {
            // If file is older than 1 hour, delete it
            // @todo 1.1: setting based
            if (time() - ($fmt = filemtime($file)) >= 3600) {
                unlink($file);
                return;
            }
            $content = file_get_contents($file);

            // Do something with the content
            echo $content;

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
            }
            else {
                // Since it's most likely HTML, display small footprint
                echo PHP_EOL . '<!-- Cached ' . date('Y-m-d H:i:s', $fmt) . ', printed ' . date('Y-m-d H:i:s') . ' -->';
            }

            return craft()->end();
        }
    }
    
    public function canCreateCacheFile()
    {
        // Skip if we're running in devMode
        if (craft()->config->get('devMode') === true) {
            return false;
        }
        // Skip if it's a CP Request
        if (craft()->request->isCpRequest()) {
            return false;
        }
        // Skip if it's a preview request
        if (craft()->request->isLivePreview()) {
            return false;
        }
        
        // Skip if it's a post/ajax request
        if (!craft()->request->isGetRequest()) {
            return false;
        }

        return true;
    }
    
    public function createCacheFile()
    {
        if ($this->canCreateCacheFile()) {
            $content = ob_get_contents();
            $file = $this->getCacheFileName();
            $fp = fopen($file, 'w+');
            if ($fp) {
                fwrite($fp, $content);
                fclose($fp);
            }
            else {
                self::log('HTML Cache could not write cache file "' . $file . '"');
            }
        }
    }
    
    public function clearCacheFiles()
    {
        // @todo split between all/single cache file
        foreach (glob($this->getCacheFileDirectory() . '*.html') as $file) {
            unlink($file);
        }
        return true;
    }
    
    private function getCacheFileName($withDirectory = true)
    {
        $fileName = preg_replace('/__(.+)?/i', '_', preg_replace('/[^a-z0-9]/i', '_', strtolower(trim($_SERVER['REQUEST_URI'], '/')))) . '.cached.html';
        if ($withDirectory) {
            $fileName = $this->getCacheFileDirectory() . $fileName;
        }
        return $fileName;
    }
    
    private function getCacheFileDirectory()
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . '_cached' . DIRECTORY_SEPARATOR;
    }
}
