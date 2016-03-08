# HTML Cache plugin for Craft CMS

Generate HTML-file based caching for your Craft CMS

> Currently in BETA, requesting feedback regarding speed improvements (cached request vs non-cached request) and other requirements

## Installation

To install HTML Cache, follow these steps:

1. Download & unzip the file and place the `htmlcache-master` directory into your `craft/plugins` directory
2.  -OR- do a `git clone https://github.com/craftapi/htmlcache/htmlcache.git` directly into your `craft/plugins` folder.  You can then update it with `git pull`
3. Install plugin in the Craft Control Panel under Settings > Plugins
4. The plugin folder should be named `htmlcache` for Craft to see it.  GitHub recently started appending `-master` (the branch name) to the name of the folder for zip file downloads.

HTML Cache works on Craft 2.4.x and Craft 2.5.x, both PHP 5.6 and 7.0

## HTML Cache Overview

Creates a HTML Cached page for any non-cp GET request for the duration of one hour (configurable) or untill an entry has been updated. Will not serve a cached request when in DEV-mode

## Configuring HTML Cache

After installing HTML Cache, you'll be redirected to the settings page.

## Using HTML Cache

HTML Cache has a settings page where you can enable/disable both normal and ubercache. The ubercache alters the public/index.php file to include extra functionality before Craft gets initialised, eliminating the TTFB caused by Yii. 

## HTML Cache Roadmap

* Fix cached CSRF-requests
* CP Widget with amount of cache files and size, plus a button to purge the cache directly

## HTML Cache Changelog

### 1.0.4-2 -- 2016.03.08

* Bugfix

### 1.0.4 -- 2016.03.07

* Moved a few functions to a standalone file
* Plugin settings including cache duration
* When enabling the UberCache, the public/index.php file will be altered to include the standalone file
* This improves the speed from 300-1500ms to about  2-25ms, depending on your server ;)

### 1.0.3 -- 2016.03.04

* Fixed case-sensitivity bug in HtmlcachePlugin.php and the Htmlcache_HtmlcacheService.php, causing an (in)visible error that did not enable the plugin

### 1.0.2 -- 2016.03.03

* Fixed case-sensitivity bug in HTMLCachePlugin.php, causing an invisible error that did not enable the plugin

### 1.0.1 -- 2016.02.22

* Moved to services instead of plugin file self

### 1.0.0 -- 2016.02.17

* Initial release

Brought to you by [CraftAPI](https://github.com/craftapi)
