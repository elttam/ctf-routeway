<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo html::chars(__($title)) ?></title>

<style>
html, body {
  height: 100%;
  background-color: black;
  height: 100%;
  margin: 0px;
  padding: 0px;
  color: white;
  font-family: courier, monospace; 
  text-align: center; 
}

h1 {
  margin-top: 20%;
}

a {
  color: green;
}

.copyright {
	visibility:hidden;
}

</style>
</head>
<body>

	<h1><?php echo html::chars(__($title)) ?></h1>
	<?php echo $content ?>

        <p class="copyright">
               <?php echo __('Rendered in {execution_time} seconds, using {memory_usage} of memory')?><br />
               Copyright ©2007–2008 Kohana Team
       </p>

</body>
</html>
