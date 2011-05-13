<?php
	require_once(dirname(__FILE__) . './../../civicrm/scripts/bluebird_config.php');
	
	$GLOBALS['nyss_backup_file_ext'] = '.zip';
	$GLOBALS['nyss_backup_data_dir'] = get_backup_dir().'/';
	$GLOBALS['nyss_backup_backup_script'] = '../scripts/dumpInstance.sh';
	$GLOBALS['nyss_backup_restore_script'] = '../scripts/restoreInstance.sh';
	
	function get_backup_dir() {
		$bbconfig = get_bluebird_config();
		return $bbconfig['globals']['backup.ui.rootdir'];
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
		if($instance_name && $file_name)
		{
			global $nyss_backup_restore_script, $nyss_backup_data_dir;
			passthru($nyss_backup_restore_script.' '.$instance_name
				.' --archive-file '.$nyss_backup_data_dir.$file_name.' --ok > /dev/null', $err);
			return $err == 0 ? true : false;
		}
		return false;
	}
	
	function do_backup($instance_name)
	{
		if($instance_name)
		{
			global $nyss_backup_file_ext, $nyss_backup_backup_script, $nyss_backup_data_dir;
			$file_name = $nyss_backup_data_dir.$instance_name.'-'.time().$nyss_backup_file_ext;	
			shell_exec($nyss_backup_backup_script.' '.$instance_name.' --zip --archive-file '.$file_name);
			return file_exists($file_name);
		}
		return false;
	}
	
	function get_files($dir, $filter = "/.*/")
	{
		$ret = array();
		
		if($handle = opendir($dir))
		{
			while(false !== ($file = readdir($handle)))
			{
				if($file != '.' && $file != '..' && preg_match($filter, $file))
				{
					$ret[] = $file;
				}
			}
		}
		closedir($handle);
		return $ret;
	}
	
	function get_instance_files($filter) {
		global $nyss_backup_data_dir;
		$data_dir = $nyss_backup_data_dir;
		
		$instance_files = array();
		
		$files = $filter ? get_files($data_dir, $filter) : get_files($data_dir);
		rsort($files);
		
		foreach($files as $i => $file)
		{
			$pieces = explode('-',$file);
			$name = $pieces[0];
			$time_stamp = explode('.',$pieces[1]);
			$time_stamp = $time_stamp[0];
			
			$instance_files[] = array('file' => $file, 'time' => $time_stamp);
		}
		
		return $instance_files; 
	}
	
	function json_encode_boolean($bool)
	{
		$json = array('success' => $bool);
		return json_encode($json);
	}
?>
