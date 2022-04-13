<?php

/**
 * Class CRM_Mosaico_AbDemux
 *
 * Mosaico mailings may use an optional field, `$mailing['template_options']['variants']`
 * to specify that multiple variants of the message will be sent as A/B tests.
 *
 * $mailing = [
 *   'subject' => '',
 *   'body_html' => '<p>Hello world</p>',
 *   'template_options' => [
 *     'variants' => [
 *       0 => ['subject' => 'Greetings!']
 *       1 => ['subject' => 'Greetings from the other side!']
 *     ]
 *   ]
 * ]
 *
 * This listener modifies the behavior of `Mailing.submit` - if the `variants`
 * are specified, then:
 *   - Split the mailing into three mailings (experiments A+B plus anticipated winner C).
 *   - The A+B mailings are identical, except for the variant fields.
 *   - Put the mailings in an A/B test
 *   - Submit the A/B experiment for testing.
 *
 * Otherwise, defer to the normal Mailing.submit API.
 */
class CRM_Mosaico_AbDemux {

  const DEFAULT_AB_PERCENTAGE = 10;

  protected $mandatory = [
    'open_tracking' => 1,
    'url_tracking' => 1,
  ];
  const DEFAULT_WINNING_TIME = '+2 days';

  /**
   * List of properties that may be overriden in variants.
   * To apply consistently, properties should be equally valid for Mailing DAO and Mailing API.
   *
   * @var array
   */
  protected $variantFields = [
    'subject',
    'body_html',
    'body_text',
    'from_name',
    'from_email',
    'replyto_email',
    'header_id',
    'footer_id',
    'reply_id',
    'unsubscribe_id',
    'resubscribe_id',
    'optout_id',
  ];

  /**
   * Handle the Mailing.send_test API.
   *
   * If there are `template_options.variants`, then we send all the variants
   * to the intended recipient.
   *
   * @param array $apiRequest
   *   The submitted API request.
   * @param callable $continue
   *   The original/upstream implementation of Mailing.send_test API.
   * @return array
   * @throws \API_Exception
   */
  public function onSendTestMailing($apiRequest, $continue) {
    if (empty($apiRequest['params']['mailing_id'])) {
      // Let upstream handle the error.
      return $continue($apiRequest);
    }
    $id = $apiRequest['params']['mailing_id'];

    $api3 = $this->makeApi3($apiRequest);
    $mailing = $api3('Mailing', 'getsingle', ['id' => $id]);
    if (empty($mailing['template_options']['variants'])) {
      // Not one of ours!
      return $continue($apiRequest);
    }

    $allResults = [];
    foreach ($mailing['template_options']['variants'] as $variant) {
      $applyVariant = function(\Civi\FlexMailer\Event\ComposeBatchEvent $e) use ($id, $variant) {
        if ($e->getMailing()->id == $id) {
          $this->applyVariant($e->getMailing(), $variant);
        }
      };
      Civi::dispatcher()->addListener('civi.flexmailer.compose', $applyVariant, \Civi\FlexMailer\FlexMailer::WEIGHT_START);
      try {
        $result = $continue($apiRequest);
        $allResults = $allResults + $result['values'];
      }
      finally {
        Civi::dispatcher()->removeListener('civi.flexmailer.compose', $applyVariant);
      }
    }
    return civicrm_api3_create_success($allResults);
  }

  /**
   * Handle the Mailing.submit API.
   *
   * @param array $apiRequest
   *   The submitted API request.
   * @param callable $continue
   *   The original/upstream implementation of Mailing.submit API.
   * @return array
   * @throws \API_Exception
   */
  public function onSubmitMailing($apiRequest, $continue) {
    civicrm_api3_verify_mandatory($apiRequest['params'], 'CRM_Mailing_DAO_Mailing', array('id'));
    if (!isset($apiRequest['params']['scheduled_date']) && !isset($apiRequest['params']['approval_date'])) {
      throw new API_Exception("Missing parameter scheduled_date and/or approval_date");
    }

    $api3 = $this->makeApi3($apiRequest);

    // Ensure that mailings A, B, C exist. Lookup $variants spec.
    $a = $api3('Mailing', 'getsingle', ['id' => $apiRequest['params']['id']]);
    if (empty($a['template_options']['variants'])) {
      // Not one of ours!
      return $continue($apiRequest);
    }
    $b = $api3('Mailing', 'clone', [
      'id' => $a['id'],
      'api.Mailing.create' => [
        'groups' => ['include' => [], 'exclude' => []],
        'mailings' => ['include' => [], 'exclude' => []],
      ],
    ]);
    $c = $api3('Mailing', 'create', ['name' => $a['name'] . ' (C)']);
    $variants = $a['template_options']['variants'];

    // Specify the key content of mailings A, B, C.
    self::update($api3, 'Mailing', $a['id'], function ($mailing) use ($variants) {
      unset($mailing['template_options']['variants']);
      $mailing['name'] .= ' (A)';
      $mailing['mailing_type'] = 'experiment';
      return $this->applyVariant($mailing, $this->mandatory + $variants[0]);
    });
    self::update($api3, 'Mailing', $b['id'], function ($mailing) use ($variants) {
      unset($mailing['template_options']['variants']);
      $mailing['name'] .= ' (B)';
      $mailing['mailing_type'] = 'experiment';
      return $this->applyVariant($mailing, $this->mandatory + $variants[1]);
    });
    self::update($api3, 'Mailing', $c['id'], function ($mailing) use ($variants) {
      $mailing['mailing_type'] = 'winner';
      return $mailing;
    });

    // Create and submit the full A/B test record.
    $ab = $api3('MailingAB', 'create', [
      'name' => $a['name'],
      'status' => 'Draft',
      'mailing_id_a' => $a['id'],
      'mailing_id_b' => $b['id'],
      'mailing_id_c' => $c['id'],
      'testing_criteria' => 'full_email',
      'group_percentage' => CRM_Utils_Array::value('variantsPct', $a['template_options'], self::DEFAULT_AB_PERCENTAGE),
      'winner_criteria' => 'open',
      'declare_winning_time' => self::DEFAULT_WINNING_TIME,
    ]);

    $submitParams = CRM_Utils_Array::subset($apiRequest['params'], array(
      'scheduled_date',
      'approval_date',
      'approval_note',
      'approval_status_id',
    ));

    $api3('MailingAB', 'submit', $submitParams + [
      'id' => $ab['id'],
      'status' => 'Testing',
    ]);

    // We should still provide a return signature for the original Mailing.submit call.
    $reloadA = $api3('Mailing', 'getsingle', [
      'id' => $a['id'],
      'return' => [
        'scheduled_id',
        'scheduled_date',
        'approval_date',
        'approval_note',
        'approval_status_id',
      ],
    ]);

    return civicrm_api3_create_success([
        $a['id'] => $reloadA + [
          '_mailing_ab' => $ab['values'][$ab['id']],
        ],
    ]);
  }

  public function update($api3, $entity, $id, $callback) {
    $base = ['id' => $id];
    $record = $api3($entity, 'getsingle', $base);
    $record = $callback($record);
    return $api3($entity, 'create', $base + $record);
  }

  /**
   * Generate an API facade which makes requests using consistent
   * security context.
   *
   * @param array $apiRequest
   *   The original API request
   * @return \Closure
   *   An API entry function which works like "civicrm_api3()`
   */
  protected function makeApi3($apiRequest) {
    $check = ['check_permissions' => CRM_Utils_Array::value('check_permissions', $apiRequest['params'], FALSE)];
    $api3 = function ($entity, $action, $params) use ($check) {
      return civicrm_api3($entity, $action, $params + $check);
    };
    return $api3;
  }

  /**
   * @param array|object $mailing
   * @param array $variant
   * @return array|object
   * @throws \API_Exception
   */
  protected function applyVariant(&$mailing, $variant) {
    $overrides = array_intersect(array_keys($variant), $this->variantFields);
    if (is_array($mailing)) {
      foreach ($overrides as $override) {
        $mailing[$override] = $variant[$override];
      }
    }
    elseif (is_object($mailing)) {
      foreach ($overrides as $override) {
        $mailing->{$override} = $variant[$override];
      }
    }
    else {
      throw new API_Exception("Cannot apply variant - unrecognized mailing object");
    }
    return $mailing;
  }

}
