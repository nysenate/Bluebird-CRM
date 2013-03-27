<?php
/**
 * redmine class.
 * 
 */
class redmine {
 
  /**
   * url
   * 
   * @var mixed
   * @access private
   */
  private $url;
 
  /**
   * apikey
   * 
   * @var mixed
   * @access private
   */
  private $apikey;
 
  /**
   * curl
   * 
   * @var mixed
   * @access private
   */
  private $curl;
 
  /**
   * config
   * 
   * @var mixed
   * @access private
   */
  private $config;
 
  /**
   * users
   * 
   * (default value: array())
   * 
   * @var array
   * @access private
   */
  private $users = array();
 
  /**
   * __construct function.
   * 
   * @access public
   * @param array $config. (default: array())
   * @return void
   */
  function __construct($config = array()) {
    $this->config = $config;
    $this->url = $config['url'];
    $this->apikey = $config['apikey'];
 
    $this->listUsers();
  }
 
  /**
   * listUsers function.
   * 
   * @access private
   * @return void
   */
  private function listUsers() {
    $users = $this->getUsers();
    foreach($users as $user) {
      $this->users[(string)$user->login] = (int)$user->id;
    }
  }
 
  /**
   * getUserId function.
   * 
   * @access public
   * @param mixed $username
   * @return void
   */
  public function getUserId($username) {
    if( !is_array($this->users) or count($this->users) == 0 ) $this->listUsers();
 
    if(array_key_exists($username, $this->users))
      return $this->users[(string)$username];
    return false;
  }
 
  /**
   * runRequest function.
   * 
   * @access private
   * @param mixed $restUrl
   * @param string $method. (default: 'GET')
   * @param string $data. (default: "")
   * @return void
   */
  private function runRequest($restUrl, $method = 'GET', $data = ""){
        $method = strtolower($method);
 
        $this->curl = curl_init();
 
        // Authentication
        if(isset($this->apikey)) {
      curl_setopt($this->curl, CURLOPT_USERPWD, $this->apikey.":".rand(100000, 199999) );
      curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }
 
        // Request
        switch ($method) {
      case "post":
        curl_setopt($this->curl, CURLOPT_POST, 1);
        if(isset($data)) curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        break;
      case "put":
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT'); 
        if(isset($data)) curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        break;
      case "delete":
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        break;
      default: // get
        break;
    }
 
        // Run the request
    try {
      curl_setopt($this->curl, CURLOPT_URL, $this->url.$restUrl); 
      curl_setopt($this->curl, CURLOPT_PORT , 80); 
      curl_setopt($this->curl, CURLOPT_VERBOSE, 0); 
      curl_setopt($this->curl, CURLOPT_HEADER, 0); 
      curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1); 
      curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "Content-length: ".strlen($data))); 
 
      $response = curl_exec($this->curl); 
      if(!curl_errno($this->curl)){ 
          $info = curl_getinfo($this->curl); 
      } else { 
        curl_close($this->curl); 
        return false;
      }
 
      curl_close($this->curl); 
    } catch (Exception $e) {
        //echo 'Exception: ',  $e->getMessage(), "\n";
        return false;
    }
 
    if($response) {
      if(substr($response, 0, 1) == '<') {
        return new SimpleXMLElement($response);
      } else {
        return false;
      }
    }
    return true;
    }
 
  /**
   * getUsers function.
   * 
   * @access public
   * @return void
   */
  public function getUsers() {
    return $this->runRequest('/users.xml', 'GET', '');
  }
 
  /**
   * getProjects function.
   * 
   * @access public
   * @return void
   */
  public function getProjects() {
    return $this->runRequest('/projects.xml', 'GET', '');
  }
 
  /**
   * getIssues function.
   * 
   * @access public
   * @param mixed $projectId
   * @return void
   */
  public function getIssues($projectId) {
    return $this->runRequest('/issues.xml'.$projectId, 'GET', '');
  }
 
  /**
   * addIssue function.
   * 
   * @access public
   * @param mixed $subject
   * @param mixed $description
   * @param mixed $project_id
   * @param int $category_id. (default: 1)
   * @param bool $assignmentUsernames. (default: false)
   * @param bool $due_date. (default: false)
   * @param int $priority_id. (default: 4)
   * @return void
   */
  public function addIssue($subject, $description, $project_id, $category_id = 1, $assignmentUsernames = false, $due_date = false, $priority_id = 4) {
    $xml = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
    $xml->addChild('subject', htmlentities($subject));
    $xml->addChild('project_id', $project_id);
    $xml->addChild('priority_id', $priority_id);
    $xml->addChild('description', htmlentities($description));
    $xml->addChild('category_id', $category_id);
    if($due_date) $xml->addChild('due_date', $due_date);
 
    if(is_array($assignmentUsernames) and count($assignmentUsernames) >= 1) {
      foreach($assignmentUsernames as $assignmentUsername) {
        if($assignmentUserId = $this->getUserId($assignmentUsername))
          $xml->addChild('assigned_to_id', $assignmentUserId);
      }
    }
 
    return $this->runRequest('/issues.xml', 'POST', $xml->asXML() );
  }
 
  /**
   * setIssueStatus function.
   * 
   * @access public
   * @param mixed $status
   * @param mixed $issueId
   * @return void
   */
  public function setIssueStatus($status, $issueId) {
    if($status) {
      $statusId = 5;  // closed
    } else {
      $statusId = 2;  // in Progress
    }
 
    $xml = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
    $xml->addChild('id', $issueId);
    $xml->addChild('status_id', $statusId);
    return $this->runRequest('/issues/'.$issueId.'.xml', 'PUT', $xml->asXML() );
  }
 
  /**
   * addNoteToIssue function.
   * 
   * @access public
   * @param mixed $issueId
   * @param mixed $note
   * @return void
   */
  public function addNoteToIssue($issueId, $note) {
    $xml = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
    $xml->addChild('id', $issueId);
    $xml->addChild('notes', htmlentities($note));
    return $this->runRequest('/issues/'.$issueId.'.xml', 'PUT', $xml->asXML() );
  }
 
  /**
   * getTrackerItemLink function.
   * 
   * @access public
   * @param mixed $trackerItemID
   * @return void
   */
  public function getTrackerItemLink($trackerItemID) {
    return $this->url.'/issues/'.$trackerItemID;
  }
}