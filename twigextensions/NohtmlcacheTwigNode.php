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

class NohtmlcacheTwigNode extends \Twig_Node
{
    /**
     * {@inheritDoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("if (!defined('NOHTMLCACHE')) {")
            ->raw(PHP_EOL)
            ->indent()
                ->write("define('NOHTMLCACHE', true);")
                ->raw(PHP_EOL)
                ->write('$_SERVER["NOHTMLCACHE"] = true;')
            ->outdent()
            ->raw(PHP_EOL)
            ->write("}")
            ->raw(PHP_EOL);
    }
}
