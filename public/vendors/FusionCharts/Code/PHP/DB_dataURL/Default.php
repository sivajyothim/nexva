<?php
//We've included ../Includes/FusionCharts.php, which contains functions
//to help us easily embed the charts.
include("../Includes/FusionCharts.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <TITLE>
	FusionCharts - dataURL and Database  Example
        </TITLE>
        <link href="../assets/ui/css/style.css" rel="stylesheet" type="text/css" />

        <?php
        //You need to include the following JS file, if you intend to embed the chart using JavaScript.
        //Embedding using JavaScripts avoids the "Click to Activate..." issue in Internet Explorer
        //When you make your own charts, make sure that the path to this JS file is correct. Else, you would get JavaScript errors.
        ?>
        <SCRIPT LANGUAGE="Javascript" SRC="../../FusionCharts/FusionCharts.js"></SCRIPT>

        <!--[if IE 6]>
        <script>
                <script type="text/javascript" src="../assets/ui/js/DD_belatedPNG_0.0.8a-min.js"></script>
          /* select the element name, css selector, background etc */
          DD_belatedPNG.fix('img');

          /* string argument can be any CSS selector */
        </script>
        <![endif]-->

        <style type="text/css">
            h2.headline {
                font: normal 110%/137.5% "Trebuchet MS", Arial, Helvetica, sans-serif;
                padding: 0;
                margin: 25px 0 25px 0;
                color: #7d7c8b;
                text-align: center;
            }
            p.small {
                font: normal 68.75%/150% Verdana, Geneva, sans-serif;
                color: #919191;
                padding: 0;
                margin: 0 auto;
                width: 664px;
                text-align: center;
            }
        </style>
        <?php
        //You need to include the following JS file, if you intend to embed the chart using JavaScript.
        //Embedding using JavaScripts avoids the "Click to Activate..." issue in Internet Explorer
        //When you make your own charts, make sure that the path to this JS file is correct. Else, you
        //would get JavaScript errors.
        ?>
        <SCRIPT LANGUAGE="Javascript" SRC="../../FusionCharts/FusionCharts.js"></SCRIPT>

    </head>
    <BODY>

        <div id="wrapper">

            <div id="header">
                <div class="back-to-home"><a href="../index.html">Back to home</a></div>

                <div class="logo"><a class="imagelink"  href="index.html"><img src="../assets/ui/images/fusionchartsv3.2-logo.png" width="131" height="75" alt="FusionCharts v3.2 logo" /></a></div>
                <h1 class="brand-name">FusionCharts</h1>
                <h1 class="logo-text">FusionCharts dataURL and Database</h1>
            </div>

            <div class="content-area">
                <div id="content-area-inner-main">
                    <h2 class="headline">Click on any pie slice to clice it out right click to enable rotation mode.</h2>

                    <div class="gen-chart-render">

                        <CENTER>
                            <?php
                            //In this example, we show how to connect FusionCharts to a database
                            //using dataURL method. In our other examples, we've used dataXML method
                            //where the XML is generated in the same page as chart. Here, the XML data
                            //for the chart would be generated in PieData.php.

                            //For the sake of ease, we've used an MySQL databases containing two
                            //tables.

                            //To illustrate how to pass additional data as querystring to dataURL,
                            //we've added an animate property, which will be passed to PieData.php.
                            //PieData.php would handle this animate property and then generate the
                            //XML accordingly.

                            //Set DataURL with animation property to 1
                            //NOTE: It's necessary to encode the dataURL if you've added parameters to it
                            $strDataURL = "PieData.php?animate=1";

                            //Create the chart - Pie 3D Chart with dataURL as strDataURL
                            echo renderChart("../../FusionCharts/Pie3D.swf", $strDataURL, "", "FactorySum", 600, 300, false, false);
                            ?>
                        </CENTER>

                    </div>
                    <div class="clear"></div>
                    <p>&nbsp;</p>
                    <p class="small"> </p>

                    <div class="underline-dull"></div>
                </div>
            </div>

            <div id="footer">
                <ul>
                    <li><a href="../index.html"><span>&laquo; Back to list of examples</span></a></li>
                    <li class="pipe">|</li>
                    <li><a href="../NoChart.html"><span>Unable to see the chart above?</span></a></li>
                </ul>
            </div>
        </div>
    </BODY>
</HTML>
