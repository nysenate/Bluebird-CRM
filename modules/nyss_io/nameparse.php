<?php
/*
Name:  nameparse.php
Version: 0.2a
Date:  030507
First:  030407
License:  GNU General Public License v2
Bugs:  If one of the words in the middle name is Ben (or St., for that matter),
    or any other possible last-name prefix, the name MUST be entered in
    last-name-first format. If the last-name parsing routines get ahold
    of any prefix, they tie up the rest of the name up to the suffix. i.e.:

    William Ben Carey  would yield 'Ben Carey' as the last name, while,
    Carey, William Ben  would yield 'Carey' as last and 'Ben' as middle.

    This is a problem inherent in the prefix-parsing routines algorithm,
    and probably will not be fixed. It's not my fault that there's some
    odd overlap between various languages. Just don't name your kids
    'Something Ben Something', and you should be alright.
Source: http://alphahelical.com/code/misc/nameparse/
*/

function norm_str($string) {
  return trim(strtolower(str_replace('.', '', $string)));
}

function in_array_norm($needle, $haystack) {
  return in_array(norm_str($needle), $haystack);
}

function parse_name($fullname) {
  $titles   = array('dr','miss','mr','mrs','ms','judge');
  $prefices = array('ben','bin','da','dal','de','del','der','de','e',
                    'la','le','san','st','ste','van','vel','von');
  $suffices = array('esq','esquire','jr','sr','2','ii','iii','iv');

  $pieces   = explode(',', preg_replace('/\s+/', ' ', trim($fullname)));
  $n_pieces = count($pieces);

  //debug
  //print_r($pieces);
  //print_r($n_pieces);exit();

  switch ($n_pieces) {
    case 1:  // array(title first middles last suffix)
      $subp = explode(' ', trim($pieces[0]));
      $n_subp = count($subp);
      for ($i = 0; $i < $n_subp; $i++) {
        $curr = trim($subp[$i]);
        $next = trim($subp[$i+1]);

        if ($i == 0 && in_array_norm($curr, $titles)) {
          $out['title'] = $curr;
          continue;
        }

        if (!$out['first']) {
          $out['first'] = $curr;
          continue;
        }

        if ($i == $n_subp-2 && $next && in_array_norm($next, $suffices)) {
          if ($out['last']) {
            $out['last'] .= " $curr";
          }
          else {
            $out['last'] = $curr;
          }
          $out['suffix'] = $next;
          break;
        }

        if ($i == $n_subp-1) {
          if ($out['last']) {
            $out['last'] .= " $curr";
          }
          else {
            $out['last'] = $curr;
          }
          continue;
        }

        if (in_array_norm($curr, $prefices)) {
          if ($out['last']) {
            $out['last'] .= " $curr";
          }
          else {
            $out['last'] = $curr;
          }
          continue;
        }

        if ($next == 'y' || $next == 'Y') {
          if ($out['last']) {
            $out['last'] .= " $curr";
          }
          else {
            $out['last'] = $curr;
          }
          continue;
        }

        if ($out['last']) {
          $out['last'] .= " $curr";
          continue;
        }

        if ($out['middle']) {
          $out['middle'] .= " $curr";
        }
        else {
          $out['middle'] = $curr;
        }
      }
      break;
    case 2:
      switch (in_array_norm($pieces[1], $suffices)) {
        case true: // array(title first middles last,suffix)
          $subp = explode(' ', trim($pieces[0]));
          $n_subp = count($subp);
          for ($i = 0; $i < $n_subp; $i++) {
            $curr = trim($subp[$i]);
            $next = trim($subp[$i+1]);

            if ($i == 0 && in_array_norm($curr, $titles)) {
              $out['title'] = $curr;
              continue;
            }

            if (!$out['first']) {
              $out['first'] = $curr;
              continue;
            }

            if ($i == $n_subp - 1) {
              if ($out['last']) {
                $out['last'] .= " $curr";
              }
              else {
                $out['last'] = $curr;
              }
              continue;
            }

            if (in_array_norm($curr, $prefices)) {
              if ($out['last']) {
                $out['last'] .= " $curr";
              }
              else {
                $out['last'] = $curr;
              }
              continue;
            }

            if ($next == 'y' || $next == 'Y') {
              if ($out['last']) {
                $out['last'] .= " $curr";
              }
              else {
                $out['last'] = $curr;
              }
              continue;
            }

            if ($out['last']) {
              $out['last'] .= " $curr";
              continue;
            }

            if ($out['middle']) {
              $out['middle'] .= " $curr";
            }
            else {
              $out['middle'] = $curr;
            }
          }
          $out['suffix'] = trim($pieces[1]);
          break;
        case false: // array(last, title first middles suffix)
          $subp = explode(' ', trim($pieces[1]));
          $n_subp = count($subp);
          for ($i = 0; $i < $n_subp; $i++) {
            $curr = trim($subp[$i]);
            $next = trim($subp[$i+1]);

            if ($i == 0 && in_array_norm($curr, $titles)) {
              $out['title'] = $curr;
              continue;
            }

            if (!$out['first']) {
              $out['first'] = $curr;
              continue;
            }

            if ($i == $n_subp-2 && $next && in_array_norm($next, $suffices)) {
              if ($out['middle']) {
                $out['middle'] .= " $curr";
              }
              else {
                $out['middle'] = $curr;
              }
              $out['suffix'] = $next;
              break;
            }

            if ($i == $n_subp-1 && in_array_norm($curr, $suffices)) {
              $out['suffix'] = $curr;
              continue;
            }

            if ($out['middle']) {
              $out['middle'] .= " $curr";
            }
            else {
              $out['middle'] = $curr;
            }
          }
          $out['last'] = $pieces[0];
          break;
      }
      unset($pieces);
      break;
    case 3:  // array(last,title first middles,suffix)
      $subp = explode(' ', trim($pieces[1]));
      $n_subp = count($subp);
      for ($i = 0; $i < $n_subp; $i++) {
        $curr = trim($subp[$i]);
        $next = trim($subp[$i+1]);
        if ($i == 0 && in_array_norm($curr, $titles)) {
          $out['title'] = $curr;
          continue;
        }

        if (!$out['first']) {
          $out['first'] = $curr;
          continue;
        }

        if ($out['middle']) {
          $out['middle'] .= " $curr";
        }
        else {
          $out['middle'] = $curr;
        }
      }

      $out['last'] = trim($pieces[0]);
      $out['suffix'] = trim($pieces[2]);
      break;
    default:  // unparseable
      unset($pieces);
      break;
  }

  return $out;
}

?>
