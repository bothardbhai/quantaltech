<?php
/**
 * Source: page-404.html (theme original)
 * Stage 1: structural conversion only — content port happens in Stage 3.
 */
$page_title  = $page_title  ?? 'Page Not Found — Quantal AI';
$active_page = $active_page ?? '';
?>
<!-- 404 Section -->
			<section class="">
				<div class="auto-container pt-70 pb-100">
					<div class="row">
						<div class="col-xl-12">
							<div class="error-page__inner theme-btn-main">
								<div class="error-page__title-box">
									<img src="<?= url('/assets/images/resource/404.png') ?>" alt="">
									<h3 class="error-page__sub-title mt-50">Page not found!</h3>
								</div>
								<p class="error-page__text">Sorry we can't find that page! The page you are looking <br> for was never existed.</p>
								<form class="error-page__form" role="search" aria-label="Search Form">
									<div class="error-page__form-input">
										<label for="error-search" class="visually-hidden">Search</label>
										<input type="search" id="error-search" name="s" placeholder="Search here" required aria-required="true">
										<button type="submit" aria-label="Submit Search">
											<i class="lnr lnr-icon-magnifier" aria-hidden="true"></i>
										</button>
									</div>
								</form>
								<a href="<?= attr(BASE_URL) ?>" class="theme-btn btn-style-one transform"><span class="btn-title">Back to Home</span></a>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!--End 404 Section -->
	    </div>
  </div>

</div>
<!-- End Page Wrapper -->

</div>
<!-- End Page Wrapper -->
