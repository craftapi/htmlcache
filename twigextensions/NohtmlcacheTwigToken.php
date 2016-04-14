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

class NohtmlcacheTwigTokenParser extends \Twig_TokenParser
{
    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return 'nohtmlcache';
    }

    /**
     * {@inheritDoc}
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new NohtmlcacheTwigNode([], [], $lineno, $this->getTag());
    }
}
