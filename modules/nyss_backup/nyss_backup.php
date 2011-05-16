<?php
	require_once(dirname(__FILE__) . './../../civicrm/scripts/bluebird_config.php');
	
	$GLOBALS['nyss_backup_file_ext'] = '.zip';
	$GLOBALS['nyss_backup_data_dir'] = get_data_dir();
	$GLOBALS['nyss_backup_backup_script'] = '../scripts/dumpInstance.sh';
	$GLOBALS['nyss_backup_restore_script'] = '../scripts/restoreInstance.sh';
	$GLOBALS['nyss_backup_name_size'] = 50;
	
	/* API functions */
	
	function do_backup($file_name, $file_time, $instance_name)
	{
		global $nyss_backup_file_ext, 
				$nyss_backup_backup_script, 
				$nyss_backup_data_dir, 
				$nyss_backup_name_size;
				
		$file_date = get_file_date($file_time);
		
		$instance_name = get_instance_name($instance_name);
		
		//if provided file name only consists of white spaces
		//and non word chracters set to default YYYYMMDD-HHMMSS
		if(!$file_name || preg_match('/^[\s\W]*$/', $file_name)) {
			$file_name = $file_date['string_date'];
		}
		else {
			//remove special characters and replace whitespace with _
			$pat = array('/(?![ \-])\W/','/ /');
			$rep = array('','_');
			$file_name =  preg_replace($pat, $rep, $file_name);
		}
		
		//get substring if file name too long
		if(strlen($file_name) > $nyss_backup_name_size) {
			$file_name = substr($file_name, 0, $nyss_backup_name_size);
		}
		
		$file_name_with_path = $nyss_backup_data_dir
							.$file_name
							.$nyss_backup_file_ext;
		
		//if the file already exists tack on date string
		if(file_exists($file_name_with_path)) {
			$file_name_with_path = $nyss_backup_data_dir
							.$file_name.'-'
							.$file_date['string_date']
							.$nyss_backup_file_ext;
		}
		
		shell_exec($nyss_backup_backup_script.' '
						.$instance_name.' --zip --archive-file '.$file_name_with_path);
						
		//touch file to make sure date in file name matches modified date
		return file_exists($file_name_with_path);
	}
	
	/*
	 * convert file list to associative array with format [{file:<filename>, time:<timestamp>}]
	 */
	function get_instance_files() {
		global $nyss_backup_data_dir;
		$data_dir = $nyss_backup_data_dir;
		
		$instance_files = array();
		
		$files = get_files($data_dir);
		usort($files, 'sort_by_ctime');
		
		foreach($files as $i => $file)
		{
			$time_stamp = filemtime($data_dir.$file);			
			$instance_files[] = array('file' => $file, 'time' => $time_stamp);
		}
		
		return $instance_files; 
	}
		
	function do_delete($file_name)
	{
		if($file_name)
		{
			global $nyss_backup_data_dir;
			return unlink($nyss_backup_data_dir.$file_name);
		}
		return false;
	}
	
	function do_restore($instance_name, $file_name)
	{
		$instance_name = get_instance_name($instance_name);
		
		if($file_name)
		{
			global $nyss_backup_restore_script, $nyss_backup_data_dir;
			passthru($nyss_backup_restore_script.' '.$instance_name
				.' --archive-file '.$nyss_backup_data_dir.$file_name.' --ok > /dev/null', $err);
			return $err == 0 ? true : false;
		}
		return false;
	}
	
	/* utility functions */
	
	function get_data_dir($instance_name) {
		$instance_name = get_instance_name($instance_name);
		
		$instance_config = get_bluebird_instance_config('../bluebird.cfg', $instance_name);
		$bb_config = get_bluebird_config();
		
		$data_dir = $bb_config['globals']['data.rootdir']
					.'/'.$instance_config['data_dirname']
					.'/'.$bb_config['globals']['backup.ui.dirname']
					.'/';
					
		if(!is_dir($data_dir)) {
			mkdir($data_dir);
		}
		
		return $data_dir;
	}
	
	//return value if $instance_name provided, otherwise
	//look at $_SERVER object
	function get_instance_name($instance_name) {
		if($instance_name) return $instance_name;
		
		if($_SERVER && $_SERVER['SERVER_NAME']) {
			$server_name = explode('.',$_SERVER['SERVER_NAME']);
			return $server_name[0];
		}
		return "";
	}
	
	function json_encode_boolean($bool)
	{
		$json = array('success' => $bool);
		return json_encode($json);
	}
	
	// returns date in format YYYYMMDD-HHMMSS
	function get_file_date($file_time) {
		$file_time = $file_time ? $file_time : time();
		return array('string_date' => date("Ymd-His", $file_time), 'int_date' => $file_time);
	}
	
	function sort_by_ctime($f1, $f2) {
		global $nyss_backup_data_dir;
		return (filemtime($nyss_backup_data_dir.$f1) < filemtime($nyss_backup_data_dir.$f2)); 
	}
	
	function get_files($dir)
	{
		$ret = array();
		
		if($handle = opendir($dir))
		{
			while(false !== ($file = readdir($handle)))
			{
				if($file != '.' && $file != '..')
				{
					$ret[] = $file;
				}
			}
		}
		closedir($handle);
		return $ret;
	}
?>
