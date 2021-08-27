<?php

class CRM_NYSS_Slack_BAO {
  /**
   * @param $message
   * @param null $title
   * @param array $fields
   * @param null $channel
   * @param string $attachment_color
   *
   * Adapted from:
   * https://github.com/nysenate/NYSenate.gov-Website-2015/blob/2.x/sites/all/modules/custom/nys_utils/nys_utils.module#L1973
   */
  static function notifySlack($message, $title = NULL, $fields = [], $channel = NULL, $attachment_color = 'danger') {
    $slack_url = Civi::settings()->get('resources_slack_url');

    // Proceed only if the slack URL is set.
    if (!empty($slack_url)) {
      // Get default channel if none is passed in.
      if ($channel == NULL) {
        $channel = Civi::settings()->get('resources_slack_channel');
      }

      // Get default title if none is passed in.
      if (empty($title)) {
        $title = Civi::settings()->get('resources_slack_title');
      }

      //append site url
      $bbcfg = get_bluebird_instance_config();
      $title .= ' (' . $bbcfg['servername'] . ')';

      // Set a default for attachment color, which can be any of the following:
      // - good (green)
      // - warning (yellow)
      // - danger (red)
      $slack_attachment_colors = ['good', 'warning', 'danger'];
      if (!in_array($attachment_color, $slack_attachment_colors)) {
        $attachment_color = 'danger';
      }

      $payload = [
        'text' => $title,
        'channel' => $channel,
        'attachments' => [[
          'color' => $attachment_color,
          'text' => $message,
          'fallback' => $message,
          'pretext' => '',
        ],
        ],
      ];

      // Merge in fields if the $fields array is not empty.
      if (is_array($fields) && count($fields)) {
        $payload['attachments'][0]['fields'] = $fields;
      }
      //Civi::log()->debug(__FUNCTION__, ['payload' => $payload]);

      // Convert $payload array to recommended json string.
      $data_string = ['payload' => json_encode($payload)];

      $ch = curl_init($slack_url);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      $response = curl_exec($ch);
      //Civi::log()->debug(__FUNCTION__, ['$response' => $response]);

      curl_close($ch);

      if ($response != 'ok') {
        watchdog('bluebird', 'Slack call failed with response: %response', ['%response'=>$response], WATCHDOG_ERROR);
      }
    }
    else {
      watchdog('bluebird', 'Slack URL is not set!', [], WATCHDOG_WARNING);
    }
  }
}
