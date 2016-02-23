# HTML Cache plugin for Craft CMS

Generate HTML-file based caching for your Craft CMS

> Currently in BETA, requesting feedback regarding speed improvements (cached request vs non-cached request) and other requirements

## Installation

To install HTML Cache, follow these steps:

1. Download & unzip the file and place the `htmlcache-master` directory into your `craft/plugins` directory
2.  -OR- do a `git clone https://github.com/craftapi/htmlcache/htmlcache.git` directly into your `craft/plugins` folder.  You can then update it with `git pull`
3. Install plugin in the Craft Control Panel under Settings > Plugins
4. The plugin folder should be named `htmlcache` for Craft to see it.  GitHub recently started appending `-master` (the branch name) to the name of the folder for zip file downloads.

HTML Cache works on Craft 2.4.x and Craft 2.5.x.

## HTML Cache Overview

Creates a HTML Cached page for any non-cp GET request for the duration of one hour or untill an entry has been updated. Will not serve a cached request when in DEV-mode

## Configuring HTML Cache

No configuration required

## Using HTML Cache

HTML Cache works automatically

## HTML Cache Roadmap

* Settings

## HTML Cache Changelog

### 1.0.1 -- 2016.02.22

* Moved to services instead of plugin file self

### 1.0.0 -- 2016.02.17

* Initial release

Brought to you by [CraftAPI](https://github.com/craftapi)
