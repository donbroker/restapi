<?php
/**
 * Template Name: Adverts Home Page 
 *
 */
get_header(); ?>

<div class="container" style="margin-top:10px;">
	<div class="row">
		<div class="col-lg-8 col-xs-12">
			<form class="navbar-form" role="search">
				<div class="input-group">
					<input type="text" class="form-control" placeholder="请输入关键字" name="srch-term" id="srch-term">
					<div class="input-group-btn">
						<button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
					</div>
				</div>
			</form>
		</div>
		<div class="col-lg-2 col-xs-6">
			<button type="button" class="btn btn-default">发布信息</button>
		</div>
		<div class="col-lg-2 col-xs-6">
			<button type="button" class="btn btn-default">管理中心</button>
		</div>
	</div>
	<div class="container">
		<div class="col-lg-10">
			<div class="row">
				<h3>最新上线</h3>
			</div>
			<div class="row">
				<div class="col-lg-6 col-sm-12">
					<!--top carousel-->
					<div id="advertismentPage-top-carousel" class="carousel slide" data-ride="carousel">
						<!--indicators dot nav-->
						<ol class="carousel-indicators">
							<li data-target="#advertismentPage-top-carousel" data-slide-to="0" class="active"></li>
							<li data-target="#advertismentPage-top-carousel" data-slide-to="1"></li>
							<li data-target="#advertismentPage-top-carousel" data-slide-to="2"></li>
						</ol>
						<!--wrapper for slides-->
						<div class="carousel-inner" role="listbox">
							<?php
								$count = 1; 
								$query1 = new WP_Query( 
									array(
										'category_name'  =>'advert',
										'post__in'       => get_option( 'sticky_posts' ),
										'posts_per_page' => 3
									) 
								);
								if ( $query1->have_posts() ):
								while ( $query1->have_posts() ):
									$query1->the_post();
							?>
								<div class="item <?php echo $count===1?"active":""; ?>">
									<img src="<?php echo wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'ad-home-slider' )[0]; ?>">
									<div class="carousel-caption">
										<h5><?php the_title() ?></h5>
									</div>
								</div>
							<?php
								$count++;
								endwhile;
								endif;
								wp_reset_postdata(); 
							?>
							<!--slide one-->
						</div><!--end wrapper for slides-->
							<!-- control button or next prev button-->
						<a class="left carousel-control" href="#advertismentPage-top-carousel" role="button" data-slide="prev">
							<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
							<span class="sr-only">Previous</span>
						</a>
						<a class="right carousel-control" href="#advertismentPage-top-carousel" role="button" data-slide="next">
							<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
							<span class="sr-only">Next</span>
						</a>
					</div><!--end carousel-->
				</div><!--end carousel slide-->
				<div class="col-lg-6 col-sm-12">
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#latest-post">最新帖子</a></li>
						<li><a data-toggle="tab" href="#lastest-reply">最新回复</a></li>
						<li><a data-toggle="tab" href="#picked-post">精华主题</a></li>
					</ul>
					<div class="tab-content">
						<div id="latest-post" class="tab-pane fade in active">
							<ul>
							<?php
								$query2 = new WP_Query( 
									array(
										'posts_per_page' => 10
									) 
								);
								if ( $query2->have_posts() ):
								while ( $query2->have_posts() ):
									$query2->the_post();
							?>
								<li><?php the_title() ?></li>
							<?php
								$count++;
								endwhile;
								endif;
								wp_reset_postdata(); 
							?>
							</ul>
						</div>
						<div id="lastest-reply" class="tab-pane fade">
							<ul>
							<?php
								$query3 = new WP_Query( 
									array(
										'posts_per_page' => 10
									) 
								);
								if ( $query3->have_posts() ):
								while ( $query3->have_posts() ):
									$query3->the_post();
							?>
								<li><?php the_title() ?></li>
							<?php
								$count++;
								endwhile;
								endif;
								wp_reset_postdata(); 
							?>
							</ul>
						</div>
						<div id="picked-post" class="tab-pane fade">
							<ul>
							<?php
								$query4 = new WP_Query( 
									array(
										'posts_per_page' => 10
									) 
								);
								if ( $query4->have_posts() ):
								while ( $query4->have_posts() ):
									$query4->the_post();
							?>
								<li><?php the_title() ?></li>
							<?php
								$count++;
								endwhile;
								endif;
								wp_reset_postdata(); 
							?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-2 col-sm-12">
			<div class="row" style="margin-bottom: 5px;" >
				<img src="http://54.166.40.15/wp-content/themes/jjtest/images/advertisment/paid-ads.png" class="img-responsive" alt="ads" height="190" width="170">
			</div>
			<div class="row" >
				<img src="http://54.166.40.15/wp-content/themes/jjtest/images/advertisment/paid-ads.png" class="img-responsive" alt="ads" height="190" width="170">
			</div>
		</div>
	</div>
</div>
<?php get_footer();?>