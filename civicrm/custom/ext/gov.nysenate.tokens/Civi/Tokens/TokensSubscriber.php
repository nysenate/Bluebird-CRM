<?php
declare(strict_types = 1);

namespace Civi\Tokens;

use Civi\Core\Service\AutoSubscriber;
use Civi\Token\Event\TokenRegisterEvent;
use Civi\Token\Event\TokenValueEvent;
use CRM_Tokens_ExtensionUtil as E;

/**
 * Token subscriber for NYSS custom tokens.
 *
 * @service tokens.subscriber
 */
class TokensSubscriber extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return [
      'civi.token.list' => 'registerTokens',
      'civi.token.eval' => 'evaluateTokens',
    ];
  }

  /**
   * Register custom NYSS tokens.
   *
   * @param \Civi\Token\Event\TokenRegisterEvent $e
   */
  public function registerTokens(TokenRegisterEvent $e): void {
    $e->entity('nyss')
      ->register('base_url', E::ts('Base URL'))
      ->register('senator_formal', E::ts('Senator Formal Name'))
      ->register('senator_email', E::ts('Senator Email'));
  }

  /**
   * Evaluate custom NYSS tokens.
   *
   * @param \Civi\Token\Event\TokenValueEvent $e
   */
  public function evaluateTokens(TokenValueEvent $e): void {
    $activeTokens = $e->getTokenProcessor()->getMessageTokens();
    if (empty($activeTokens['nyss'])) {
      return;
    }

    $bbconfig = function_exists('get_bluebird_instance_config') ? get_bluebird_instance_config() : [];
    $basename = $bbconfig['db.basename'] ?? '';
    $domain = $bbconfig['base.domain'] ?? '';
    $bb = [
      'base_url' => ($basename && $domain) ? "{$basename}.{$domain}" : '',
      'senator_formal' => $bbconfig['senator.name.formal'] ?? '',
      'senator_email' => $bbconfig['senator.email'] ?? '',
    ];

    foreach ($e->getRows() as $row) {
      $row->format('text/html');
      foreach ($activeTokens['nyss'] as $token) {
        if ($token === 'base_url') {
          $row->tokens('nyss', 'base_url', 'http://' . $bb['base_url']);
        }
        elseif ($token === 'senator_formal') {
          $row->tokens('nyss', 'senator_formal', $bb['senator_formal']);
        }
        elseif ($token === 'senator_email') {
          $row->tokens('nyss', 'senator_email', $bb['senator_email']);
        }
      }
    }
  }

}
