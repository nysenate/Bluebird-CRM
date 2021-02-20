<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2017-02-13
// Revised: 2017-03-08 - Moved to Smarty template engine
// Revised: 2017-03-13 - More CLI options for fine-grained control
// Revised: 2017-03-16 - added --list and --preview options
// Revised: 2017-03-17 - added support for default template via --default
// Revised: 2017-04-06 - hero (header) image now downloaded and stored locally
//

require_once 'common_funcs.php';
require_once dirname(__FILE__).'/../modules/civicrm/packages/Smarty/Smarty.class.php';

define('DEFAULT_SCALED_WIDTH', 275);
define('DEFAULT_JPG_QUALITY', 90);
define('DEFAULT_PNG_QUALITY', 6);


// Convert all Bluebird configuration parameter names, transforming dots
// into underscores.  This is because Smarty does not like associative
// array indices that contain dots.
function transform_config($bbcfg)
{
  $transformed_cfg = array();
  foreach ($bbcfg as $key => $val) {
    $nkey = strtr($key, '.', '_');
    $transformed_cfg[$nkey] = $val;
  }
  return $transformed_cfg;
} // transform_config()



function set_email_defaults(&$cfg)
{
  if (empty($cfg['email.font.family'])) {
    $cfg['email.font.family'] = 'arial';
  }
  if (empty($cfg['email.font.size'])) {
    $cfg['email.font.size'] = 14;
  }
  if (empty($cfg['email.font.color'])) {
    $cfg['email.font.color'] = '#505050';
  }

  if (empty($cfg['email.background.color'])) {
    $cfg['email.background.color'] = '#ffffff';
  }

  if (!isset($cfg['email.header.include_banner'])) {
    $cfg['email.header.include_banner'] = true;
  }
  if (!isset($cfg['email.header.website_url'])) {
    $cfg['email.header.website_url'] = "http://{$cfg['shortname']}.nysenate.gov/";
  }

  if (!isset($cfg['email.footer.include_banner'])) {
    $cfg['email.footer.include_banner'] = true;
  }
  if (!isset($cfg['email.footer.include_addresses'])) {
    $cfg['email.footer.include_addresses'] = true;
  }

  if (!isset($cfg['email.images.instance.base_url'])) {
    $cfg['email.images.instance.base_url'] = "{$cfg['public.url.base']}/{$cfg['envname']}/{$cfg['shortname']}/images";
  }
  if (!isset($cfg['email.images.common.base_url'])) {
    $cfg['email.images.common.base_url'] = "{$cfg['public.url.base']}/{$cfg['envname']}/common/images";
  }

  if (!isset($cfg['senator.name.formal'])) {
    $cfg['senator.name.formal'] = 'New York State Senator';
  }
  if (!isset($cfg['senator.address.albany'])) {
    $cfg['senator.address.albany'] = 'Legislative Office Bldg|Albany, NY 12247';
  }
  if (!isset($cfg['senator.address.district'])) {
    $cfg['senator.address.district'] = 'ADDRESS OF DISTRICT OFFICE';
  }
  if (!isset($cfg['senator.address.satellite'])) {
    $cfg['senator.address.satellite'] = '';
  }
} // set_email_defaults()



function retrieve_and_store_image($url, $filepath, $scaled_width = 0)
{
  // Download the image data from the provided URL.
  // Note that "allow_url_fopen" must be enabled in php.ini.
  $image_data = file_get_contents($url);
  if ($image_data === false) {
    return false;
  }

  // Create an image resource from the image data.
  $ih = imagecreatefromstring($image_data);
  if ($ih === false) {
    return false;
  }

  // Scale the image to the provided size, if requested.
  if ($scaled_width != 0) {
    // No height parameter forces GD to preserve the aspect ratio.
    $scaled_ih = imagescale($ih, $scaled_width);
    imagedestroy($ih);
    $ih = $scaled_ih;
  }

  // Output the image data to the specified filepath.  The output format
  // is determined from the file extension.
  $fileext = strtolower(substr(strrchr($filepath, '.'), 1));
  switch ($fileext) {
    case 'gif':
      $rc = imagegif($ih, $filepath);
      break;
    case 'jpg':
    case 'jpeg':
      $rc = imagejpeg($ih, $filepath, DEFAULT_JPG_QUALITY);
      break;
    case 'png':
      $rc = imagepng($ih, $filepath, DEFAULT_PNG_QUALITY);
      break;
    default:
      echo "Error: $filepath: Unknown image file type\n";
  }
  imagedestroy($ih);
  return $rc;
} // retrieve_and_store_image()



function retrieve_senator_info($name, $bbcfg, $is_local = true)
{
  $ch = curl_init("https://www.nysenate.gov/senators-json/$name");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $senator_json = curl_exec($ch);
  curl_close($ch);

  if ($senator_json !== false) {
    $senator_info = json_decode($senator_json);
    // A non-matching senator name will return the entire array of senators in
    // the JSON data.  Therefore, if the JSON is an array, the lookup failed.
    if (is_array($senator_info)) {
      return null;
    }
    else {
      // Convert the single object into an array.
      $senator_info = (array)$senator_info;
      if ($is_local) {
        $tpldir = $bbcfg['data.rootdir'].'/'.$bbcfg['data_dirname'].'/pubfiles/images/template';
        foreach (array('img', 'hero_img') as $imgtype) {
          $url = $senator_info[$imgtype];
          $fname = "{$name}_website_{$imgtype}.jpg";
          $ipath = "$tpldir/$fname";
          $rc = retrieve_and_store_image($url, $ipath, DEFAULT_SCALED_WIDTH);
          if ($rc === true) {
            $senator_info[$imgtype] = $bbcfg['email.images.instance.base_url']."/template/$fname";
          }
        }
      }
      return $senator_info;
    }
  }
  else {
    return null;
  }
} // retrieve_senator_info()



function initialize_smarty($bbcfg, $sinfo, $tpldir)
{
  if ($bbcfg == null || $sinfo == null) {
    return null;
  }

  if ($tpldir == null) {
    $tpldir = dirname(__FILE__).'/../templates';
  }
  
  $smarty = new Smarty();
  $smarty->left_delimiter = '{{';
  $smarty->right_delimiter = '}}';
  $smarty->template_dir = $tpldir;
  $smarty->compile_dir = '/tmp';
  $smarty->assign_by_ref('bbcfg', $bbcfg);
  $smarty->assign_by_ref('senator', $sinfo);
  return $smarty;
} // initialize_smarty()



function display_template($tpl, $show_text = false)
{
  if ($show_text) {
    print $tpl['text'];
  }
  else {
    print $tpl['html'];
  }
} // display_template()



function generate_template($smarty, $ttype, $tdisp)
{
  $mime_types = array('html', 'text');
  $tpl = array();

  foreach ($mime_types as $mime_type) {
    $tfile = "{$ttype}_email_$tdisp.$mime_type.tpl";
    if ($smarty->template_exists($tfile)) {
      $tpl[$mime_type] = $smarty->fetch($tfile);
      $smarty->clear_compiled_tpl($tfile);
    }
    else {
      _stderr("ERROR: Template file [$tfile] not found");
      return null;
    }
  }
  return $tpl;
} // generate_template()



// Returns the user-friendly template name from the template type.
function get_template_name($ttype)
{
  $tpl_type_map = array(
    'classic' => 'NYSS Classic Theme',
    'responsive' => 'NYSS Website Theme'
  );

  if (isset($tpl_type_map[$ttype])) {
    return $tpl_type_map[$ttype];
  }
  else {
    return null;
  }
} // get_template_name()



// Returns the CiviCRM component type string from the template disposition.
function get_component_type($tdisp)
{
  $tpl_disp_map = array(
    'header' => 'Header',
    'footer' => 'Footer'
  );

  if (isset($tpl_disp_map[$tdisp])) {
    return $tpl_disp_map[$tdisp];
  }
  else {
    return null;
  }
} // get_component_type()



// Retrieve the matching template from the database using the template type
// and the template disposition (header or footer).
// Returns the template as an array with 'html' and 'text' elements, or null
// if no template can be found, or false if more than one template is found.
function retrieve_template($dbh, $ttype, $tdisp)
{
  $name = get_template_name($ttype);
  $ctype = get_component_type($tdisp);

  $sql = "SELECT body_html, body_text ".
         "FROM civicrm_mailing_component ".
         "WHERE name=? AND component_type=?";
  $sth = $dbh->prepare($sql);
  $sth->execute(array($name, $ctype));
  if (!$sth) {
    print_r($dbh->errorInfo());
    return null;
  }

  $row_count = 0;
  while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    $tpl = array('html' => $row['body_html'], 'text' => $row['body_text']);
    $row_count++;
  }

  if ($row_count == 1) {
    return $tpl;
  }
  else if ($row_count == 0) {
    return null;
  }
  else {
    return false;
  }
} // retrieve_template()



// Delete all matching occurrences of the provided template.
function delete_template($dbh, $ttype, $tdisp)
{
  $name = get_template_name($ttype);
  $ctype = get_component_type($tdisp);

  $sql = "DELETE FROM civicrm_mailing_component ".
         "WHERE name=? AND component_type=?";
  $sth = $dbh->prepare($sql);
  $sth->execute(array($name, $ctype));
  if (!$sth) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    return true;
  }
} // delete_template()



function update_template($dbh, $ttype, $tdisp, $tpl)
{
  $name = get_template_name($ttype);
  $ctype = get_component_type($tdisp);
  $subj = "$name $ctype";  // eg. "NYSS Classic Theme Header"

  // First, confirm that a single copy of template is already in the database.
  $curtpl = retrieve_template($dbh, $ttype, $tdisp);

  if ($curtpl === null || $curtpl === false) {
    // There are no matching templates, or multiple matching templates.
    if ($curtpl === false) {
      _stderr("NOTE: Deleting multiple copies of template [$ttype/$tdisp]");
      delete_template($dbh, $ttype, $tdisp);
    }
    // Now there are no copies of the named template, so insert it.
    $sql = "INSERT INTO civicrm_mailing_component ".
           "(name, component_type, subject, body_html, body_text, is_default, is_active) ".
           "VALUES (?, ?, ?, ?, ?, ?, ?)";
    $params = array($name, $ctype, $subj, $tpl['html'], $tpl['text'], 0, 1);
  }
  else {
    // There is a single matching template, so update it.
    $sql = "UPDATE civicrm_mailing_component ".
           "SET subject=?, body_html=?, body_text=? ".
           "WHERE name=? AND component_type=?";
    $params = array($subj, $tpl['html'], $tpl['text'], $name, $ctype);
  }

  $sth = $dbh->prepare($sql);
  $sth->execute($params);
  if (!$sth) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    return true;
  }
} // update_template()



function update_default_template($dbh, $ttype)
{
  $name = get_template_name($ttype);
  if ($name == null) {
    return false;
  }

  // First, clear the default status for all header/footer templates.
  $sql = "UPDATE civicrm_mailing_component ".
         "SET is_default=0 ".
         "WHERE component_type='Header' OR component_type='Footer'";
  if ($dbh->exec($sql) === false) {
    print_r($dbh->errorInfo());
    return false;
  }

  $sql = "UPDATE civicrm_mailing_component ".
         "SET is_default=1 ".
         "WHERE name=? AND (component_type='Header' OR component_type='Footer')";
  $params = array($name);
  $sth = $dbh->prepare($sql);
  $sth->execute($params);
  if (!$sth) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    return true;
  }
} // update_default_template()



function _stderr($s)
{
  fwrite(STDERR, "$s\n");
} // _stderr()



function usage($p)
{
  _stderr("Usage: $p instance [--website-shortname|-w <shortname>] [--all|-a | --classic|-c | --responsive|-r] [--default|-d <template-type>] [--header|-h | --footer|-f] [--list|-l | --preview|-p | --update|-u] [--text|-t]
    Option details:
      instance: the Bluebird instance from the config file
      --website-shortname: senator shortname on website for retrieving JSON
                           (this should generally be the same as <instance>)
      --all: generate all available template types (classic & responsive)
      --classic: generate only a classic template
      --responsive: generate only a responsive template
      --default: specify default template type (omit to keep current default)
      --header: generate only the header template for each template type
      --footer: generate only the footer template for each template type
      --list: display current template(s) from database [default]
      --preview: display newly generated template(s) without updating database
      --update: update the database with all generated templates
      --text: display only text version of template (default is HTML)");
} // usage()



$prog = basename($argv[0]);
$instance = null;
$shortname = null;
$default_ttype = null;
$mode = 'list';
$text_display = false;
$local_images = true;
$tpl_types = array();
$tpl_disps = array('header' => true, 'footer' => true);

$i = 1;
while ($i < $argc) {
  switch ($argv[$i]) {
    case '--website-shortname': case '-w':
      $shortname = $argv[++$i];
      break;
    case '--all': case '-a':
      $tpl_types['classic'] = true;
      $tpl_types['responsive'] = true;
      break;
    case '--classic': case '-c':
      $tpl_types['classic'] = true;
      break;
    case '--responsive': case '-r':
      $tpl_types['responsive'] = true;
      break;
    case '--default': case '-d':
      $default_ttype = $argv[++$i];
      break;
    case '--header': case '-h':
      $tpl_disps['header'] = true;
      $tpl_disps['footer'] = false;
      break;
    case '--footer': case '-f':
      $tpl_disps['header'] = false;
      $tpl_disps['footer'] = true;
      break;
    case '--list': case '-l':
      $mode = 'list';
      break;
    case '--preview': case '-p':
      $mode = 'preview';
      break;
    case '--update': case '-u':
      $mode = 'update';
      break;
    case '--text': case '-t':
      $text_display = true;
      break;
    case '--no-local-images': case '-n':
      $local_images = false;
      break;
    case '--help':
      usage($prog);
      exit(0);
      break;
    case ($argv[$i][0] == '-'):
      _stderr("$prog: {$argv[$i]}: Unknown option");
      exit(1);
    default:
      $instance = $argv[$i];
      break;
  }
  $i++;
}

if (!$instance) {
  _stderr("$prog: Must specify an instance");
  usage($prog);
  exit(1);
}
else if (count($tpl_types) == 0) {
  _stderr("$prog: Must specify a template type (--all, --classic, --responsive)");
  usage($prog);
  exit(1);
}
// Confirm that default template type is valid.
else if ($default_ttype && !isset($tpl_types[$default_ttype])) {
  _stderr("$prog: [$default_ttype] is not a valid default template type");
  exit(1);
}

if (!$shortname) {
  _stderr("$prog: Warning: Website shortname not specified; using instance name [$instance]");
  $shortname = $instance;
}

$bootstrap = bootstrap_script($prog, $instance, DB_TYPE_CIVICRM);
if ($bootstrap == null) {
  _stderr("$prog: Unable to bootstrap this script; exiting");
  exit(1);
}

$dbref = $bootstrap['dbrefs'][DB_TYPE_CIVICRM];
$bbconfig = $bootstrap['bbconfig'];
// Set default values for all e-mail template config.
set_email_defaults($bbconfig);


if ($mode == 'preview' || $mode == 'update') {
  $seninfo = retrieve_senator_info($shortname, $bbconfig, $local_images);
  if ($seninfo == null) {
    _stderr("$prog: Unable to retrieve info for [$shortname] from website");
    exit(1);
  }

  $template_dir = $bbconfig['app.rootdir'].'/templates';
  $smarty_bbcfg = transform_config($bbconfig);

  // Initialize the Smarty template engine.
  $smarty = initialize_smarty($smarty_bbcfg, $seninfo, $template_dir);
}

foreach ($tpl_types as $tpl_type => $dummy) {
  foreach ($tpl_disps as $tpl_disp => $is_td_active) {
    if ($is_td_active) {
      if ($mode == 'list') {
        // In "list" mode, the current template is retrieved from the db.
        $tpl = retrieve_template($dbref, $tpl_type, $tpl_disp);
        if ($tpl !== null && $tpl !== false) {
          display_template($tpl, $text_display);
        }
        else if ($tpl === null) {
          _stderr("ERROR: Unable to find a matching template for [$tpl_type/$tpl_disp]");
        }
        else {
          _stderr("ERROR: Found more than one matching template for [$tpl_type/$tpl_disp]");
        }
      }
      else {
        // In "preview" and "update" modes, a new template is generated.
        _stderr("Generating template [$tpl_type/$tpl_disp]");
        $tpl = generate_template($smarty, $tpl_type, $tpl_disp);
        if ($tpl) {
          if ($mode == 'update') {
            if (update_template($dbref, $tpl_type, $tpl_disp, $tpl) == true) {
              _stderr("Successfully updated database for template [$tpl_type/$tpl_disp]");
            }
            else {
              _stderr("ERROR: Failed to update database for template [$tpl_type/$tpl_disp]");
            }
          }
          else { // mode == 'preview'
            display_template($tpl, $text_display);
          }
        }
        else {
          _stderr("ERROR: Unable to generate template [$tpl_type/$tpl_disp]");
        }
      }
    }
  }
}

if ($default_ttype && $mode == 'update') {
  if (update_default_template($dbref, $default_ttype) == true) {
    _stderr("Successfully enabled [$default_ttype] as default template");
  }
  else {
    _stderr("ERROR: Unable to enable [$default_ttype] as default template");
  }
}

$dbref = null;
exit(0);
