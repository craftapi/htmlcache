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
 * @version   1.0.5
 */

namespace Craft;

class HtmlcachePlugin extends BasePlugin
{
    /**
     * Call the service to check if we already have a cache file; register events
     *
     * @return mixed
     */
    public function init()
    {
        if (!function_exists('\htmlcache_index')) {
            include_once 'functions/htmlcache.php';
        }

        if (!$this->isEnabled) {
            \htmlcache_indexEnabled(false);
        }

        if ($this->isInstalled && $this->isEnabled) {
            craft()->htmlcache_htmlcache->checkForCacheFile();
            craft()->attachEventHandler('onEndRequest', function() {
                craft()->htmlcache_htmlcache->createCacheFile();
            });
            craft()->on('entries.saveEntry', function(Event $event) {
                craft()->htmlcache_htmlcache->clearCacheFiles();
            });
        }
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
        return '1.0.5';
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

    public function hasSettings()
    {
        return true;
    }

    /**
     * Returns whether the plugin should get its own tab in the CP header.
     *
     * @return bool
     */
    protected function defineSettings()
    {
        return [
            'cacheDurationIndex'    => [AttributeType::Mixed, 
                                        'required'  => true, 
                                        'default'   => 3600],
            'cacheDurationBlock'    => [AttributeType::Mixed, 
                                        'required'  => true, 
                                        'default'   => 3600],
            'enableIndex'           => [AttributeType::Bool, 
                                        'default'   => 0, 
                                        'required'  => true],
            'enableGeneral'         => [AttributeType::Bool, 
                                        'default'   => 1, 
                                        'required'  => true],
            'enableBlocksOnDev'     => [AttributeType::Bool, 
                                        'default'   => 0, 
                                        'required'  => true]
        ];
    }

    /**
     * Returns the plugin settings
     *
     * @return html
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('htmlcache/_settings', ['settings' => $this->getSettings()]);
    }

    /**
     * Process the settings and check if the index needs to be altered
     *
     * @return function
     */
    public function setSettings($values) 
    {
        if (!function_exists('\htmlcache_indexEnabled')) {
            include_once 'functions/htmlcache.php';
        }

        if (!empty($values['htmlcacheSettingsForm'])) {
            // Write these settings to a .json file for offline reference
            $fp = fopen(htmlcache_directory() . 'settings.json', 'w+');
            if ($fp) {
                fwrite($fp, json_encode($values));
                fclose($fp);
            }
            \htmlcache_indexEnabled($values['enableIndex'] == 1 ? true : false);
            // Check if it actually worked
            if (stristr(file_get_contents($_SERVER['SCRIPT_FILENAME']), 'htmlcache') === false && $values['enableIndex'] == 1) {
                craft()->userSession->setError(Craft::t('The file ' . $_SERVER['SCRIPT_FILENAME'] . ' could not be edited'));
                return false;
            }
        }

        if (!empty($values['purgeCache'])) {
            craft()->htmlcache_htmlcache->clearCacheFiles();
        }
        return parent::setSettings($values);
    }
    
    /**
     * Set the default settings
     *
     * @return function
     */
    public function onAfterInstall()
    {
        craft()->request->redirect(UrlHelper::getCpUrl('/settings/plugins/htmlcache'));
    }

    /**
     * Removes the index.php modification if set
     *
     * @return bool
     */
    public function onBeforeUninstall()
    {
        // Make sure to delete any reference in the public/index.php file
        \htmlcache_indexEnabled(false);
    }

    public function addTwigExtension()
    {
        Craft::import('plugins.htmlcache.twigextensions.HtmlcacheTwigExtension');
        Craft::import('plugins.htmlcache.twigextensions.HtmlcacheTwigTokenParser');
        Craft::import('plugins.htmlcache.twigextensions.HtmlcacheTwigNode');
        Craft::import('plugins.htmlcache.twigextensions.NohtmlcacheTwigTokenParser');
        Craft::import('plugins.htmlcache.twigextensions.NohtmlcacheTwigNode');
        return new HtmlcacheTwigExtension;
    }
}
