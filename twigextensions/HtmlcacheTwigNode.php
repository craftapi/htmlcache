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

class HtmlcacheTwigNode extends \Twig_Node
{
    private static $cacheCount = 1;

    /**
     * {@inheritDoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $i = static::$cacheCount++;

        // Get attributes
        $conditions         = $this->getNode('conditions');
        $ignoreConditions   = $this->getNode('ignoreConditions');
        $expiration         = $this->getNode('expiration');
        $key                = $this->getNode('key');
        $tags               = $this->getNode('tags');

        // Get attributes
        $durationNum        = $this->getAttribute('durationNum');
        $durationUnit       = $this->getAttribute('durationUnit');
        $global             = $this->getAttribute('global') ? 'true' : 'false';

        // Create a unique key

        if (empty($key)) {
            $key            = preg_replace('/[^a-zA-Z0-9-]/', '-', implode('-', $this->getAttribute('backtrace')));
        }

        $compiler
                ->addDebugInfo($this)
                ->write('$cacheService = \Craft\craft()->htmlcache_htmlcache;')
                ->write(PHP_EOL)
                ->write("\$ignoreCache{$i} = (\$cacheService->canCreateCacheBlock()");

        if ($conditions) {
            $compiler 
                ->raw(' || !(')
                    ->subcompile($conditions)
                ->raw(')');
        }
        elseif ($ignoreConditions) {
            $compiler
                ->raw(' || (')
                    ->subcompile($ignoreConditions)
                ->raw(')');
        }

        $compiler
                ->raw(');')
                ->write(PHP_EOL)

                ->write("if (!\$ignoreCache{$i}) {")
                ->write(PHP_EOL)
                    ->indent()
                        ->write("\$cacheKey{$i} = '" . $key . "';")
                        ->write(PHP_EOL)
                        ->write("\$cacheBody{$i} = \$cacheService->getCacheBlock(\$cacheKey{$i}, {$global});")
                        ->write(PHP_EOL)
                    ->outdent()
                ->write('} else {')
                ->write(PHP_EOL)
                    ->indent()
                        ->write("\$cacheBody{$i} = null;")
                        ->write(PHP_EOL)
                    ->outdent()
                ->write('}')
                ->write(PHP_EOL)

                ->write("if (\$cacheBody{$i} === null) {")
                ->write(PHP_EOL)
                    ->indent()
                        ->write("ob_start();")
                        ->write(PHP_EOL)
                        ->subcompile($this->getNode('body'))
                        ->write(PHP_EOL)
                        ->write("\$cacheBody{$i} = ob_get_clean();")
                        ->write(PHP_EOL)
                        ->write("if (!\$ignoreCache{$i}) {")
                        ->write(PHP_EOL)
                            ->indent()
                                ->write("\$cacheService->setCacheBlock(\$cacheKey{$i}, {$global}, ");

        if ($durationNum) {
            if ($durationUnit == 'week') {
                if ($durationNum == 1) {
                    $durationNum = 7;
                    $durationUnit = 'days';
                }
                else {
                    $durationUnit = 'weeks';
                }
            }
            $compiler           ->raw("'+{$durationNum} {$durationUnit}'");
        }
        else {
            $compiler           ->raw('null');
        }

        $compiler               ->raw(', ');

        if ($expiration) {
            $compiler           ->subcompile($expiration);
        }
        else {
            $compiler           ->raw('null');
        }

        $compiler               ->raw(", \$cacheBody{$i});")
                                ->write(PHP_EOL)
                            ->outdent()
                        ->write('}')
                        ->write(PHP_EOL)
                    ->outdent()
                ->write('}')
                ->write(PHP_EOL)
                ->write("echo \$cacheBody{$i};")
                ->write(PHP_EOL);
    }    
}
