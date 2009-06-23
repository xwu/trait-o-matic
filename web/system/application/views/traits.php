<?php
// always expired, always modified
header("Expires: Sat, 05 Nov 2005 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

// HTTP/1.0
header("Pragma: no-cache");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Trait-o-matic</title>
	<link rel="stylesheet" media="screen" type="text/css" href="/media/styles.css">
	<link rel="stylesheet" media="screen" type="text/css" href="/media/index.css">
	<!--[if lte IE 7]><link rel="stylesheet" media="screen" type="text/css" href="/media/styles-ie.css"><![endif]-->
	<!--[if lte IE 7]><link rel="stylesheet" media="screen" type="text/css" href="/media/index-ie.css"><![endif]-->
	<!--[if IE 8]><link rel="stylesheet" media="screen" type="text/css" href="/media/styles-ie8.css"><![endif]-->
	<link rel="stylesheet" media="only screen and (max-device-width: 480px)" type="text/css" href="/media/styles-iphone.css">
	<link rel="stylesheet" media="only screen and (max-device-width: 480px)" type="text/css" href="/media/index-iphone.css">
	<meta name="viewport" content="user-scalable=no,width=device-width">
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.2/prototype.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.1/effects.js"></script>
</head>
<body>
<?php
// show header only if we're not submitting into an iframe
if (!isset($asynchronous) || !$asynchronous):
?>
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
	<div id="subhead"><div>
		<h2><span>Find and classify phenotypic correlations for variations in whole genomes</span></h2>
		<div id="submenu">
			<p>
				<span class="link"><a href="/">View Samples</a></span>
				<span class="bullet"> &nbsp;&bull;&nbsp; </span>
				<span class="current">Submit Query</span>
				<span class="bullet"> &nbsp;&bull;&nbsp; </span>
				<span class="link"><a href="/results/">Retrieve Results</a></span>
			</p>
		</div>
	</div></div>
<?php
endif;
?>
	<div id="body"><div>
		<div id="main">
			<div class="two-column">
				<div class="column">
					<h3>2. Traits</h3>
					<p>We need some additional details about you in order to make an accurate analysis. Some information is also collected for error checking and statistical purposes.</p>
				</div>
				<div class="last column">
<?php if (validation_errors()): ?>
					<div class="error"><?php echo validation_errors('<div>', '</div>'); ?></div>
<?php endif; ?>
					<form enctype="multipart/form-data" name="trait-form" id="trait-form" method="POST" action="/query/">
						<div class="wrapper">
							<p><label class="label">Date of Birth<span class="description"> (YYYY-MM-DD)</span><br>
							<input type="text" class="wide text" name="date-of-birth" size="60" id="date-of-birth" value="<?php echo form_error('date-of-birth') ? '' : set_value('date-of-birth'); ?>"></label></p>
							<p><label class="label">Sex</label><br>
							<label><input type="radio" class="radio" name="sex" value="female" id="female-sex"<?php echo set_radio('sex', 'female'); ?>> Female</label><br>
							<label><input type="radio" class="radio" name="sex" value="male" id="male-sex"<?php echo set_radio('sex', 'male'); ?>> Male</label></p>
							<p><label class="label">Ancestry<span class="description"> (check all that apply)</span></label><br>
							<label><input type="checkbox" class="checkbox" name="ancestry[]" value="ami" id="indigenous-american-ancestry"<?php echo set_checkbox('ancestry[]', 'ami'); ?>> Indigenous American</label><br>
							<label><input type="checkbox" class="checkbox" name="ancestry[]" value="afn" id="north-african-ancestry"<?php echo set_checkbox('ancestry[]', 'afn'); ?>> North African</label><br>
							<label><input type="checkbox" class="checkbox" name="ancestry[]" value="afs" id="sub-saharan-african-ancestry"<?php echo set_checkbox('ancestry[]', 'afs'); ?>> Sub-Saharan African</label><br>
							<label><input type="checkbox" class="checkbox" name="ancestry[]" value="eur" id="european-ancestry"<?php echo set_checkbox('ancestry[]', 'eur'); ?>> European</label><br>
							<label><input type="checkbox" class="checkbox" name="ancestry[]" value="asw" id="west-asian-ancestry"<?php echo set_checkbox('ancestry[]', 'asw'); ?>> West Asian</label><br>
							<label><input type="checkbox" class="checkbox" name="ancestry[]" value="asc" id="central-south-asian-ancestry"<?php echo set_checkbox('ancestry[]', 'asc'); ?>> Central/South Asian</label><br>
							<label><input type="checkbox" class="checkbox" name="ancestry[]" value="ase" id="east-southeast-asian-ancestry"<?php echo set_checkbox('ancestry[]', 'ase'); ?>> East/Southeast Asian</label><br>
							<label><input type="checkbox" class="checkbox" name="ancestry[]" value="oci" id="indigenous-oceanic-ancestry"<?php echo set_checkbox('ancestry[]', 'oci'); ?>> Indigenous Oceanic</label></p>
							<p><label class="label">Blood Type<span class="description"> (optional)</span><br>
							<select class="wide" name="blood-type" id="blood-type">
								<option value=""<?php echo set_select('blood-type', ''); ?>>(select one)</option>
								<option value="O+"<?php echo set_select('blood-type', 'O+'); ?>>O+</option>
								<option value="A+"<?php echo set_select('blood-type', 'A+'); ?>>A+</option>
								<option value="B+"<?php echo set_select('blood-type', 'B+'); ?>>B+</option>
								<option value="AB+"<?php echo set_select('blood-type', 'AB+'); ?>>AB+</option>
								<option value="O-"<?php echo set_select('blood-type', 'O-'); ?>>O&minus;</option>
								<option value="A-"<?php echo set_select('blood-type', 'A-'); ?>>A&minus;</option>
								<option value="B-"<?php echo set_select('blood-type', 'B-'); ?>>B&minus;</option>
								<option value="AB-"<?php echo set_select('blood-type', 'AB-'); ?>>AB&minus;</option>
							</select></label></p>
							<p><label class="label">Eye Color<br>
							<select class="wide" name="eye-color" id="eye-color">
								<option value=""<?php echo set_select('eye-color', ''); ?>>(select one)</option>
								<optgroup label="common">
								<option value="brown"<?php echo set_select('eye-color', 'brown'); ?>>Brown</option>
								<option value="blue"<?php echo set_select('eye-color', 'blue'); ?>>Blue</option>
								<option value="green"<?php echo set_select('eye-color', 'green'); ?>>Green</option>
								</optgroup>
								<optgroup label="other">
								<option value="amber"<?php echo set_select('eye-color', 'amber'); ?>>Amber</option>
								<option value="gray"<?php echo set_select('eye-color', 'gray'); ?>>Gray</option>
								<option value="hazel"<?php echo set_select('eye-color', 'hazel'); ?>>Hazel</option>
								<option value="red"<?php echo set_select('eye-color', 'red'); ?>>Red</option>
								<option value="violet"<?php echo set_select('eye-color', 'violet'); ?>>Violet</option>
								<option value="aniridic"<?php echo set_select('eye-color', 'aniridic'); ?>>(Aniridic)</option>
								<option value="heterochromic"<?php echo set_select('eye-color', 'heterochromic'); ?>>(Heterochromic)</option>
								</optgroup>
							</select></label></p>
							<p><label class="label">Handedness<br>
							<select class="wide" name="handedness" id="handedness">
								<option value=""<?php echo set_select('handedness', ''); ?>>(select one)</option>
								<option value="right"<?php echo set_select('handedness', 'right'); ?>>Right</option>
								<option value="left"<?php echo set_select('handedness', 'left'); ?>>Left</option>
								<option value="mixed"<?php echo set_select('handedness', 'mixed'); ?>>Mixed</option>
								<option value="ambidextrous"<?php echo set_select('handedness', 'ambidextrous'); ?>>Ambidextrous</option>
							</select></label></p>
							<p><label class="label">Height<span class="description"> (optional)</span></label><br>
							<label><input type="text" name="height-in-centimeters" size="6" id="height-in-centimeters"> cm</label>
							<span class="or">&ndash; or &ndash;</span> <label><input type="text" name="height-in-feet" size="3" id="height-in-feet"> feet</label> <label><input type="text" name="height-in-inches" size="3" id="height-in-inches"> inches</label></p>
							<p><label class="label">Weight<span class="description"> (optional)</span></label><br>
							<label><input type="text" name="weight-in-kilograms" size="6" id="weight-in-kilograms"> kg</label>
							<span class="or">&ndash; or &ndash;</span> <label><input type="text" name="weight-in-pounds" size="3" id="weight-in-pounds"> pounds</label> <label><input type="text" name="weight-in-ounces" size="3" id="weight-in-ounces"> ounces</label></p>
						</div>
<?php if (isset($job)): ?>
						<input type="hidden" name="job" id="job" value="<?php echo $job; ?>">
<?php endif; ?>
						<p class="submit"><span class="label"></span><input type="submit" name="submit-trait-form" id="submit-trait-form" value="Next &raquo;"></p>
					</form>
				</div>
			</div>
		</div>
	</div></div>
<?php
// show footer only if we're not submitting into an iframe
if (!isset($asynchronous) || !$asynchronous):
?>
	<div id="foot"><div>
		<div id="copyright">
			<p>
				<span>Copyright &copy; MMIX President and Fellows of Harvard College<br>[{elapsed_time} s]</span>
			</p>
		</div>
	</div></div>
<?php
// script to copy iframe contents into parent document
else:
?>
<script type="text/javascript">
var node = top.document.getElementById("main");
$(node).update($("main").innerHTML);
Element.extend(top.document).fire("ajax:update");
</script>
<?php
endif;
?>
</body>
</html>