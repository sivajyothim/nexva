<?php // -*- mode: sgml-html; mmm-classes: html-php -*-

error_reporting(0);
include("testGoogleLog.php");
include("testGoogleXMLParser.php");
include("testGoogleCart.php");

$title = 'PhpUnit test run For PHP Sample Code';
?>
  <html>
    <head>
      <title><?php echo $title; ?></title>
      <STYLE TYPE="text/css">
	<?php
	include ("stylesheet.css");
	?>
      </STYLE>
    </head>
    <body>
      <h1><?php echo $title; ?></h1>
      <p>
	This page runs all the phpUnit self-tests for the different classes of the
  PHP Sample Code.
      </p>
      <p>
  Use this TestCases to test the Library if you have changed any part of the 
  code.
      </p>
      <p>
	<?php
	if (isset($only)) {
	 $suite = new TestSuite($only);
	}

	$result = new PrettyTestResult;
	$suite->run($result);
	$result->report();
	?>
    </body>
  </html>
