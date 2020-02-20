<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OembedController
 *
 * @author Administrator
 */


class Default_OembedController extends Zend_Controller_Action {

    //put your code here


    public function indexAction() {

        $html = '
                    <!-- neXlinker START -->
                    	<span class="nexlinker" id="nexlinker_badge_12379largenexva"></span>
                    	<script type="text/javascript">
                    		var _nxs = _nxs || [];
                    		var _nx = new Object();
                    		_nx.aid = "12379";
                    		_nx.s = "large";
                    		_nx.b = "nexva";
                    		_nx.h = "nexva.com";
                    		_nxs.push(_nx);
                    	</script>
                    	<script type="text/javascript" src="http://nexva.com/web/nexlinker/nl.js"></script>
                   	<!-- neXlinker END -->
        ';

        $html = htmlentities($html);

        $output = '<?xml version="1.0" encoding="UTF-8"?>
            <oembed>
                <type>rich</type>
                <width>400</width>
                <height>342</height>
                <html>'.$html.'</html>

            </oembed>
        ';


        $html = "<b>HELLO WORLD FROM OEMBED </b>";
        $html = htmlentities($html);

        $output = '<?xml version="1.0" encoding="UTF-8"?>
                    <oembed>
                      <version type="float">1.0</version>
                      <type>rich</type>
                      <provider-name>Soundcloud</provider-name>
                      <provider-url>http://soundcloud.com</provider-url>
                      <height type="integer">81</height>

                      <width>100%</width>
                      <title>Oopart - RIP Wolfgang [Original Mix] by Oopart Official</title>
                      <description>http://www.facebook.com/oopartofficial
                    http://www.youtube.com/user/OopartVideos
                    http://www.twitter.com/oopartofficial</description>
                      <html>&lt;![CDATA[&lt;object height=&quot;81&quot; width=&quot;100%&quot;&gt;

                    &lt;param name=&quot;movie&quot; value=&quot;http://player.soundcloud.com/player.swf?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F17862046&quot;&gt;&lt;/param&gt;
                    &lt;param name=&quot;allowscriptaccess&quot; value=&quot;always&quot;&gt;&lt;/param&gt;
                    &lt;embed allowscriptaccess=&quot;always&quot; height=&quot;81&quot; src=&quot;http://player.soundcloud.com/player.swf?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F17862046&quot; type=&quot;application/x-shockwave-flash&quot; width=&quot;100%&quot;&gt;&lt;/embed&gt;

                    &lt;/object&gt;

                    &lt;span&gt;&lt;a href=&quot;http://soundcloud.com/oopartmusic/ripwolfgang&quot;&gt;Oopart - RIP Wolfgang [Original Mix]&lt;/a&gt; by &lt;a href=&quot;http://soundcloud.com/oopartmusic&quot;&gt;Oopart Official&lt;/a&gt;&lt;/span&gt;
                    ]]&gt;</html>

                    </oembed>

        ';

           /**
            $errors = libxml_use_internal_errors( 'true' );

           $data = simplexml_load_string( $output );

            libxml_use_internal_errors( $errors );

            if ( !is_object($data) ) die("NO");

            die($data->html);
            *
            */






        header("Content-type: text/xml");


        echo $output;

        die();





    }
}
?>
