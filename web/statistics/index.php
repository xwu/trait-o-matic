<?php
function server_load() {
	if (file_exists("/proc/loadavg")) {
		$load = file_get_contents("/proc/loadavg");
		$load = explode(" ", $load);
		return $load[2]; // return load averaged over 15 minutes
	}
	return 0;
}

function num_cpus() {
	if (file_exists("/proc/cpuinfo")) {
		$cpu = file_get_contents("/proc/cpuinfo");
		$num = substr_count($cpu, "processor");
		return $num;
	}
	return 1;
}

function server_load_percent() {
	return round(server_load() / num_cpus() * 100, 0);
}

function xmlrpc_status() {
	$pid = trim(`cat /var/trait/.pid`);
	if (is_numeric($pid)) {
		$etime = trim(`ps -p $pid -o etime=`);
		if ($etime) {
			return $etime;
		}
	}
	return false;
}

$s = server_load_percent();
$x = xmlrpc_status();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Statistics &middot; Trait-o-matic</title>
	<!--[if lte IE 7]><link rel="stylesheet" media="screen" type="text/css" href="/media/styles-ie.css"><![endif]-->
	<!--[if IE 8]><link rel="stylesheet" media="screen" type="text/css" href="/media/styles-ie8.css"><![endif]-->
	<link rel="stylesheet" media="screen" type="text/css" href="/media/styles.css">
	<link rel="stylesheet" media="only screen and (max-device-width: 480px)" type="text/css" href="/media/styles-iphone.css">
	<meta name="viewport" content="width=device-width">
</head>
<body>
	<div id="head"><div>
		<div id="logotype"><a href="/"><img src="/media/logotype.gif" width="158" height="36" alt="Trait-o-matic"></a></div>
		<div id="menu">
			<p>
				<span class="description"><em>See also:</em></span>
				<span class="link"><a href="http://github.com/xwu/trait-o-matic/wikis">Documentation</a></span>
				<span class="bullet"> &nbsp;&bull;&nbsp; </span>
				<span class="link"><a href="http://github.com/xwu/trait-o-matic/tree">Code Repository</a></span>
			</p>
		</div>
	</div></div>
	<div id="body"><div>
		<div id="main">
			<h2><span>Statistics</span></h2>
			<div class="two-column">
				<div class="column">
					<p><span class="statistic"><?php echo $x ? $x : "--:--"; ?> </span>XMLRPC uptime</p>
				</div>
				<div class="last column">
					<p><span class="statistic"><?php echo $s; ?>% </span>server load</p>
				</div>
			</div>
		</div>
	</div></div>
	<div id="foot"><div>
		<div id="copyright">
			<p>
				<span>Copyright &copy; MMIX President and Fellows of Harvard College</span>
			</p>
		</div>
	</div></div>
</body>
</html>