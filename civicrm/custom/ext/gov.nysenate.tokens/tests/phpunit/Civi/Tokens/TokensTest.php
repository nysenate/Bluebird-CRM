<?php
declare(strict_types = 1);
namespace Civi\Tokens;

use Civi\Token\TokenProcessor;

/**
 * Tests for gov.nysenate.tokens.
 */
class TokensTest extends \PHPUnit\Framework\TestCase {

  public function setUp(): void {
    parent::setUp();
    require_once dirname(__DIR__, 4) . '/tokens.civix.php';
  }

  /**
   * Test that TokensSubscriber declares subscriptions to the required token events.
   */
  public function testSubscribedEvents(): void {
    $events = TokensSubscriber::getSubscribedEvents();
    $this->assertArrayHasKey('civi.token.list', $events);
    $this->assertArrayHasKey('civi.token.eval', $events);
    $this->assertSame('registerTokens', $events['civi.token.list']);
    $this->assertSame('evaluateTokens', $events['civi.token.eval']);
  }

  /**
   * Test that the 3 custom tokens are registered on the nyss entity:
   * - {nyss.base_url}
   * - {nyss.senator_formal}
   * - {nyss.senator_email}
   */
  public function testTokensRegistration(): void {
    $subscriber = new TokensSubscriber();
    $dispatcher = new \Civi\Core\CiviEventDispatcher(new \Symfony\Component\EventDispatcher\EventDispatcher());
    $dispatcher->addListener('civi.token.list', [$subscriber, 'registerTokens']);

    $tp = new TokenProcessor($dispatcher, ['controller' => __CLASS__]);
    $tokens = $tp->listTokens();

    $this->assertArrayHasKey('{nyss.base_url}', $tokens, 'Expected token "{nyss.base_url}" to be registered');
    $this->assertArrayHasKey('{nyss.senator_formal}', $tokens, 'Expected token "{nyss.senator_formal}" to be registered');
    $this->assertArrayHasKey('{nyss.senator_email}', $tokens, 'Expected token "{nyss.senator_email}" to be registered');

    $this->assertSame('Base URL', $tokens['{nyss.base_url}']);
    $this->assertSame('Senator Formal Name', $tokens['{nyss.senator_formal}']);
    $this->assertSame('Senator Email', $tokens['{nyss.senator_email}']);
  }

  /**
   * Test that tokens are evaluated and rendered correctly by TokensSubscriber.
   */
  public function testTokensEvaluation(): void {
    $subscriber = new TokensSubscriber();
    $dispatcher = new \Civi\Core\CiviEventDispatcher(new \Symfony\Component\EventDispatcher\EventDispatcher());
    $dispatcher->addListener('civi.token.eval', [$subscriber, 'evaluateTokens']);

    $tp = new TokenProcessor($dispatcher, ['controller' => __CLASS__]);
    $tp->addMessage('test_msg', 'URL: {nyss.base_url}, Name: {nyss.senator_formal}, Email: {nyss.senator_email}', 'text/plain');
    $tp->addRow(['contactId' => 1]);
    $tp->evaluate();

    foreach ($tp->getRows() as $row) {
      $rendered = $row->render('test_msg');
      $this->assertStringContainsString('URL:', $rendered);
      $this->assertStringContainsString('Name:', $rendered);
      $this->assertStringContainsString('Email:', $rendered);
    }
  }

}
