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
 * @version   1.0.5
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
            \htmlcache_checkCache(false);

            return craft()->end();
        }
    }
    
    public function canCreateCacheFile()
    {
        // Skip if we're running in devMode
        if (craft()->config->get('devMode') === true || defined('NOHTMLCACHE') || isset($_SERVER['NOHTMLCACHE'])) {
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

    public function canCreateCacheBlock()
    {
        // When in development mode
        if (craft()->config->get('devMode') === true) {
            // Make sure we check if it's allowed
            return (int)!\htmlcache_getSettings()->enableBlocksOnDev;
        }

        // Skip if it's a live preview
        if (craft()->request->isLivePreview()) {
            return false;
        }

        return true;
    }
    
    public function createCacheFile()
    {
        if ($this->canCreateCacheFile()) {
            $content = ob_get_contents();
            if (!empty($content)) {
                $file = $this->getCacheFileName();
                $fp = fopen($file, 'w+');
                if ($fp) {
                    fwrite($fp, $content);
                    fclose($fp);
                }
            }
        }
    }

    public function getCacheBlock($key, $global = false)
    {
        $file = $this->getCacheFileDirectory() . $key . '.cached.html';
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return null;
    }

    public function setCacheBlock($key, $global = false, $duration = null, $expiration = null, $content = null)
    {
        $file = $this->getCacheFileDirectory() . $key . '.cached.html';
        $fp = fopen($file, 'w+');
        if ($fp) {
            fwrite($fp, $content);
            fclose($fp);
        }
        return true;
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
        return \htmlcache_filename($withDirectory);
    }
    
    private function getCacheFileDirectory()
    {
        return \htmlcache_directory();
    }
}
