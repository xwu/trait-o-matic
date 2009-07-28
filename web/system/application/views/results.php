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
	<script type="text/javascript" src="/scripts/sortable.js"></script>
	<script type="text/javascript" src="/scripts/toggle.js"></script>
	<script type="text/javascript" src="/scripts/legend.js"></script>
	<script type="text/javascript" src="/scripts/results.js"></script>
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
	<div id="subhead"><div>
		<h2><span>Find and classify phenotypic correlations for variations in whole genomes</span></h2>
		<div id="submenu">
			<p>
				<span class="link"><a href="/">View Samples</a></span>
				<span class="bullet"> &nbsp;&bull;&nbsp; </span>
				<span class="link"><a href="/query/">Submit Query</a></span>
				<span class="bullet"> &nbsp;&bull;&nbsp; </span>
				<span class="current">Retrieve Results</span>
			</p>
		</div>
	</div></div>
	<div id="body"><div>
		<div id="main">
			<div class="two-column">
				<div class="column">
					<h3><?php echo $username; ?></h3>
					<p>Date of birth: <?php echo $phenotypes['date-of-birth']; ?><br>
					<?php echo ucfirst(lang($phenotypes['sex'])); ?>, <?php function r($v, $w) { if ($v != '') { $v .= ', '; } $v .= lang($w); return $v; } print array_reduce($phenotypes['ancestry'], 'r'); ?></p>
					<p>Download <a href="/download/ns/<?php echo urlencode($job_id); ?>">nsSNPs</a> or <a href="/download/genotype/<?php echo urlencode($job_id); ?>">source data (all SNPs)</a></p>
<?php
if (!$public):
$public_mode_strings = array(
	-1 => 'only you',
	0 => 'only you and expert curators',
	1 => 'everyone'
);
// these are just for show to express
// what it is that users, curators, and
// others may do at each of the three
// modes
//
// group = curators
// w = curate
// x = reprocess, etc.
$public_mode_symbols = array(
	-1 => '700',
	0 => '760',
	1 => '764'
);
$public_mode_actions = array(
	-1 => 'Restrict access to only me',
	0 => 'Restrict access to only me and expert curators',
	1 => 'Grant access to everyone (public sample)'
);
?>
					<p>Currently, <strong><?php echo htmlspecialchars($public_mode_strings[$job_public_mode]); ?></strong> may view these results<?php foreach($public_mode_actions as $k => $v): if ($job_public_mode != $k): ?><br><a href="/chmod/<?php echo urlencode($public_mode_symbols[$k]); ?>/<?php echo urlencode($job_id); ?>"><?php echo htmlspecialchars($v); ?></a><?php endif; endforeach; ?></p>
					<p><a href="/reprocess/<?php echo urlencode($job_id); ?>" onclick="return window.confirm('Are you sure you want to discard current results and reprocess this query?')">Reprocess this query</a> &nbsp;&bull;&nbsp; <a href="/logout/">Log out</a></p>
<?php endif; ?>
				</div>
				<div class="last column">
					<p id="allele-frequency-legend" class="legend"><strong>Highlighting by allele frequency</strong><br>
					<span class="rare">Rare (<i>f</i>&nbsp;&lt;&nbsp;0.05)</span><br>
					<span class="minor">Minor (0.05&nbsp;&le;&nbsp;<i>f</i>&nbsp;&lt;&nbsp;0.5)</span><br>
					<span class="major">Major (<i>f</i>&nbsp;&ge;&nbsp;0.5)</span><br>
					<span class="unknown-frequency">Unknown</span></p>
					<!-- h3>Partial exome (20%)</h3>
					<p>Average coverage: 7x<br>
					Variants: 1000</p -->
					<!-- p><img src="http://chart.apis.google.com/chart?cht=bvs&chxt=x,y&chxl=0:|1||3||5||7||9||11||13||15||17||19||21||X|Y|1:|0%20Mb|2.5%20Mb&chs=264x80&chd=t:0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5,0.5|2.45,2.43,1.99,1.92,1.81,1.71,1.58,1.46,1.35,1.35,1.35,1.33,1.14,1.05,1.00,0.900,0.817,0.778,0.638,0.636,0.470,0.495,1.53,0.510&chco=4cb825,c4e8b7&chbh=2,6&chds=0,2.5" width="264" height="80" alt="[To be completed later]"></p -->
				</div>
			</div>
			<div id="results">
<?php
foreach (array('omim' => 'OMIM', 'snpedia' => 'SNPedia', 'hgmd' => 'HGMD', 'morbid' => 'Other hypotheses') as $k => $v):
?>
			<h3 class="toggle"><?php echo htmlspecialchars($v); ?><?php if (array_key_exists($k, $phenotypes) && count($phenotypes[$k])): ?> <span class="count">(<?php echo count($phenotypes[$k]); ?>)</span><?php endif; ?></h3>
			<div class="data">
				<table class="sortable data" width="100%">
					<col width="25%">
					<col width="25%">
<?php if (array_key_exists($k, $phenotypes) && count($phenotypes[$k]) && array_key_exists('score', $phenotypes[$k][0])): ?>
					<col width="40%">
					<col width="10%">
<?php else: ?>
					<col width="50%">
<?php endif; ?>
					<thead>
						<tr>
							<th scope="col" class="keep"><div>Coordinates<br>
							<?php if (array_key_exists($k, $phenotypes) && count($phenotypes[$k]) && array_key_exists('gene', $phenotypes[$k][0]) && array_key_exists('amino_acid_change', $phenotypes[$k][0])): ?><i>Gene, amino acid change</i><?php else: ?><i>Function</i><?php endif; ?></div></th>
							<th scope="col" class="no-sort">Genotype<?php if (array_key_exists($k, $phenotypes) && count($phenotypes[$k]) && array_key_exists('trait_allele', $phenotypes[$k][0])): ?><br>
							<i>Trait-associated allele</i><?php endif; ?></th>
							<th scope="col" class="text"><div>Associated trait</div></th>
<?php if (array_key_exists($k, $phenotypes) && count($phenotypes[$k]) && array_key_exists('score', $phenotypes[$k][0])): ?>
							<th scope="col" class="sort-first-descending number"><div>Score</div></th>
<?php endif; ?>
						</tr>
					</thead>
					<tbody>
<?php
foreach ($phenotypes[$k] as $o):

// these variables are re-used; don't let previous values taint the output
unset($maf, $taf, $minor, $rare, $freq_unknown, $url);

// last-minute allele frequency calculations; for now, we give every
// variant the benefit of the doubt and use the lowest allele frequency
// for any population in which the subject claims to have ancestry
if (array_key_exists('maf', $o) && $o['maf'] != "N/A")
{
	$mafs = array_intersect_key(array_change_key_case(get_object_vars($o['maf']), CASE_LOWER),
	                            array_flip($phenotypes['ancestry']));
	if (count($mafs))
	{
		$freq_unknown = FALSE;
		$maf = min($mafs);
		$minor = $maf < 0.5;
		$rare = $maf < 0.05;
	}
	else
	{
		$freq_unknown = TRUE;
	}
}
else
{
	$freq_unknown = TRUE;
}

// trait allele frequencies are used over maf values, where available
if (array_key_exists('taf', $o) && $o['taf'] != "N/A")
{
	$tafs = array_intersect_key(array_change_key_case(get_object_vars($o['taf']), CASE_LOWER),
	                            array_flip($phenotypes['ancestry']));
	if (count($tafs))
	{
		$taf = min($tafs);
		$minor = $taf < 0.5;
		$rare = $taf < 0.05;
	}
}

// last-minute presentational corrections
// for this we need the chromosome name minus the "chr" prefix
$chromosome_without_prefix = str_replace('chr', '', $o['chromosome']);

// format genotypes: snpedia gives actual semicolon-separated genotypes;
// others give only a list of alleles--we treat these differently
if (strpos($o['genotype'], ';') !== FALSE)
{
	$o['genotype'] = str_replace(';', '/', $o['genotype']);
	if (!(is_numeric($chromosome_without_prefix) ||
	  ($chromosome_without_prefix == 'X' && $phenotypes['sex'] == 'female')))
	{
		$alleles = array_unique(explode('/', $o['genotype']));
		if (count($alleles) == 1)
			$o['genotype'] = $alleles[0];
	}
}
else if (is_numeric($chromosome_without_prefix) ||
  ($chromosome_without_prefix == 'X' && $phenotypes['sex'] == 'female'))
{
	if (strpos($o['genotype'], '/') === FALSE)
		$o['genotype'] = $o['genotype'].'/'.$o['genotype'];
}

$v = preg_split('/\t/', $o['variant']);

// format reference links
$references = explode(',', $o['reference']);
//TODO: do something about showing more than the first reference
//TODO: do something about LSDBs referenced in HGMD
$reference = explode(':', $references[0]);
switch ($reference[0])
{
case 'dbsnp':
	$article_id = $reference[1];
	$url = "http://www.snpedia.com/index.php/{$article_id}";
	break;
case 'omim':
	$allele_id = explode('.', $reference[1]);
	$article_id = $allele_id[0];
	$url = "http://www.ncbi.nlm.nih.gov/entrez/dispomim.cgi?id={$article_id}";
	break;
case 'pmid':
	$pmid = $reference[1];
	$url = "http://www.ncbi.nlm.nih.gov/pubmed/{$pmid}";
	break;
}
?>
						<tr class="<?php if ($freq_unknown): ?>unknown-frequency<?php elseif ($rare): ?>rare<?php elseif ($minor): ?>minor<?php else: ?>major<?php endif; ?>">
							<td><?php echo $o['chromosome'].':'.$o['coordinates']; ?><br>
							<?php if (array_key_exists('gene', $o) && array_key_exists('amino_acid_change', $o)): ?><i><?php echo $o['gene']; ?>, <?php echo $o['amino_acid_change']; ?></i><?php else: ?><i><span class="dim">(Not computed)</span></i><?php endif; ?></td>
							<td><?php echo $o['genotype']; ?><?php if (array_key_exists('trait_allele', $o)): ?><br>
							<i><?php echo $o['trait_allele']; ?></i><?php endif; ?></td>
							<td><a href="<?php echo $url; ?>"><?php echo $o['phenotype']; ?></a></td>
<?php if (array_key_exists('score', $o)): ?>
							<td scope="col" class="number"><?php echo $o['score']; ?></td>
<?php endif; ?>
						</tr>
<?php endforeach; ?>
<?php if (!array_key_exists($k, $phenotypes) || !count($phenotypes[$k])): ?>
						<tr>
							<td colspan="3"><span><br>No results available<br><br></span></td>
						</tr>
<?php endif; ?>
					</tbody>
				</table>
			</div>
<?php
endforeach;
?>
			</div>
		</div>
	</div></div>
	<div id="foot"><div>
		<div id="copyright">
			<p>
				<span>Copyright &copy; MMIX President and Fellows of Harvard College<br>[{elapsed_time} s]</span>
			</p>
		</div>
	</div></div>
</body>
</html>