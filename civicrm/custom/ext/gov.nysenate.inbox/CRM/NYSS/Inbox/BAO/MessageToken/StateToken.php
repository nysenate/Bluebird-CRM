<?php

class CRM_NYSS_Inbox_BAO_MessageToken_StateToken
  extends \CRM_NYSS_Inbox_BAO_MessageToken_GenericToken
  implements CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  protected array $states = [
    'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
    'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
    'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
    'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
    'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
    'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
    'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
    'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
    'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
    'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
    'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
    'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
    'WI' => 'Wisconsin', 'WY' => 'Wyoming'
  ];

  public function __construct(string $token, ?int $offset = null) {
    parent::__construct(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STATE,
                        $token, $offset);
    return $this;
  }

  function getStateName($code) {
    global $states;
    return $states[$code] ?? 'Unknown';
  }

  /**
   * Overrides Generic getData() to expand 2 letter state codes to full names.
   * @return mixed|string
   */
  public function getData() {
    return (array_key_exists($this->getToken(),$this->states)) ?
      $this->states[$this->getToken()] : $this->getToken();
  }

}