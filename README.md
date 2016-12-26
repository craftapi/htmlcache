# HTML Cache plugin for Craft CMS

This Craft plugin will generate static HTML files for your website. No need for Reddis/Varnish setups anymore! 

* Improves the speed drastically: 300-1500MS to 2-50MS (depending on server setup) if *ubercache* is enabled from the settings page
* Busts the cache automatically when an entry has been updated
* Cache duration time can be set; defaults to 3600 seconds (1 hour)
* Active development and support through [Craft's Slack](https://craftcms.com/community)
* Make sure to check out the Roadmap (further below) and add your wishes/requirements through an issue
* Respects redirects and CSRF-tokens

> This plugin is still in *beta*, so please test if this plugin works as expected on a *development* environment _before_ pushing to a production site.

Brought to you by [CraftAPI](https://github.com/craftapi)

## :beers: HTMLCache is Beerware
I've decided to keep this project Open Source/Beerware and to not publish it as a "premium" plugin. If you like the project/find it useful and you have a few bucks to spare, you're welcome to donate a beer :beer: through Pledgie! 

<a href='https://pledgie.com/campaigns/31263?utm_source=github-craftapi-htmlcache'><img alt='Click here to lend your support to: Craft HTMLCache donations and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/31263.png?skin_name=chrome' border='0' ></a>

A big thank you for all donations so far!

## Installation

To install HTML Cache, follow these steps:

1. Download & unzip the file and place the `htmlcache` directory into your `craft/plugins` directory
2. Install plugin in the Craft Control Panel under `Settings > Plugins`
3. Set your preferred settings 

HTML Cache works on Craft 2.5.x and Craft 2.6.x, both PHP 5.6 and 7.0

## HTML Cache Overview

Creates a HTML Cached page for any non-cp GET request for the duration of one hour (configurable) or until an entry has been updated. Will not serve a cached request when in DEV-mode

## Configuring HTML Cache

After installing HTML Cache, you'll be redirected to the settings page. Afterwards you can find the settings in the Craft Control Panel under `Settings > Plugins > HTMLCache Settings`

If you've enabled the webhook, you can call the webhook at `https://yourdomain.dev/actions/htmlcache/webhook?key=XXXX`

## Using HTML Cache

HTML Cache has a settings page where you can enable/disable both normal and _ubercache_. The _ubercache_ alters the public/index.php file to include extra functionality before Craft gets initialised, eliminating the TTFB caused by Yii 1 and any slow databases (400+ queries per page?). 

## HTML Cache Roadmap

* done: _Fix cached CSRF-requests_
* CP Widget with amount of cache files and size, plus a button to purge the cache directly
* done: _Move files inside cached directory to storage/runtime directory as those permissions should work at all times_
* done: _Cache bust by webhook_
* done: _Improve cache busting by checking the impact of an updated entry; do we really need to bust everything?_
* Implement Twig Tags to prevent pages from getting cached

## HTML Cache Changelog

### 1.0.6 -- 2016.12.26

* Fixes CSRF requests and redirects
* Enables cache busting by webhook
* Busts single cache instead of all pages

### 1.0.5.1 -- 2016.12.15

* Bugfixes and improvements

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
