<?php

//replicated from CRM_Utils_String::stripSpaces
//in order to process off c3.2.x core base
function nyss_stripSpaces($s)
{
  if (empty($s)) {
    return $s;
  }

  $pat = array(
    0 => "/^\s+/",
    1 =>  "/\s{2,}/",
    2 => "/\s+\$/"
  );

  $rep = array(
    0 => "",
    1 => " ",
    2 => ""
  );
        
  return preg_replace($pat, $rep, $s);
} // nyss_stripSpaces()


function convertLowerCase($s)
{
  return strtolower($s);
} // convertLowerCase()


/*
 * browsers have changed how they support flushing content to screen
 * this function calls the necessary PHP flushing actions
 */
function nyss_flush()
{
  ob_end_flush();
  ob_flush();
  flush();
  ob_start();
} // nyss_flush()
