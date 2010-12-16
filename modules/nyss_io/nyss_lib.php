<?php

function nyss_out($type,$v,$toscreen=false) {

	global $nyss_ioline,$nyss_iototallines;

	if(!empty($v) && (($type=='debug' && NYSSIODEBUG) || $type!='debug')) {

		$v = print_r($v,true);

		error_log($v. " (line: $nyss_ioline)");
		if ($toscreen) {

			echo "<pre>$v (line: $nyss_ioline of $nyss_iototallines)</pre>";
			flush();
			ob_flush();
		}
	}
}

class nyss_iofileobject {

 public $filepath;
 public $type = ",";
 public $hasHeader = 1;
 public $linecount = 0;
 public $header = array();

 private $file_handle;

 function nyss_iofileobject($filepath, $type=',') {

  $this->type=$type;
  $this->filepath = $filepath;

  ini_set('auto_detect_line_endings',TRUE);

  // open the file for reading
  $this->file_handle = fopen($filepath, 'r');

  $line = $this->getLineAsArray();

 if (!is_array($line)) {

         //set error message and exit
         drupal_set_message('bad file. Either the file is not a cvs or has the wrong number of headers.','error');
         return false;
  }

  //get the header row
  $this->header = array();
  foreach ($line as $key=>$val) $this->header[strtolower($val)]=$key;

 }

 public function countLines() {

	$ns=$rs=0;

	$fh = fopen($this->filepath, 'r');

	while ($chunk = fread($fh, 1024000)) {
		$ns += substr_count($chunk, "\n");
		$rs += substr_count($chunk, "\r");
	}

	if ($rs>$ns) $ns=$rs;
	return $rs;
  }

 public function getLine() {

	if ($line = $this->getLineAsArray()) {

		++$this->linecount;

	        foreach ($this->header as $colname=>$colnum) $aLine[$colname] = $line[$colnum];

	 	return $aLine;
 	}

	return false;
 }

 function getLineAsArray()
 {
   $line = fgets($this->file_handle);
   if ($line) {
     return explode($this->type, rtrim($line));
   }
   else {
     return false;
   }
 } // getLineAsArray()

}

