<?php

use InvalidArgumentException;
use PhpParser\Node\Expr\Cast\Object_;

class CRM_NYSS_BAO_Integration_WebsiteEventData
{
    protected CRM_NYSS_BAO_Integration_SenateDistrict $user_district;
    protected CRM_NYSS_BAO_Integration_SenateDistrict $target_district;

    protected string $event_type;
    protected string $event_action;
    protected object $user_info;
    protected object $raw_user_info;

    protected object $event_info;

    protected DateTime $created_at;

    public function __construct(CRM_Core_DAO $data) {

        // event_type
        if (! empty($data->event_type)) {
            $this->event_type = $data->event_type;
        } else {
            throw new InvalidArgumentException("Event type cannot be empty.");
        }

        // event_action
        if (! empty($data->event_action)) {
            $this->event_action = $data->event_action;
        } else {
            throw new InvalidArgumentException("Event action cannot be empty.");
        }

        // event_data
        if (! empty($data->event_data)) {
            $event_data = json_decode($data->event_data);

            if (empty($event_data->user_info)) {
                throw new InvalidArgumentException("user_info parameter cannot be empty.");
            }

            $this->user_info = $event_data->user_info;
            // user_info could be altered. Store a copy that won't change
            $this->raw_user_info = $event_data->user_info;

            if (!empty($event_data->event_info)) {
                $this->event_info = $event_data->event_info;
            } else {
                throw new InvalidArgumentException("event_info cannot be empty.");
            }

        } else {
            throw new InvalidArgumentException("Event data cannot be empty.");
        }

        // user_district and user_shortname
        if (!empty($data->user_shortname) && $data->user_district > 0) {
            $this->user_district = new CRM_NYSS_BAO_Integration_SenateDistrict($data->user_district, $data->user_shortname);
        }

        // target_district and target_shortname
        if (!empty($data->target_shortname) && $data->target_district > 0) {
            $this->user_district = new CRM_NYSS_BAO_Integration_SenateDistrict($data->target_district, $data->target_shortname);
        }

        // created_at
        $this->created_at = new DateTime($data->created_at);

        // gender (possibly deprecated, but keeping code)
        if ($data->gender) {
            switch ($data->gender) {
                case 'male':
                    $this->setGender(CRM_NYSS_BAO_Integration_WebsiteEventData_Gender::MALE);
                    break;
                case 'female':
                    $this->setGender(CRM_NYSS_BAO_Integration_WebsiteEventData_Gender::FEMALE);
                    break;
                case 'other':
                    $this->setGender(CRM_NYSS_BAO_Integration_WebsiteEventData_Gender::OTHER);
                    break;
                default:
            }
        }

        // date of birth (possibly deprecated as well)
        if (!empty($data->dob)) {
            $dob = new DateTime($data->dob);
            $this->setDob($dob->format('Y-m-d'));
        }

        return $this;
    }

    /**
     * For backwards compatibility with CRM_NYSS_BAO_Integration_Website::matchContact()
     * returns an array with contact information as expected and used by other classes.
     * @return array
     */
    public function getContactParams(): array
    {
        if (!empty($this->getFirstName()) || !empty($this->getLastName())) {
            return [
                'web_user_id' => $this->getWebUserId(),
                'first_name' => $this->getFirstName(),
                'last_name' => $this->getLastName(),
                'email' => $this->getEmail(),
                'street_address' => $this->getStreetAddress(),
                'city' => $this->getCity(),
                'state' => $this->getState(),
                'state_province' => $this->getState(),
                'postal_code' => $this->getZipCode(),
                'gender' => $this->getGender(),
                'gender_id' => $this->getGenderId(),
                'birthdate' => $this->getDob(),
            ];
        }
        return [];
    }

    /**
     * Checks the stored address via SAGE.
     * @param bool $store when true stores SAGE address corrections in the object -- changing $this->user_info
     * @return bool true means that SAGE found the address. false means SAGE did not find the address.
     */
    public function checkAddress(bool $store = false) : bool {
        $address = [
            'web_user_id' => $this->getWebUserId(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'email' => $this->getEmail(),
            'street_address' => $this->getStreetAddress(),
            'city' => $this->getCity(),
            'state' => $this->getState(),
            'postal_code' => $this->getZipCode()
        ];

        if (!empty($address['state'])) {
            //match params format required by SAGE checkAddress
            $address['state_province'] = $this->getState();
        }

        $result = CRM_Utils_SAGE::checkAddress($address);

        if ($store) {
            $this->setCity($address['city']);
            $this->setState($address['state']);
            $this->setStateProvince($address['state_province']);
            $this->setZipCode($address['postal_code']);
            $this->setZipCodeSuffix($address['postal_code_suffix']);
            $this->setStreetAddress($address['street_address']);
        }

        return $result;
    }


    public function getUserInfo(): object
    {
        return $this->user_info;
    }

    public function getRawUserInfo(): object
    {
        return $this->raw_user_info;
    }

    public function getEventInfo(): object
    {
        return $this->event_info;
    }

    protected function setEventInfoAttribute($name, $value): static {
        $this->event_info[$name] = $value;
        return $this;
    }

    public function getWebUserId() : ?int {
        return $this->user_info->id;
    }

    public function getFirstName() : ?string {
        return $this->user_info->first_name;
    }

    public function getLastName() : ?string {
        return $this->user_info->last_name;
    }

    public function getEmail() : ?string {
        return $this->user_info->email;
    }

    public function getStreetAddress() : ?string {
        return $this->user_info->address;
    }

    protected function setStreetAddress(string $street_address) : void
    {
        $this->user_info->address = $street_address;
    }


    public function getCity() : ?string {
        return $this->user_info->city;
    }

    protected function setCity(string $city): void
    {
        $this->user_info->city = $city;
    }

    public function getState() : ?string {
        return $this->user_info->state;
    }

    protected function setState(string $state): void
    {
        $this->user_info->state = $state;
    }

    public function getZipCode() : ?string {
        return $this->user_info->zipcode;
    }

    protected function setZipCode(string $postal_code) : void
    {
        $this->user_info->zipcode = $postal_code;
    }

    protected function setStateProvince(string $state_province) : void
    {
        $this->user_info->state_province = $state_province;
    }

    public function getZipCodeSuffix() : ?string {
        return $this->user_info->zipcode_suffix;
    }
    protected function setZipCodeSuffix(string $postal_code_suffix): void
    {
        $this->user_info->zipcode_suffix = $postal_code_suffix;
    }

    public function setGender(CRM_NYSS_BAO_Integration_WebsiteEventData_Gender $gender) : void {
        $this->user_info->gender = $gender;
        switch($gender) {
            case CRM_NYSS_BAO_Integration_WebsiteEventData_Gender::FEMALE :
                $this->user_info->gender_id = 1;
                break;
            case CRM_NYSS_BAO_Integration_WebsiteEventData_Gender::MALE :
                $this->user_info->gender_id = 2;
                break;
            case CRM_NYSS_BAO_Integration_WebsiteEventData_Gender::OTHER :
                $this->user_info->gender_id = 4;
                break;
        }
    }

    public function getGender() : ?string {
        return $this->user_info->gender ?? null;
    }
    public function getGenderId() : ?int {
        return $this->user_info->gender_id ?? null;
    }

    public function setDob(string $dob) : void {
        $this->user_info->dob = $dob;
    }
    public function getDob() : ?string
    {
        return $this->user_info->dob ?? null;
    }

    public function getEventType(): string
    {
        return $this->event_type;
    }

    public function getEventAction(): string
    {
        return $this->event_action;
    }

    public function getUserDistrict(): CRM_NYSS_BAO_Integration_SenateDistrict
    {
        return $this->user_district;
    }

    public function getTargetDistrict(): CRM_NYSS_BAO_Integration_SenateDistrict
    {
        return $this->target_district;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }


}

Enum CRM_NYSS_BAO_Integration_WebsiteEventData_Gender {
    case MALE;
    case FEMALE;
    case OTHER;
}