<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package ultrabootstrap
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php wp_head(); ?>
</head>


<body <?php body_class(); ?>>
<?php $header_text_color = get_header_textcolor();?>

<header>	
<section class="logo-menu">
	<nav class="navbar navbar-default navbar-fixed-top">
		<div class="container">
					<!-- Brand and toggle get grouped for better mobile display -->
				    <div class="navbar-header">
				      	<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					        <span class="sr-only"><?php _e('Toggle navigation' , 'ultrabootstrap' ); ?></span>
					        <span class="icon-bar"></span>
					        <span class="icon-bar"></span>
					        <span class="icon-bar"></span>
				      	</button>
				      	<div class="logo-tag">
				      		
				      			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php if ( has_custom_logo()): the_custom_logo(); else: ?>
				      			<h1 class="site-title" style="color:<?php echo "#". $header_text_color;?>"><?php echo bloginfo( 'name' ); ?></h1>
				      			<h2 class="site-description" style="color:<?php echo "#". $header_text_color;?>"><?php bloginfo('description'); ?></h2><?php endif; ?></a>                     
      						
      					</div>
				    </div>

					<!-- Collect the nav links, forms, and other content for toggling -->
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<form  class="navbar-form navbar-right" role="search">
							<ul class="nav pull-right">
								<div class="main-search">
									<button class="btn btn-search" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
									  <i class="fa fa-search"></i>
									</button>
									<div class="search-box collapse" id="collapseExample">
											<div class="well search-well">
										    <form method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                          						<input type="text" class="form-control" placeholder="Search a keyword" value="<?php echo get_search_query(); ?>" name="s">
                          					</form>
											</div>
									</div>
								</div>
							</ul>
						</form>
  							
						<?php
				            wp_nav_menu( array(
				                'menu'              => 'primary',
				                'theme_location'    => 'primary',
				                'depth'             => 8,
				                'container'         => 'div',
				                'menu_class'        => 'nav navbar-nav navbar-right',
				                'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
				                'walker'            => new wp_bootstrap_navwalker())
				            );
				        ?>
				    </div> <!-- /.end of collaspe navbar-collaspe -->
	</div> <!-- /.end of container -->
	</nav>
</section> <!-- /.end of section -->
</header>