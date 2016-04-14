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

class HtmlcacheTwigTokenParser extends \Twig_TokenParser
{
    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return 'htmlcache';
    }

    /**
     * {@inheritDoc}
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $nodes = array(
            'expiration'        => null,
            'conditions'        => null,
            'ignoreConditions'  => null,
            'key'               => null,
            'body'              => null,
        );

        $attributes = array(
            'global'            => false,
            'durationNum'       => null,
            'durationUnit'      => null,
            'backtrace'         => [
                'file'              => $this->parser->getFilename(),
                //'token'             => $this->parser->getVarName(),
                'line'              => $token->getLine()
            ]
        );

        if ($stream->test(\Twig_Token::NAME_TYPE, 'globally'))
        {
            $attributes['global'] = true;
            $stream->next();
        }

        if ($stream->test(\Twig_Token::NAME_TYPE, 'using'))
        {
            $stream->next();
            $stream->expect(\Twig_Token::NAME_TYPE, 'key');
            $nodes['key'] = $this->parser->getExpressionParser()->parseExpression();
        }

        if ($stream->test(\Twig_Token::NAME_TYPE, 'with'))
        {
            $stream->next();
            $stream->expect(\Twig_Token::NAME_TYPE, 'tags');
            $nodes['tags'] = $this->parser->getExpressionParser()->parseExpression();
        }

        if ($stream->test(\Twig_Token::NAME_TYPE, 'for'))
        {
            $stream->next();
            $attributes['durationNum'] = $stream->expect(\Twig_Token::NUMBER_TYPE)->getValue();
            $attributes['durationUnit'] = $stream->expect(\Twig_Token::NAME_TYPE, array('sec','secs','second','seconds','min','mins','minute','minutes','hour','hours','day','days','fortnight','fortnights','forthnight','forthnights','month','months','year','years','week','weeks'))->getValue();
        }
        else if ($stream->test(\Twig_Token::NAME_TYPE, 'until'))
        {
            $stream->next();
            $nodes['expiration'] = $this->parser->getExpressionParser()->parseExpression();
        }

        if ($stream->test(\Twig_Token::NAME_TYPE, 'if'))
        {
            $stream->next();
            $nodes['conditions'] = $this->parser->getExpressionParser()->parseExpression();
        }
        else if ($stream->test(\Twig_Token::NAME_TYPE, 'unless'))
        {
            $stream->next();
            $nodes['ignoreConditions'] = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        $nodes['body'] = $this->parser->subparse(array($this, 'decideTagEnd'), true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new HtmlcacheTwigNode($nodes, $attributes, $lineno, $this->getTag());
    }  

    /**
     * @return boolean
     */
    public function decideTagEnd(\Twig_Token $token)
    {
        return $token->test('endhtmlcache');
    } 
}
