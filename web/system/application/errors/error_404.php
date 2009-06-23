<?php
header("HTTP/1.1 404 Not Found");
$host = $_SERVER['SERVER_NAME'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Not Found &middot; Trait-o-matic</title>
		<link rel="stylesheet" media="screen" type="text/css" href="/media/error.css" />
		<!--[if lte IE 7]><link rel="stylesheet" media="screen" type="text/css" href="/media/error-ie.css" /><![endif]-->
		<script language="javascript" type="text/javascript" src="/scripts/placeholders.js"></script>
	</head>
	<body>
		<div class="wrapper"><div class="middle"><div class="inner">
			<div class="capsule">
				<p class="badge"><img src="/media/404.gif" width="100" height="100" alt="404" /></p>
				<h2>Sorry, we cannot find the page you requested</h2>
				<p>This page may have been moved or deleted. You may want to try the following:</p>
				<ul>
					<li>Check that the address is spelt correctly (if you typed it in manually).</li>
					<li>Use the Back button to try something else on the previous page.</li>
					<li>Return to our <a href="/">home page</a> and follow the links to find the information you want.</li>
					<li>Enter a search term below to see if Google can find it.</li>
				</ul>
				<div class="search">
					<form id="gs" method="get" action="http://google.com/search"><div>
						<input type="hidden" name="domains" value="<?php echo htmlspecialchars($host); ?>" />
						<input type="hidden" name="sitesearch" value="<?php echo htmlspecialchars($host); ?>" />
						<input type="search" class="search-field" id="query" name="q" size="30" value="" placeholder="Google" results="0" />
					</div></form>
				</div>
				<!--<h1><?php echo $heading; ?></h1><?php echo $message; ?>-->
			</div>
		</div></div></div>
	</body>
</html>