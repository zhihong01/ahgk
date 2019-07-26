<?php

  function joinPaths() {
      $args = func_get_args();
      $paths = array();
      foreach ($args as $arg)
          $paths = array_merge($paths, (array) $arg);

      $paths2 = array();
      foreach ($paths as $path) {
          $path = trim($path, '/');
          if (strlen($path))
              $paths2[] = $path;
      }
      $result = join('/', $paths2);
      if (strlen($paths[0]) && substr($paths[0], 0, 1) == '/')
          return '/' . $result;
      return $result;
  }

  function cleanArray(& $arr, $trim = true, $del_crlf = true) {
      foreach ($arr as $key => $value) {
          if ($del_crlf) {
              $value = str_replace(array("\r\n", "\r"), "", $value);
          }

          if ($trim) {
              $value = trim($value);
          }

          if ($value == '') {
              unset($arr[$key]);
          }
      }

      return $arr;
  }

  function cleanString($string, $trim = true, $del_crlf = true) {
      if ($del_crlf) {
          $string = str_replace(array("\r\n", "\r"), "", $string);
      }

      if ($trim) {
          $string = trim($string);
      }

      return $string;
  }

  function findAllLinks($htmlDocument, $ignoreUrl) {
      $pattern = '#<a\s[^>]*href=\"(?P<url>[^\"]+)\"[^>]*>(?P<text>.*?)</a>#is';
      $matches = array();
      preg_match_all($pattern, $htmlDocument, $matches);

      $urls = array();
      foreach ($matches['url'] as $k => $v) {
          if ((!preg_match("~^" . $ignoreUrl . "~i", $v)) && (preg_match("%^http%i", $v))) {
              $urls[$k] = array('url' => $v, 'text' => $matches['text'][$k]);
          }
      }
      return $urls;
  }

  function randKey($len=6) {
      $chars = array(
          "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
          "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
          "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
      );
      $charsLen = count($chars) - 1;
      shuffle($chars);
      $output = "";
      for ($i = 0; $i < $len; $i++) {
          $output .= $chars[mt_rand(0, $charsLen)];
      }
      return $output;
  }

  function dateDiff($start, & $lang, $end=null) {

      $sdate = strtotime($start);
      if ($sdate == 0)
          return "<em>" . $lang['date_value_not_set'] . "</em>";

      $edate = 0;
      if ($end) {
          $edate = strtotime($end);
      } else {
          $edate = time();
      }
      $timeshift = "";

      $time = $edate - $sdate;
      $ago_later = $lang['date_ago'];
      if ($time < 0) {
          $ago_later = $lang['date_later'];
          $time = $sdate - $edate;
      }

      if ($time >= 0 && $time <= 59) {
          // Seconds
          $timeshift = $time . $lang['date_seconds'] . $ago_later;
      } elseif ($time >= 60 && $time <= 3599) {
          // Minutes + Seconds
          $pmin = ($time) / 60;
          $premin = explode('.', $pmin);

          $presec = $pmin - $premin[0];
          $sec = $presec * 60;

          $timeshift = $premin[0] . $lang['date_min'] . round($sec, 0) . $lang['date_sec'] . $ago_later;
      } elseif ($time >= 3600 && $time <= 86399) {
          // Hours + Minutes
          $phour = ($time) / 3600;
          $prehour = explode('.', $phour);

          $premin = $phour - $prehour[0];
          $min = explode('.', $premin * 60);

          $timeshift = $prehour[0] . $lang['date_hrs'] . $min[0] . $lang['date_min'] . $ago_later;
      } elseif ($time >= 86400) {
          // Days + Hours + Minutes
          $pday = ($time) / 86400;
          $preday = explode('.', $pday);

          $phour = $pday - $preday[0];
          $prehour = explode('.', $phour * 24);

          $timeshift = $preday[0] . $lang['date_days'] . $prehour[0] . $lang['date_hrs'] . $ago_later;
      }
      return $timeshift;
  }

  function removeFile($path, & $filename_list, $has_subdir=true) {
      if ($filename_list != null && is_array($filename_list)) {
          foreach ($filename_list as $filename) {
              $subdir = "";

              if ($has_subdir) {
                  $subdir = substr($filename, 0, 6);
              }

              $full_name = joinPaths($path, $subdir, $filename);
              if (file_exists($full_name) && is_file($full_name)) {
                  @unlink($full_name);
              }
          }
      }
  }

?>