<?php

namespace js4php5\compiler\parser;

use js4php5\compiler\lexer\Lexer;
use js4php5\compiler\lexer\Token;

class DefaultParserStrategy extends ParserStrategy
{

    /**
     * @param Token $token
     * @param Lexer $lex
     *
     * @throws ParseException
     */
    public function assertDone(Token $token, Lexer $lex)
    {
        if ($token->getType()) {
            $this->stuck($token, $lex, array());
        }
    }

    /**
     * @param Token $token
     * @param Lexer $lex
     * @param       $stack
     *
     * @throws ParseException
     */
    public function stuck(Token $token, Lexer $lex, $stack)
    {
        throw new ParseException(
            "Parser stuck; source and grammar do not agree. Can't tell what to do with token.",
            $token,
            $token->getStart());
    }
}
