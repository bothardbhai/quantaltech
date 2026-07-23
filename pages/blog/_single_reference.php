<?php
/**
 * Source: news-details.html (theme original)
 * Stage 1: structural conversion only - content port happens in Stage 3.
 */
$page_title  = $page_title  ?? 'Blog Post - Quantal AI';
$active_page = $active_page ?? 'blog';
?>

<!-- Start main-content -->
<section class="page-title" style="background-image: url(/assets/images/background/page-title.jpg);">
    <div class="auto-container">
        <div class="title-outer text-center">
            <h1 class="title">News Details</h1>
            <ul class="page-breadcrumb">
                <li><a href="/">Home</a></li>
                <li>News</li>
            </ul>
        </div>
    </div>
</section>
<!-- end main-content --> 
<!--Blog Details Start-->
<section class="blog-details pt-120 pb-120">
	<div class="container">
		<div class="row">
			<div class="col-xl-8 col-lg-7">
				<div class="blog-details__left">
					<div class="blog-details__img">
						<img src="/assets/images/resource/news-details.jpg" alt="">
						<div class="blog-details__date">
							<span class="day">28</span>
							<span class="month">Aug</span>
						</div>
					</div>
					<div class="blog-details__content">
						<ul class="list-unstyled blog-details__meta">
							<li><a href="/blog"><i class="fas fa-user-circle"></i> Admin</a> </li>
							<li><a href="/blog"><i class="fas fa-comments"></i> 02
									Comments</a>
							</li>
						</ul>
						<h3 class="blog-details__title">Your business absolutely needs a updated daily</h3>
						<p class="blog-details__text-2">Mauris non dignissim purus, ac commodo diam. Donec sit amet lacinia nulla. Aliquam quis purus in justo pulvinar tempor. Aliquam tellus nulla, sollicitudin at euismod nec, feugiat at nisi. Quisque vitae odio nec lacus interdum tempus. Phasellus a rhoncus erat. Vivamus vel eros vitae est aliquet </p>
						<p class="blog-details__text-2">Mauris non dignissim purus, ac commodo diam. Donec sit amet lacinia nulla. Aliquam quis purus in justo pulvinar tempor. Aliquam tellus nulla, sollicitudin at euismod nec, feugiat at nisi. Quisque vitae odio nec lacus interdum tempus. Phasellus a rhoncus erat. Vivamus vel eros vitae est aliquet </p>
						<p class="blog-details__text-2">Mauris non dignissim purus, ac commodo diam. Donec sit amet lacinia nulla. Aliquam quis purus in justo pulvinar tempor. Aliquam tellus nulla, sollicitudin at euismod nec, feugiat at nisi. Quisque vitae odio nec lacus interdum tempus. Phasellus a rhoncus erat. Vivamus vel eros vitae est aliquet </p>
					</div>
					<div class="blog-details__bottom">
						<p class="blog-details__tags"> <span>Tags</span> <a href="/blog">Business</a> <a href="/blog">Agency</a> </p>
						<div class="blog-details__social-list"> <a href="/blog"><i class="fa fa-x"></i></a> <a href="/blog"><i class="fab fa-facebook"></i></a> <a href="/blog"><i class="fab fa-pinterest-p"></i></a> <a href="/blog"><i class="fab fa-instagram"></i></a> </div>
					</div>
					<div class="nav-links">
						<div class="prev">
							<a href="/blog" rel="prev">Bring to the table win-win survival strategies</a>
						</div>
						<div class="next">
							<a href="/blog" rel="next">How to lead a healthy &amp; well-balanced life</a>
						</div>
					</div>
					<div class="comment-one">
						<h3 class="comment-one__title">2 Comments</h3>
						<div class="comment-one__single">
							<div class="comment-one__image"> <img src="/assets/images/resource/testi1-1.jpg" alt=""> </div>
							<div class="comment-one__content">
								<h3>Kevin Martin</h3>
								<p>Mauris non dignissim purus, ac commodo diam. Donec sit amet lacinia nulla.
									Aliquam quis purus in justo pulvinar tempor. Aliquam tellus nulla,
									sollicitudin at euismod.
								</p>
								<a href="/blog" class="theme-btn btn-style-one comment-one__btn"><span class="btn-title">Reply</span></a>
							</div>
						</div>
						<div class="comment-one__single">
							<div class="comment-one__image"> <img src="/assets/images/resource/testi1-1.jpg" alt=""> </div>
							<div class="comment-one__content">
								<h3>Sarah Albert</h3>
								<p>Mauris non dignissim purus, ac commodo diam. Donec sit amet lacinia nulla.
									Aliquam quis purus in justo pulvinar tempor. Aliquam tellus nulla,
									sollicitudin at euismod.
								</p>
								<a href="/blog" class="theme-btn btn-style-one comment-one__btn"><span class="btn-title">Reply</span></a>
							</div>
						</div>
						<div class="comment-form">
							<h3 class="comment-form__title mb-3">Leave a Comment</h3>
							<form id="contact_form" name="contact_form" action="#" method="post">
								<div class="row">
									<div class="col-sm-6">
										<div class="mb-3">
											<input name="form_name" class="form-control" type="text" placeholder="Enter Name">
										</div>
									</div>
									<div class="col-sm-6">
										<div class="mb-3">
											<input name="form_email" class="form-control required email" type="email" placeholder="Enter Email">
										</div>
									</div>
								</div>
								<div class="mb-3">
									<textarea name="form_message" class="form-control required" rows="5" placeholder="Enter Message"></textarea>
								</div>
								<div class="mb-3 theme-btn-main">
									<input name="form_botcheck" class="form-control" type="hidden" value="">
									<button type="submit" class="theme-btn btn-style-one transform" data-loading-text="Please wait..."><span class="btn-title">Submit Comment</span></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-lg-5">
				<div class="sidebar">
					<div class="sidebar__single sidebar__search">
						<form action="#" class="sidebar__search-form">
							<input type="search" placeholder="Search here">
							<button type="submit"><i class="lnr-icon-search"></i></button>
						</form>
					</div>
					<div class="sidebar__single sidebar__post">
						<h3 class="sidebar__title">Latest Posts</h3>
						<ul class="sidebar__post-list list-unstyled">
							<li>
								<div class="sidebar__post-image"> <img src="/assets/images/resource/news-1.jpg" alt=""> </div>
								<div class="sidebar__post-content">
									<h3> <span class="sidebar__post-content-meta"><i class="fas fa-user-circle"></i>Admin</span> <a href="/blog">The Future of Smart Homes in Construction</a>
									</h3>
								</div>
							</li>
							<li>
								<div class="sidebar__post-image"> <img src="/assets/images/resource/news-2.jpg" alt=""> </div>
								<div class="sidebar__post-content">
									<h3> <span class="sidebar__post-content-meta"><i class="fas fa-user-circle"></i>Admin</span> <a href="/blog">Tensive quality vectors life through strategies</a> </h3>
								</div>
							</li>
							<li>
								<div class="sidebar__post-image"> <img src="/assets/images/resource/news-3.jpg" alt=""> </div>
								<div class="sidebar__post-content">
									<h3> <span class="sidebar__post-content-meta"><i class="fas fa-user-circle"></i>Admin</span> <a href="/blog">How to stay motivated until a project is finished</a> </h3>
								</div>
							</li>
						</ul>
					</div>
					<div class="sidebar__single sidebar__category">
						<h3 class="sidebar__title">Categories</h3>
						<ul class="sidebar__category-list list-unstyled">
							<li><a href="/blog">Architecture Design<span class="icon-right-arrow"></span></a> </li>
							<li class="active"><a href="/blog">Building Construction<span class="icon-right-arrow"></span></a></li>
							<li><a href="/blog">Building Renovation<span class="icon-right-arrow"></span></a> </li>
							<li><a href="/blog">Civil Engineering<span class="icon-right-arrow"></span></a> </li>
							<li><a href="/blog">Construction<span class="icon-right-arrow"></span></a> </li>
							<li><a href="/blog">Web Development<span class="icon-right-arrow"></span></a> </li>
						</ul>
					</div>
					<div class="sidebar__single sidebar__tags">
						<h3 class="sidebar__title">Tags</h3>
						<div class="sidebar__tags-list"> <a href="#">Consulting</a> <a href="#">Agency</a> <a href="#">Business</a> <a href="#">Digital</a> <a href="#">Experience</a> <a href="#">Technology</a> </div>
					</div>
					<div class="sidebar__single sidebar__comments">
						<h3 class="sidebar__title">Recent Comments</h3>
						<ul class="sidebar__comments-list list-unstyled">
							<li>
								<div class="sidebar__comments-icon"> <i class="fas fa-comments"></i> </div>
								<div class="sidebar__comments-text-box">
									<p>A wordpress commenter on <br>
										launch new mobile app
									</p>
								</div>
							</li>
							<li>
								<div class="sidebar__comments-icon"> <i class="fas fa-comments"></i> </div>
								<div class="sidebar__comments-text-box">
									<p> <span>John Doe</span> on template:</p>
									<h5>comments</h5>
								</div>
							</li>
							<li>
								<div class="sidebar__comments-icon"> <i class="fas fa-comments"></i> </div>
								<div class="sidebar__comments-text-box">
									<p>A wordpress commenter on <br>
										launch new mobile app
									</p>
								</div>
							</li>
							<li>
								<div class="sidebar__comments-icon"> <i class="fas fa-comments"></i> </div>
								<div class="sidebar__comments-text-box">
									<p> <span>John Doe</span> on template:</p>
									<h5>comments</h5>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!--Blog Details End-->

 <!-- Main Footer -->
