<?php

$include = '/\.(php|html|tpl|css|js|xml)$/i';
$exclude = '/(^(jquery|moment|bootstrap|quill|tiny|theme|plugin)|(\.map\.inc\.php|\.min\.js))/i';
$includePath = '';
$excludePath = '/(quill|tiny)/i';


header('Content-Type: text/plain; charset=utf-8');
$path = dirname(__FILE__);
view_content($path, $include, $exclude, $includePath, $excludePath);


function view_content ($start, $include, $exclude, $includePath, $excludePath) {
  $items = scandir($start);

  foreach ($items as $item) {
    if (strpos($item, '.') === 0) {
      continue;
    }

    $path = str_replace('//', '/', $start . '/' . $item);

    if (is_dir($path)) {
      view_content($path, $include, $exclude, $includePath, $excludePath);
    } else
      if (
        ($include && !preg_match($include, $item)) || ($exclude && preg_match($exclude, $item)) ||
        ($includePath && !preg_match($includePath, $path)) || ($excludePath && preg_match($excludePath, $path))
      ) {
        continue;
      } else {
        $content = file_get_contents($path);
        echo PHP_EOL. $path .':'. PHP_EOL;
        echo '===================================================================================='. PHP_EOL.PHP_EOL;
        echo $content;
        echo PHP_EOL.PHP_EOL .'===================================================================================='. PHP_EOL.PHP_EOL;
      }
  }
}
