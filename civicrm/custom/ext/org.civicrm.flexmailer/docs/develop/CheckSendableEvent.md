The `CheckSendableEvent` (`EVENT_CHECK_SENDABLE`) determines whether a draft mailing is fully specified for delivery.

For example, some jurisdictions require that email blasts provide contact
information for the organization (eg street address) and an opt-out link.
By default, the check-sendable event will verify that this information is
provided through a mail-merge token (eg `{action.unsubscribeUrl}`).

The token validation logic depends on how the message has been encoded.  If
you provide a new template language, you can implement new enforcement logic,
e.g.


```php
<?php
function mustache_civicrm_container($container) {
  $container->addResource(new \Symfony\Component\Config\Resource\FileResource(__FILE__));
  $container->findDefinition('dispatcher')->addMethodCall('addListener',
    array(\Civi\FlexMailer\Validator::EVENT_CHECK_SENDABLE, '_mustache_check_sendable')
  );
}

function _mustache_check_sendable(\Civi\FlexMailer\Event\CheckSendableEvent $e) {
  if ($e->getMailing()->template_type !== 'mustache') return;

  if (strpos('{{unsubscribeUrl}}', $e->getMailing()->body_html) === FALSE) {
    $e->setError('body_html:unsubscribeUrl', E::ts('Please include the token {{unsubscribeUrl}}'));
  }
}
```
