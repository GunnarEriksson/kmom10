<!doctype html>
<html lang='<?=$lang?>'>
<head>
<meta charset='utf-8'/>
<title><?=getTitle($title)?></title>
<link rel='shortcut icon' href='<?=$favicon?>'/>
<?php foreach ($stylesheets as $stylesheet): ?>
<link rel='stylesheet' type='text/css' href='<?=$stylesheet?>'/>
<?php endforeach; ?>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2">
</head>
<body>
  <div id='wrapper'>
    <div id='header'><?=$header?></div>
    <div id='navbar'><?= getNavbar($menu)?><?= getSearchMovieTitleForm()?></div>
    <div id='main'><?=$main?></div>
    <div id='footer'><?=$footer?></div>
  </div>
</body>
</html>
