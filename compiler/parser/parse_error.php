<?php

namespace js4php5\compiler\parser;

use Exception;

/**
 * Parser-specific generic error (distinct from PHP's built-in \ParseError).
 */
class parse_error extends Exception
{
}
