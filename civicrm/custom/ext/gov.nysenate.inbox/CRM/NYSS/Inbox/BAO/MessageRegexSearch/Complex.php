<?php

class CRM_NYSS_Inbox_BAO_MessageRegexSearch_Complex extends CRM_NYSS_Inbox_BAO_MessageRegexSearch {

  public array $named_subpatterns = [];

  public function addNamedSubpattern(String $name) : static {
    $this->named_subpatterns[] = $name;
    return $this;
  }

  public function setNamedSubppaterns(array $names) : static {
    $this->named_subpatterns = $names;
    return $this;
  }

  public function listNamedSubpatterns(): array {
    return $this->named_subpatterns;
  }

  protected function createToken(): CRM_NYSS_Inbox_BAO_MessageToken_Interface {
    //$match = $this->pluckMatch();
    //$offset = $this->pluckOffset() || strpos($this->subject,$match);
    $token = parent::createToken();
    foreach($this->listNamedSubpatterns() as $type) {
      $part = CRM_NYSS_Inbox_BAO_MessageToken_Factory::create($type,
          $this->pluckMatch($type),
          $this->pluckOffset($type));
      $token->addPart($part);
    }
    return $token;
  }

}