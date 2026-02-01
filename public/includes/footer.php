
<!-- START FOOTER -->
<footer class="bg_gray">
	<div class="footer_top small_pt pb_20">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-12 col-sm-12">
                	<div class="widget">
                        <div class="footer_logo">
                            <a href="#"><img src="assets/images/logo_dark.png" alt="logo"></a>
                        </div>
                       
                        <ul class="contact_info">
                            <?php 
                            // Use AppContext contact map if available
                            if (isset($app) && $app instanceof AppContext) {
                                $footerContactMap = $app->contactMap;
                            } else {
                                // Fallback: load contact details locally
                                $contactDetails = $db->query("SELECT * FROM contact_details WHERE status = 'active' ORDER BY sort_order ASC")->fetchAll();
                                $footerContactMap = [];
                                foreach ($contactDetails as $contact) {
                                    $footerContactMap[$contact['type']][] = $contact;
                                }
                            }

                            // Display address
                            if (!empty($footerContactMap['address'])): 
                                foreach ($footerContactMap['address'] as $address):
                            ?>
                            <li>
                                <i class="ti-location-pin"></i>
                                <p><?php echo htmlspecialchars($address['value']); ?></p>
                            </li>
                            <?php 
                                endforeach;
                            endif;
                            
                            // Display email
                            if (!empty($footerContactMap['email'])): 
                                foreach ($footerContactMap['email'] as $email):
                            ?>
                            <li>
                                <i class="ti-email"></i>
                                <a href="mailto:<?php echo htmlspecialchars($email['value']); ?>"><?php echo htmlspecialchars($email['value']); ?></a>
                            </li>
                            <?php 
                                endforeach;
                            endif;
                            
                            // Display phone
                            if (!empty($footerContactMap['phone'])): 
                                foreach ($footerContactMap['phone'] as $phone):
                            ?>
                            <li>
                                <i class="ti-mobile"></i>
                                <p><?php echo htmlspecialchars($phone['value']); ?></p>
                            </li>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </ul>
                    </div>
        		</div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                	<div class="widget">
                        <h6 class="widget_title">Useful Links</h6>
                        <ul class="widget_links">
                            <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/about-us">About Us</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/certifications">Certifications</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/testimonials">Testimonials</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/contact-us">Contact Us</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/enquiry">Send Enquiry</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                	<div class="widget">
                        <h6 class="widget_title">Brands</h6>
                        <ul class="widget_links">
                            <?php 
                            // Use AppContext brands if available
                            $footerBrands = [];
                            if (isset($app) && $app instanceof AppContext) {
                                $footerBrands = $app->allBrands;
                            } elseif (isset($allBrands)) {
                                $footerBrands = $allBrands;
                            } else {
                                $footerBrands = $db->query("SELECT * FROM brands WHERE status = 'active' ORDER BY sort_order ASC, name ASC")->fetchAll();
                            }
                            ?>
                            <?php foreach ($footerBrands as $footerBrand): ?>
                            <li><a href="<?php echo SITE_URL; ?>/<?php echo $footerBrand['slug']; ?>"><?php echo htmlspecialchars($footerBrand['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                	<div class="widget">
                    	<h6 class="widget_title">Our Location</h6>
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3821.3921688509013!2d74.24648167514971!3d16.70727308406853!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bc100443d162aa9%3A0x51d79135613bddf!2sSpark%20Systems%20-%20Authorized%20Channel%20Partner%20For%20Bosch%20Industrial%2C%20Dealer%2C%20Supplier%20Of%20All%20Types%20Of%20Industrial%20Power%20Tools!5e0!3m2!1sen!2sin!4v1704264233915!5m2!1sen!2sin" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Spark Systems"></iframe>
                           
                    </div>
                	
                </div>
            </div>
        </div>
    </div>
  
    <div class="bottom_footer border-top-tran">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-center text-md-start mb-md-0">© 2026 All Rights Reserved by Spark Systems</p>
                </div>
                
            </div>
        </div>
    </div>
</footer>
<!-- END FOOTER -->

<a href="#" class="scrollup" style="display: none;"><i class="ion-ios-arrow-up"></i></a> 

<!-- Latest jQuery --> 
<script src="<?php echo SITE_URL; ?>/assets/js/jquery-3.7.0.min.js"></script> 
<!-- popper min js -->
<script src="<?php echo SITE_URL; ?>/assets/js/popper.min.js"></script>
<!-- Latest compiled and minified Bootstrap --> 
<script src="<?php echo SITE_URL; ?>/assets/bootstrap/js/bootstrap.min.js"></script> 
<!-- owl-carousel min js  --> 
<script src="<?php echo SITE_URL; ?>/assets/owlcarousel/js/owl.carousel.min.js"></script> 
<!-- magnific-popup min js  --> 
<script src="<?php echo SITE_URL; ?>/assets/js/magnific-popup.min.js"></script> 
<!-- waypoints min js  --> 
<script src="<?php echo SITE_URL; ?>/assets/js/waypoints.min.js"></script> 
<!-- parallax js  --> 
<script src="<?php echo SITE_URL; ?>/assets/js/parallax.js"></script> 
<!-- countdown js  --> 
<script src="<?php echo SITE_URL; ?>/assets/js/jquery.countdown.min.js"></script> 
<!-- imagesloaded js --> 
<script src="<?php echo SITE_URL; ?>/assets/js/imagesloaded.pkgd.min.js"></script>
<!-- isotope min js --> 
<script src="<?php echo SITE_URL; ?>/assets/js/isotope.min.js"></script>
<!-- jquery.dd.min js -->
<script src="<?php echo SITE_URL; ?>/assets/js/jquery.dd.min.js"></script>
<!-- slick js -->
<script src="<?php echo SITE_URL; ?>/assets/js/slick.min.js"></script>
<!-- elevatezoom js -->
<script src="<?php echo SITE_URL; ?>/assets/js/jquery.elevatezoom.js"></script>
<!-- scripts js --> 
<script src="<?php echo SITE_URL; ?>/assets/js/scripts.js"></script>

</body>
</html>