<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.7 Plugin: WP-Print 2.50										|
|	Copyright (c) 2008 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://lesterchan.net															|
|																							|
|	File Information:																	|
|	- Printer Friendly Post/Page Template										|
|	- wp-content/plugins/wp-print/print-posts.php							|
|																							|
+----------------------------------------------------------------+
*/
?>

<?php global $text_direction; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<title><?php bloginfo('name'); ?> <?php wp_title(); ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="Robots" content="noindex, nofollow" />
	<?php if(@file_exists(TEMPLATEPATH.'/print-css.css')): ?>
		<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css.css" type="text/css" media="screen, print" />
	<?php else: ?>
		<link rel="stylesheet" href="<?php echo plugins_url('wp-print/print-css.css'); ?>" type="text/css" media="screen, print" />
	<?php endif; ?>
	<?php if('rtl' == $text_direction): ?>
		<?php if(@file_exists(TEMPLATEPATH.'/print-css-rtl.css')): ?>
			<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css-rtl.css" type="text/css" media="screen, print" />
		<?php else: ?>
			<link rel="stylesheet" href="<?php echo plugins_url('wp-print/print-css-rtl.css'); ?>" type="text/css" media="screen, print" />
		<?php endif; ?>
	<?php endif; ?>
</head>
<body>
<p style="text-align: center;"><strong>- <?php bloginfo('name'); ?> - <span dir="ltr"><?php bloginfo('url')?></span> -</strong></p>
<div class="Center">
	<div id="Outline">
		<?php if (have_posts()): ?>
			<?php while (have_posts()): the_post(); ?>
					<p id="BlogTitle"><?php the_title(); ?></p>
					<?php $custom =  get_post_custom(get_the_ID()); ?>
					<div style="width: 420px; float: right; line-height: 26px; ">
				<p style="margin-top: 25px; font-size: 14px; width: 200px; float: left;">
				<span class="listing-label">Title:</span> <?php echo $custom['_title'][0];?><br />
				<span class="listing-label">Bedrooms:</span> <?php echo $custom['_bedrooms'][0];?><br />
				<span class="listing-label">Bathrooms:</span> <?php echo $custom['_bathrooms'][0]; ?><br />
				<span class="listing-label">Floor Space:</span> <?php echo $custom['_living_area'][0]; ?> SqFt.</p>
				
				<p style="margin-top: 25px; font-size: 14px; width: 200px; float: left; margin-left: 20px;">
				<span class="listing-label">Built In:</span> <?php echo $custom['_built_in'][0];?><br />
				<span class="listing-label">Storeys:</span> <?php echo $custom['_storeys'][0];?><br />
				<span class="listing-label">Land Size:</span> <?php echo $custom['_land_size'][0]; ?><br />
				<span class="listing-label">Title:</span> <?php echo $custom['_title'][0]; ?> SqFt.</p></div>

					<div id="BlogContent"><?php print_content(); ?></div>
			<?php endwhile; ?>
			<hr class="Divider" style="text-align: center;" />
			<?php if(print_can('comments')): ?>
				<?php comments_template(); ?>
			<?php endif; ?>
			<p><?php _e('Article printed from', 'wp-print'); ?> <?php bloginfo('name'); ?>: <strong dir="ltr"><?php bloginfo('url'); ?></strong></p>
			<p><?php _e('URL to article', 'wp-print'); ?>: <strong dir="ltr"><?php the_permalink(); ?></strong></p>
			<?php if(print_can('links')): ?>
				<p><?php print_links(); ?></p>
			<?php endif; ?>
			<p style="text-align: <?php echo ('rtl' == $text_direction) ? 'left' : 'right'; ?>;" id="print-link"><?php _e('Click', 'wp-print'); ?> <a href="#Print" onclick="window.print(); return false;" title="<?php _e('Click here to print.', 'wp-print'); ?>"><?php _e('here', 'wp-print'); ?></a> <?php _e('to print.', 'wp-print'); ?></p>
		<?php else: ?>
				<p><?php _e('No posts matched your criteria.', 'wp-print'); ?></p>
		<?php endif; ?>
	</div>
</div>
<p style="text-align: center;"><?php echo stripslashes($print_options['disclaimer']); ?></p>
</body>
</html>