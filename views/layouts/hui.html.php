<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2013, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
?>
<!doctype html>
<html>
<head>
	<?php echo $this->html->charset();?>
	<meta name="description" content="Huement User Interface (hui)">
	<meta name="author" content="Derek Scott (@huement)">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<!-- Mobile Related Stuff -->
	<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
	<title><?php echo $this->title(); ?></title>
	
	<!-- iPhone -->
	<link href="http://huement.s3.amazonaws.com/imgs/57.png" sizes="57x57" rel="apple-touch-icon">
	<!-- iPad -->
	<link href="http://huement.s3.amazonaws.com/imgs/72.png" sizes="72x72" rel="apple-touch-icon">
	<!-- iPhone (Retina) -->
	<link href="http://huement.s3.amazonaws.com/imgs/114.png" sizes="114x114" rel="apple-touch-icon">
	<!-- iPad (Retina) -->
	<link href="http://huement.s3.amazonaws.com/imgs/144.png" sizes="144x144" rel="apple-touch-icon">

	<!-- Old Internet Explorer -->
	<!--[if lt IE 9]>
	<link rel="stylesheet" href="http://huementui.s3.amazonaws.com/css/ie-min.css" type="text/css" media="screen" />
	<![endif]-->
		
	<?php echo $this->html->style(array('/hui/dist/hui-light.css')); ?>
	<?php echo $this->styles(); ?>
	<?php echo $this->html->link('Icon', null, array('type' => 'icon')); ?>
	<script type="text/javascript" src="http://hui.huement.com/libs/modernizr/modernizr-min.js"></script>
</head>
<body class="lithified">
	<div class="container">
		<div class="row limit">
			<div class="twelvecol last">
				<div class="masthead">
					<a href="http://lithify.me/docs/manual/quickstart" class="button right" style="float:right"><span class="label">Quickstart</span></a>
					<a href="http://lithify.me/docs/manual" class="button middle" style="float:right"><span class="label">Manual</span></a>
					<a href="http://lithify.me/docs/lithium" class="button middle" style="float:right"><span class="label">API</span></a>
					<a href="http://lithify.me/" class="button left" style="float:right"><span class="label">More</span></a>
			
					<a href="http://lithify.me/"><h3>&#10177;</h3></a>
					<div class="push" style="height:10px;"></div>
				</div>
				<hr>
			
			
				<div class="content">
					<?php echo $this->content(); ?>
				</div>

				<hr>
			
				<div class="footer">
					<p>&copy; <img src="http://huement.s3.amazonaws.com/imgs/huement-dark.png" style="width:200px;height:48px;margin:0px 10px -15px 10px;" alt="huement.com"/> 2013</p>
				</div>
			</div>
		</div>
	</div>
	
	<!-- CDN jQuery. fall back to local if necessary -->
	<script type="text/javascript" src="http://huementui.s3.amazonaws.com/cdn/jquery-1.8.3.min.js"></script>
	<script type="text/javascript">window.jQuery || document.write('<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"><\/script>')</script>
	<script type="text/javascript" src="http://huementui.s3.amazonaws.com/cdn/github/dist/hui-0.2.4.js"></script>
	
	<?php echo $this->scripts(); ?>
	
</body>
</html>