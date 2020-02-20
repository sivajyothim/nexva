<?php
//If the form is submitted
if(isset($_POST['submit'])) {

	//Check to make sure that the name field is not empty
	if(trim($_POST['contactname']) == '') {
		$hasError = true;
	} else {
		$name = trim($_POST['contactname']);
	}

	//Check to make sure that the subject field is not empty
	if(trim($_POST['subject']) == '') {
		$hasError = true;
	} else {
		$subject = trim($_POST['subject']);
	}

	//Check to make sure sure that a valid email address is submitted
	if(trim($_POST['email']) == '')  {
		$hasError = true;
	} else if (!eregi("^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$", trim($_POST['email']))) {
		$hasError = true;
	} else {
		$email = trim($_POST['email']);
	}

	//Check to make sure comments were entered
	if(trim($_POST['message']) == '') {
		$hasError = true;
	} else {
		if(function_exists('stripslashes')) {
			$comments = stripslashes(trim($_POST['message']));
		} else {
			$comments = trim($_POST['message']);
		}
	}

	//If there is no error, send the email
	if(!isset($hasError)) {
		$emailTo = 'test@chimpstudio.co.uk'; //Put your own email address here
		$body = "Name: $name \n\nEmail: $email \n\nSubject: $subject \n\nComments:\n $comments";
		$headers = 'From: Portfolio 3 <'.$emailTo.'>' . "\r\n" . 'Reply-To: ' . $email;

		mail($emailTo, $subject, $body, $headers);
		$emailSent = true;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Portfolio 3</title>
<!--// Stylesheet //-->
<link rel="stylesheet" href="css/style.css" type="text/css" />
<link rel="stylesheet" href="css/ddsmoothmenu.css" type="text/css" />
<link rel="stylesheet" href="css/slider.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/contentslider.css" type="text/css" />
<link rel="stylesheet" href="css/jquery.fancybox-1.3.1.css" type="text/css" media="screen" />
<!--// Java Script //-->
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/ddsmoothmenu.js"></script>
<script type="text/javascript" src="js/menu.js"></script>
<script type="text/javascript" src="js/cufon-yui.js"></script>
<script type="text/javascript" src="js/cufon.js"></script>
<script type="text/javascript" src="js/Comfortaa_400-Comfortaa_700.font.js"></script>
<script type="text/javascript" src="js/jquery.min14.js"></script>
<script type="text/javascript" src="js/jquery.easing.1.2.js"></script>
<script type="text/javascript" src="js/jquery.anythingslider.js"></script>
<script type="text/javascript" src="js/slider.js"></script>
<script type="text/javascript" src="js/contentslider.js"></script>
<script type="text/javascript" src="js/virtualpaginate.js"></script>
<script type="text/javascript" src="js/jquery.fancybox-1.3.1.js"></script>
<script type="text/javascript" src="js/lightbox.js"></script>
<script type="text/javascript" src="js/jquery.validate.pack.js"></script>
<script type="text/javascript" src="js/contact.js"></script>
</head>

<body>
<!-- Wrapper Section -->
<div id="wrappersec">
	<!-- Header Section -->
	<div id="masthead">
    	<div class="logo"><a href="index.html" name="top"><img src="images/logo.png" alt="" /></a></div>
        <div class="subscribe">
        	<ul>
            	<li>Subscribe:</li>
                <li><a href="#" class="first">Posts</a></li>
                <li><a href="#">Comments</a></li>
                <li><a href="#" class="last">E-mail</a></li>
            </ul>
        </div>
        <div class="clear"></div>
        <div class="navigation">
        	<div id="smoothmenu1" class="ddsmoothmenu">
                <ul>
                    <li><a href="index.html">Main Page</a>
                    	<ul>
                        	<li><a href="index1.html">Index1</a></li>
                        </ul>
                    </li>
                    <li><a href="static.html">About Us</a></li>
                    <li><a href="portfolio.html">Portfolio</a>
                        <ul>
                          <li><a href="#">Gallery Categories</a></li>
                          <li><a href="#">Gallery Items</a>
                              <ul>
                                  <li><a href="#">Gallery Categories</a></li>
                                  <li><a href="#">Gallery Items</a></li>
                                  <li><a href="#">Gallery Works</a></li>
                                  <li><a href="#">Slideshow Gallery</a></li>
                        	  </ul>
                          </li>
                          <li><a href="#">Gallery Works</a></li>
                          <li><a href="#">Slideshow Gallery</a></li>
                        </ul>
                	</li>
                    <li><a href="blog.html">Blog</a>
                        <ul>
                          <li><a href="blog_post.html">Blog Post</a></li>
                        </ul>
                	</li>
                    <li><a href="#">Colors</a>
                        <ul>
                          <li><a href="../blue/index.html">Blue</a></li>
                          <li><a href="../gray/index.html">Gray</a></li>
                          <li><a href="../green/index.html">green</a></li>
                          <li><a href="../red/index.html">Red</a></li>
                          <li><a href="../brown/index.html">Brown</a></li>
                        </ul>
                	</li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="sitemap.html">Site Map</a></li>
                </ul>
                <div class="clear"></div>
			</div>
        	<div class="search">
            	<ul>
                    <li><input name="" type="text" class="bar" /></li>
                    <li><input name="" type="image" src="images/go.gif" alt="" /></li>
            	</ul>
            </div>
        </div>
    </div>
    <!-- Content Section -->
    <div id="contentsec">
        <!-- Column 1 -->
        <div class="col1">
            <div class="leavecomments secbg">
            	<h2 class="colr">Contact Guilde</h2>
                <p>
                	Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed elit. Nulla sem risus, vestibulum in, volutpat eget, dapibus ac, lectus. Curabitur dolor sapien, hendrerit non, suscipit bibendum, auctor ac, arcu. Vestibulum dapibus. Sed pede lacus, pretium in, condimentum sit amet, mollis dapibus, magna. Ut bibendum dolor nec augue. Ut tempus luctus metus. Sed a velit. Pellentesque at libero elementum ante condimentum sollicitudin. Pellentesque lorem ipsum, semper quis, interdum et, sollicitudin eu, purus. Vivamus fringilla ipsum vel orci. Phasellus vitae massa at massa pulvinar pellentesque. Fusce tincidunt libero vitae odio. Donec malesuada diam nec mi. Integer hendrerit pulvinar ante. Donec eleifend, nisl eget aliquam congue, justo metus venenatis neque, vel tincidunt elit augue sit amet velit. Nulla facilisi. Aenean suscipit. 
                </p>
            </div>
            <div class="leavecomments secbg">
            	<h2 class="colr">Send a Message</h2>
                <div id="contact-wrapper">
	<?php if(isset($hasError)) { //If errors are found ?>
		<p class="error">Please check if you've filled all the fields with valid information. Thank you.</p>
	<?php } ?>

	<?php if(isset($emailSent) && $emailSent == true) { //If email is sent ?>
		<p><strong>Email Successfully Sent!</strong></p>
		<p>Thank you <strong><?php echo $name;?></strong> for using my contact form! Your email was successfully sent and I will be in touch with you soon.</p>
	<?php } ?>

	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="contactform">
		<ul class="forms">
		    <li class="txt"><label for="contactname">Name</label></li>
			<li class="inputfield"><input type="text" size="50" name="contactname" id="contactname" value="" class="required bar" /></li>
		</ul>

		<ul class="forms">
			<li class="txt"><label for="email">Email</label></li>
			<li class="inputfield"><input type="text" size="50" name="email" id="email" value="" class="required email bar" /></li>
		</ul>

		<ul class="forms">
			<li class="txt"><label for="subject">Subject</label></li>
			<li class="inputfield"><input type="text" size="50" name="subject" id="subject" value="" class="required bar" /></li>
		</ul>

		<ul class="forms">
			<li class="txt"><label for="message">Message</label></li>
			<li class="textfield"><textarea rows="5" cols="50" name="message" id="message" class="required"></textarea></li>
		</ul>
        <ul class="forms">
			<li class="txt"><input type="submit" value="" name="submit" class="submit" /></li>
		</ul>
	</form>
	</div>
            </div>
        </div>
        <!-- Column 2 -->
        <div class="col2">
        	<br />
        	<div class="small_featured secbg">
            	<h2 class="colr">Address Map</h2>
                <iframe class="map" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=london&amp;ie=UTF8&amp;hq=&amp;hnear=London,+United+Kingdom&amp;z=14&amp;ll=51.500152,-0.126236&amp;output=embed"></iframe>
            </div>
            <div class="services secbg">
            	<h2 class="colr">Our Services</h2>
                <p class="bold">
                	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin dictum tincidunt 
                </p>
                <p>
                	Lorem ipsum dolor sit amet, consectetur adipiscing
 elit. Proin dictum tincidunt dolor vitae posuere. 
Fusce rhoncus tincidunt sapien. Quisque malesua
da mi eu ipsum facilisis vehicula aliquet libero iac
ulis. liquam tempor placerat lectus. Curabitur sit 
 
                </p>
            </div>
            <div class="clear"></div>
            <div class="follow">
            	<div class="topcurve">&nbsp;</div>
                <div class="middlecurve">
                	<ul>
                        <li>
                            <a href="#" class="rss">SUBSCRIBE TI OUR FEEDS<br />Al Posts</a>
                        </li>
                        <li>
                            <a href="#" class="twitter">FOLLOW US ON TWITTER<br />@ Guilde</a>
                        </li>
                        <li>
                            <a href="#" class="facebook">BE OUR FRIEND ON FACEBOOK<br />Guilde Communication</a>
                        </li>
                    </ul>
                    <div class="clear"></div>
                </div>
                <div class="bottomcurve">&nbsp;</div>
            </div>
            <div class="clear">
            	<div class="advertise">
        	<!-- Banner Script Start -->
        	<div class="anythingSlider adcont">
              <div class="wrapper">
                <ul>
                  <li>
                  		<a href="#"><img src="images/ad_banner1.gif" alt="" /></a>
                  </li>
                  <li>
                  		<a href="#"><img src="images/ad_banner1.gif" alt="" /></a>
                  </li>
                  <li>
                  		<a href="#"><img src="images/ad_banner1.gif" alt="" /></a>
                  </li>
                  <li>
                  		<a href="#"><img src="images/ad_banner1.gif" alt="" /></a>
                  </li>
                </ul>        
              </div>
        	</div>
            <!-- Banner Script End -->
        </div>
            </div>
        </div>
    </div>
</div>
<div class="clear"></div>
<!-- Partners Section -->
<div id="partners">
	<div id="innerpartners">
    	<h4>Our Satisfied clients</h4>
        <ul>
        	<li><a href="$"><img src="images/logo1.gif" alt="" /></a></li>
            <li><a href="$"><img src="images/logo2.gif" alt="" /></a></li>
            <li><a href="$"><img src="images/logo3.gif" alt="" /></a></li>
            <li><a href="$"><img src="images/logo4.gif" alt="" /></a></li>
            <li><a href="$"><img src="images/logo5.gif" alt="" /></a></li>
            <li><a href="$"><img src="images/logo6.gif" alt="" /></a></li>
        </ul>
        <div class="clear"></div>
    </div>
</div>
<div class="clear"></div>
<!-- Blog Tabs Section -->
<div id="blogtabs">
	<div id="innertabs">
    	<div class="section">
        	<h6 class="black">Follow us on Twitter</h6>
        	<ul class="twittersec">
            	<li>
                	<a href="#">Lorem ipsum dolor sit amet, consectetur adipiscing elit. <span class="colr">about 20min ago</span></a>
                </li>
                <li>
                	<a href="#">Lorem ipsum dolor sit amet, consectetur adipiscing elit. <span class="colr">about 20min ago</span></a>
                </li>
                <li>
                	<a href="#">Lorem ipsum dolor sit amet, consectetur adipiscing elit. <span class="colr">about 20min ago</span></a>
                </li>
                <li>
                	<a href="#">Lorem ipsum dolor sit amet, consectetur adipiscing elit. <span class="colr">about 20min ago</span></a>
                </li>
            </ul>
        </div>
        <div class="section">
        	<h6 class="black">We on Flicker</h6>
        	<ul class="flickr">
            	<li><a href="#"><img src="images/flickr1.gif" alt="" /></a></li>
                <li><a href="#"><img src="images/flickr2.gif" alt="" /></a></li>
                <li class="last"><a href="#"><img src="images/flickr3.gif" alt="" /></a></li>
                <li><a href="#"><img src="images/flickr4.gif" alt="" /></a></li>
                <li><a href="#"><img src="images/flickr5.gif" alt="" /></a></li>
                <li class="last"><a href="#"><img src="images/flickr6.gif" alt="" /></a></li>
            </ul>
            <a href="#" class="flickrlink">Find Clean Design on flickr</a>
        </div>
        <div class="section">
        	<h6 class="black">Popular Posts</h6>
        	<ul class="poppost">
            	<li><a href="blog_post.html">Optimised Layout</a></li>
                <li><a href="blog_post.html">Extensions Integration</a></li>
                <li><a href="blog_post.html">Infusion of Style</a></li>
                <li><a href="blog_post.html">Optimised Layout</a></li>
                <li><a href="blog_post.html">Extensions Integration</a></li>
                <li><a href="blog_post.html">Infusion of Style</a></li>
                <li><a href="blog_post.html">Infusion of Style</a></li>
                <li><a href="blog_post.html">Optimised Layout</a></li>
            </ul>
        </div>
        <div class="section last">
        	<h6 class="black">Contact Information</h6>
        	<ul class="contact_info">
            	<li class="email">Email<br /><a href="mailto:yourmail@yourmail.com">yourmail@yourmail.com</a></li>
                <li class="phone">Phone<br />123-456-789-1</li>
                <li class="adres">Address<br />Abcd2Net, Inc. 123 N. Hancock St</li>
                <li class="twit_botm">Twitter<br /><a href="#">http://twitter.com/youname</a></li>
                <li class="fb_botm">Facebok<br /><a href="#">http://facebook.com/yourname</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="clear"></div>
<!-- Copyrights Section -->
<div id="copyrights">
	<div id="innerrights">
    	<div class="text">
        	<p>© 2010 guilde communications, All Rights Reserved</p>
            <div class="clear"></div>
            <p>Designed By: </p>
            <a href="#"><img src="images/designdby.gif" alt="" /></a>
        </div>
        <div class="top">
        	<a href="#top" class="backtotop">Back to Top</a>
        </div>
    </div>
</div>


</body>
</html>
