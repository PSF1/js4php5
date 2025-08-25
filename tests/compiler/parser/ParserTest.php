<?php
declare(strict_types=1);

namespace js4php5\compiler\parser {
  // Minimal ParseStackFrame stub en el namespace del parser
  class ParseStackFrame {
    public $state;
    private array $stack = [];
    public function __construct($symbol, $state) { $this->state = $state; }
    public function shift($x) { $this->stack[] = $x; }
    public function semantic() { return $this->stack; }
    public function fold($x) { $this->stack[] = $x; }
  }
}

namespace js4php5\tests\compiler\parser {
  use PHPUnit\Framework\TestCase;
  use js4php5\compiler\parser\Parser;
  use js4php5\compiler\parser\DefaultParserStrategy;
  use js4php5\compiler\lexer\Token;
  use js4php5\compiler\lexer\Point;

  // Stub de Lexer que produce una secuencia fija de tokens
  class StubLexer extends \js4php5\compiler\lexer\Lexer
  {
    /** @var Token[] */
    private array $tokens;
    public function __construct(array $tokens) { $this->tokens = $tokens; }
    public function next()
    {
      return array_shift($this->tokens) ?? Token::getNullToken();
    }
    public function start($string) { /* no-op for stub */ }
  }

  // Implementación concreta mínima de Parser para poder probar getStep/parse
  class DummyParser extends Parser
  {
    public function reduce($action, $tokens)
    {
      // Devuelve un valor simbólico sin usar $tokens
      return 'REDUCED_' . $action;
    }
  }

  final class ParserTest extends TestCase
  {
    public function testGetStepReturnsTransitionForGlyphAndDefault(): void
    {
      $pda = [
        'action' => [],
        'start'  => ['S' => 'S0'],
        'delta'  => [
          'S0' => [
            'T' => ['go', 'S1'],
            '[default]' => ['error'],
          ],
          'S1' => [],
        ],
      ];
      $parser = new DummyParser($pda);

      $step = $parser->getStep('S0', 'T');
      $this->assertSame(['go', 'S1'], $step);

      $def = $parser->getStep('S0', 'X');
      $this->assertSame(['error'], $def);

      // Estado inexistente -> también 'error'
      $unk = $parser->getStep('NOPE', 'T');
      $this->assertSame(['error'], $unk);
    }

    public function testParseWithDefaultStrategyConsumesGoThenDoAndReturnsReduced(): void
    {
      // PDA: S0 --(go on 't')-> S1; S1 --(do on '')-> reduce(1)
      $pda = [
        'action' => [],
        'start'  => ['S' => 'S0'],
        'delta'  => [
          'S0' => [ 't' => ['go', 'S1'] ],
          'S1' => [ ''  => ['do', 1] ],
        ],
      ];
      $parser = new DummyParser($pda);

      // Tokens: primero 't', luego NullToken ('')
      $t1 = new Token('t', 't', new Point(1,0), new Point(1,1));
      $lex = new StubLexer([$t1, Token::getNullToken()]);

      $result = $parser->parse('S', $lex, null); // strategy null -> usa DefaultParserStrategy internamente
      $this->assertSame('REDUCED_1', $result);
    }

    public function testParseUsesProvidedStrategy(): void
    {
      $pda = [
        'action' => [],
        'start'  => ['S' => 'S0'],
        'delta'  => [
          'S0' => [ ''  => ['do', 99] ], // reduce inmediatamente con NullToken
        ],
      ];
      $parser = new DummyParser($pda);
      $lex = new StubLexer([Token::getNullToken()]);

      // Estrategia personalizada que no lanza en assertDone
      $strategy = new class extends DefaultParserStrategy {
        public function assertDone(\js4php5\compiler\lexer\Token $token, \js4php5\compiler\lexer\Lexer $lex) {
          // no-op
        }
      };

      $result = $parser->parse('S', $lex, $strategy);
      $this->assertSame('REDUCED_99', $result);
    }
  }
}
