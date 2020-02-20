<?php
/**
 * Facebook Like
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Lib
 * @version     $Id$
 */

class Nexva_View_Helper_FacebookLike extends Zend_View_Helper_Abstract {

    public function FacebookLike($url) {
        $url = urlencode($url);
//        $width = 450;
//        $action = 'like'; // recommend
//        $likeButton = '<iframe src="http://www.facebook.com/plugins/like.php?href='.$url.'&amp;layout=button_count&amp;show_faces=true&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:21px;" allowTransparency="true"></iframe>';
//        $likeButton = '<fb:like href="' . $url . '" layout="button_count" show_faces="false"></fb:like>';
        $likeButton = '<iframe src="http://www.facebook.com/plugins/like.php?href='.$url.'&amp;layout=standard&amp;show_faces=true&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:20px;" allowTransparency="true"></iframe>';
        return $likeButton;
    }
}

?>