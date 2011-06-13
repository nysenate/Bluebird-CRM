<?php
	require('vars.php');
	$hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert/norsh}Inbox';
	//ex. term: TO "xx@example.com"
	$term = '';
	
	$api_functions = array(
		'get_emails' => 'get_message_json',
		'get_body' => 'get_message_body'
	);
	
	$app_params = array(
		'server' => $hostname, 
		'user' => $username, 
		'pass' => $password,
		'term' => $term,
		'rsort' => true,
		'page_size' => 5,
		'page_idx' => 0
	);
	
	header('Content-type: application/json');
	
	foreach($_GET as $key => $value) {
		$app_params[$key] = $value;
	}
	
	if($app_params['function'] && $api_functions[$app_params['function']]) {
		echo call_user_func($api_functions[$app_params['function']], $app_params);
	}
	else {
		echo "bad function";
	}
		
	function get_message_json($params) {
		$imap_conn = get_imap_conn($params);
		$emails = get_imap_paging_search($imap_conn, $params);
	
		$response = array();
	
		foreach($emails as $i => $uid) {
			$header = imap_headerinfo($imap_conn, $uid);
			
			$response[] = array(
				'to' => $header->to, 
				'from' => $header->from,
				'cc' => ($header->cc == null ? array() : $header->cc),
				'subject' => $header->subject,
				'time_stamp' => $header->udate,
				'uid' => $uid
			);
		}
		imap_close($imap_conn);
		
		return json_encode($response);
	}
	
	function get_message_body($params)
	{
		$imap_conn = get_imap_conn($params);
		$body = imap_fetchbody($imap_conn, $params['uid'], 1);
		imap_close($imap_conn);
		return json_encode($body);
	}
	
	function get_imap_paging_search($imap_conn, $params)
	{
		$results = get_imap_search($imap_conn, $params);
		
		$chunks = array_chunk($results, $params['page_size']);
		
		if(count($chunks) >= $params['page_idx'])
		{
			return $chunks[$params['page_idx']];
		}
	}
	
	function get_imap_search($imap_conn, $params)
	{
		$results = imap_search($imap_conn, $params['term']);
		if($params['rsort'] === true) {
			rsort($results);
		}
		return $results;
	}
	
	function get_imap_conn($params)
	{
		$imap_conn = imap_open($params['server'], $params['user'], $params['pass']);
		
		if ($imap_conn === false)
		{
			echo "Error: Unable to open IMAP connection\n";
			return false;
		}
		
		return $imap_conn;
	}
?>
