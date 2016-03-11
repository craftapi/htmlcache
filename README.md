# HTML Cache plugin for Craft CMS

This Craft plugin will generate static HTML files for your website. No need for Reddis/Varnish setups anymore! 

* Improves the speed drastically: 300-1500MS to 2-50MS (depending on server setup) if *ubercache* is enabled from the settings page
* Busts the cache automatically when an entry has been updated
* Cache duration time can be set; defaults to 3600 seconds (1 hour)
* Active development and support through [Craft's Slack](https://craftcms.com/community)
* Make sure to check out the Roadmap (further below) and add your wishes/requirements through an issue

> This plugin is still in *beta*, so please test if this plugin works as expected on a *development* environment _before_ pushing to a production site.

Brought to you by [CraftAPI](https://github.com/craftapi)

## :beers: HTMLCache is Beerware
I've decided to keep this project Open Source/Beerware and to not publish it as a "premium" plugin. If you like the project/find it usefull and you have a few bucks to spare, you're welcome to donate a beer :beer: through Pledgie! 

<a href='https://pledgie.com/campaigns/31263?utm_source=github-craftapi-htmlcache'><img alt='Click here to lend your support to: Craft HTMLCache donations and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/31263.png?skin_name=chrome' border='0' ></a>

## Installation

To install HTML Cache, follow these steps:

1. Download & unzip the file and place the `htmlcache-master` directory into your `craft/plugins` directory
2.  -OR- do a `git clone https://github.com/craftapi/htmlcache/htmlcache.git` directly into your `craft/plugins` folder.  You can then update it with `git pull`
3. Install plugin in the Craft Control Panel under Settings > Plugins
4. The plugin folder should be named `htmlcache` for Craft to see it.  GitHub recently started appending `-master` (the branch name) to the name of the folder for zip file downloads.

HTML Cache works on Craft 2.5.x and Craft 2.6.x, both PHP 5.6 and 7.0

## HTML Cache Overview

Creates a HTML Cached page for any non-cp GET request for the duration of one hour (configurable) or untill an entry has been updated. Will not serve a cached request when in DEV-mode

## Configuring HTML Cache

After installing HTML Cache, you'll be redirected to the settings page.

## Using HTML Cache

HTML Cache has a settings page where you can enable/disable both normal and ubercache. The ubercache alters the public/index.php file to include extra functionality before Craft gets initialised, eliminating the TTFB caused by Yii. 

## HTML Cache Roadmap

* Fix cached CSRF-requests
* CP Widget with amount of cache files and size, plus a button to purge the cache directly
* Move files inside _cached directory to storage/runtime directory as those permissions should work at all times
* Cache bust by webhook
* 1.1: Improve cache busting by checking the impact of an updated entry; do we really need to bust everything?

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
