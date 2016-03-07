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
 * @version   1.0.4
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
        return \htmlcache_filename($withDirectory);
    }
    
    private function getCacheFileDirectory()
    {
        return \htmlcache_directory();
    }
}
