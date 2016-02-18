<?php
/**
 * HTML Cache plugin for Craft CMS
 *
 * Generate HTML-file based caching for your Craft CMS
 *
 * @author    Chris - CraftAPI
 * @copyright Copyright (c) 2016 CraftAPI
 * @link      https://github.com/craftapi
 * @package   HTMLCache
 * @since     1.0.0
 */

namespace Craft;

class HTMLCachePlugin extends BasePlugin
{
    /**
     * Called after the plugin class is instantiated; do any one-time initialization here such as hooks and events:
     *
     * craft()->on('entries.saveEntry', function(Event $event) {
     *    // ...
     * });
     *
     * or loading any third party Composer packages via:
     *
     * require_once __DIR__ . '/vendor/autoload.php';
     *
     * @return mixed
     */
    public function init()
    {
        $this->checkForCacheFile();
        craft()->attachEventHandler('onEndRequest', function() {
            HTMLCachePlugin::createCacheFile();
        });
    }

    public function checkForCacheFile()
    {
        if (!$this->canCreateCacheFile()) {
            return;
        }

        $file = $this->getCacheFileName();
        if (file_exists($file)) {
            // If file is older than 1 hour, delete it
            if (time() - filemtime($file) >= 3600) {
                unlink($file);
                return;
            }
            $content = file_get_contents($file);
            // Do something with the content
            echo $content;
            craft()->end();
            return true;
        }
    }

    public static function createCacheFile()
    {
        $me = new self;
        if ($me->canCreateCacheFile()) {
            $content = ob_get_contents();
            $file = $me->getCacheFileName();
            $fp = fopen($file, 'w+');
            fwrite($fp, $content);
            fclose($fp);
        }
    }

    private function canCreateCacheFile()
    {
        // Skip if we're running in devMode
        if (craft()->config->get('devMode') === true) {
            return false;
        }
        // Skip if it's a CP Request
        if (craft()->request->isCpRequest()) {
            return false;
        }

        return true;
    }

    private function getCacheFileName()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '_cached' . DIRECTORY_SEPARATOR . md5($_SERVER['REQUEST_URI']) . '.html';
    }

    /**
     * Returns the user-facing name.
     *
     * @return mixed
     */
    public function getName()
    {
         return Craft::t('HTML Cache');
    }

    /**
     * Plugins can have descriptions of themselves displayed on the Plugins page by adding a getDescription() method
     * on the primary plugin class:
     *
     * @return mixed
     */
    public function getDescription()
    {
        return Craft::t('Generate HTML-file based caching for your Craft CMS');
    }

    /**
     * Plugins can have links to their documentation on the Plugins page by adding a getDocumentationUrl() method on
     * the primary plugin class:
     *
     * @return string
     */
    public function getDocumentationUrl()
    {
        return 'https://github.com/craftapi/htmlcache/blob/master/README.md';
    }

    /**
     * Plugins can now take part in Craft’s update notifications, and display release notes on the Updates page, by
     * providing a JSON feed that describes new releases, and adding a getReleaseFeedUrl() method on the primary
     * plugin class.
     *
     * @return string
     */
    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/craftapi/htmlcache/master/releases.json';
    }

    /**
     * Returns the version number.
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * As of Craft 2.5, Craft no longer takes the whole site down every time a plugin’s version number changes, in
     * case there are any new migrations that need to be run. Instead plugins must explicitly tell Craft that they
     * have new migrations by returning a new (higher) schema version number with a getSchemaVersion() method on
     * their primary plugin class:
     *
     * @return string
     */
    public function getSchemaVersion()
    {
        return '1.0.0';
    }

    /**
     * Returns the developer’s name.
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'CraftAPI';
    }

    /**
     * Returns the developer’s website URL.
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'https://github.com/craftapi';
    }

    /**
     * Returns whether the plugin should get its own tab in the CP header.
     *
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }
}
