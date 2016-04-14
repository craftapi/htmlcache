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
 * @since     1.0.5
 * @version   1.0.5
 */

namespace Craft;
use Twig_Extension;

class HtmlcacheTwigExtension extends \Twig_Extension
{
    public $filtersInitialised;

    public function init()
    {
        return parent::init();
    }

    public function getName()
    {
        return 'htmlcache';
    }

    public function getTokenParsers()
    {
        return [
            new HtmlcacheTwigTokenParser,
            new NohtmlcacheTwigTokenParser
        ];
    }
}
