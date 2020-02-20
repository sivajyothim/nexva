<?php 
$method = (isset($_GET['m']) && !empty($_GET['m'])) ? $_GET['m'] : 'test'; 

switch ($method) {
	case 'test':
		$output = array ('key1' => 'value 1', 'key2' => 'value 2', 'key3' => 'value 3');
		break;
	
	case 'searchSg':
		$query = (isset($_GET['q']) && !empty($_GET['q'])) ? $_GET['q'] : 0;
		$output = do_search_suggest ($query);
		break;

	case 'getFeatAppList':
		$output = do_feat_app_list ();
		break;

	case 'getAppList':
		$cat = (isset($_GET['cat']) && !empty($_GET['cat'])) ? $_GET['cat'] : 0;
		$output = do_app_list ($cat);
		break;

	case 'getCatList':
		$output = do_cat_list ();
		break;
	
	case 'getMenuList':
		$block = (isset($_GET['blk']) && !empty($_GET['blk'])) ? $_GET['blk'] : 0;
		$sID = (isset($_GET['sid']) && !empty($_GET['sid'])) ? $_GET['sid'] : 0;
		$output = do_menu_list ($sID,$block);
		break;
	
	case 'getMenu':
		$menuID = (isset($_GET['mid']) && !empty($_GET['mid'])) ? $_GET['mid'] : 0;
		$output = do_menu ($menuID);
		break;
	
	case 'getFeatAppInfo':
		$appID = (isset($_GET['a']) && !empty($_GET['a'])) ? $_GET['a'] : 0;
		$output = do_feat_app_info ($appID);
		break;
	
	case 'startSession':
		$sID = (isset($_GET['sid']) && !empty($_GET['sid'])) ? $_GET['sid'] : 0;
		$output = do_session_start ($sID);
		break;
	
	case 'getUserGreeting':
		$sID = (isset($_GET['sid']) && !empty($_GET['sid'])) ? $_GET['sid'] : 0;
		$output = do_user_greeting ($sID);
		break;
	
	case 'getUserPhones':
		$sID = (isset($_GET['sid']) && !empty($_GET['sid'])) ? $_GET['sid'] : 0;
		$output = get_user_phones ($sID);
		break;
	
	case 'setUserLanguage':
		$sID = (isset($_GET['sid']) && !empty($_GET['sid'])) ? $_GET['sid'] : 0;
		$lang = (isset($_GET['lang']) && !empty($_GET['lang'])) ? $_GET['lang'] : 0;
		$output = set_user_language ($sID, $lang);
		break;
	
	case 'setUserCurrency':
		$sID = (isset($_GET['sid']) && !empty($_GET['sid'])) ? $_GET['sid'] : 0;
		$cur = (isset($_GET['cur']) && !empty($_GET['cur'])) ? $_GET['cur'] : 0;
		$output = set_user_currency ($sID, $cur);
		break;
	
	case 'userLogin':
		$sID = (isset($_POST['sid']) && !empty($_POST['sid'])) ? $_POST['sid'] : 0;
		$username = (isset($_POST['username']) && !empty($_POST['username'])) ? $_POST['username'] : 0;
		$password = (isset($_POST['password']) && !empty($_POST['password'])) ? $_POST['password'] : 0;
		$output = do_user_login ($sID, $username, $password);
		break;
	
	case 'searchSgPhone':
		$query = (isset($_GET['q']) && !empty($_GET['q'])) ? $_GET['q'] : 0;
		$output = do_search_phone_suggest ($query);
		break;
		
	case 'setUserPhone':
		$phoneID = (isset($_GET['phone']) && !empty($_GET['phone'])) ? $_GET['phone'] : 0;
		$sID = (isset($_GET['sid']) && !empty($_GET['sid'])) ? $_GET['sid'] : 0;
		$output = set_user_phone ($phoneID,$sID);
		break;
		
	case 'removeUserPhone':
		$phoneID = (isset($_GET['phone']) && !empty($_GET['phone'])) ? $_GET['phone'] : 0;
		$sID = (isset($_GET['sid']) && !empty($_GET['sid'])) ? $_GET['sid'] : 0;
		$output = remove_user_phone ($phoneID,$sID);
		break;
	
	case 'getPageTitle':
		$sID = (isset($_GET['sid']) && !empty($_GET['sid'])) ? $_GET['sid'] : 0;
		$pgID = (isset($_GET['pgid']) && !empty($_GET['pgid'])) ? $_GET['pgid'] : 0;
		$output = get_page_title ($sID, $pgID);
		break;
}

echo json_encode($output);

function get_page_title ($sID, $pgID) {
	$output['pageTitle']='Books & References';
	$output['cat']='cat_002';
	$output['breadcrumbs'][0]['pgid']='home';
	$output['breadcrumbs'][0]['title']='Home';
	$output['breadcrumbs'][1]['pgid']='cat_01';
	$output['breadcrumbs'][1]['title']='Books & References';
	return $output;
}

function get_user_phone ($phoneID, $sID) {
	$output['phones']=update_user_phones ($sID);
	$output['systemMsg']['title'] ='';
	$output['systemMsg']['msg']   ='';
	return $output;
}

function set_user_phone ($phoneID, $sID) {
	$output['phones']=update_user_phones ($sID);
	$output['systemMsg']['title'] ='user settings updated';
	$output['systemMsg']['msg']   ='new phone added.';
	return $output;
}

function remove_user_phone ($phoneID, $sID) {
	$output['phones']=update_user_phones ($sID);
	$output['systemMsg']['title']='user settings updated';
	$output['systemMsg']['msg']='phone was removed.';
	return $output;
}

function update_user_phones ($sID) {
	if (strlen($sID)<2) { 
		$m=0;
		$output[$m]['id']		= 'null_phone';
		$output[$m]['img']		= 'images/unknown_phone_icon.png';
		$output[$m]['phone']	= 'please select your mobile device.';
	} else {
		$n=rand(0,3);
		$i=0;	
		do {
			$m=rand(0,3);
			switch ($m) {
				case 0: $query='no'; break;
				case 1: $query='mo'; break;
				case 2: $query='so'; break;
				case 3: $query='i-'; break;
			}
			$phones=do_search_phone_suggest ($query);
			$o=rand(0,3);
			$output[$i]=$phones[$o];
			$i++;
		} while ($i<$n);
	}
	return $output;
}

function do_search_phone_suggest ($query) {
	$query='_____'.$query;
	if (strpos($query, 'no') > 1) {
		$i=0;
		$searchSg[$i]['id']		= 'nokia_n73';
		$searchSg[$i]['img']	= '/web/phone_images/nokia_n73.jpg';
		$searchSg[$i]['phone']	= 'Nokia N73';
		$i++;
		$searchSg[$i]['id']		= 'nokia_5130_xpressmusic';
		$searchSg[$i]['img']	= '/web/phone_images/nokia_5130_xpressmusic.jpg';
		$searchSg[$i]['phone']	= 'Nokia 5130 XpressMusic';
		$i++;
		$searchSg[$i]['id']		= 'nokia_7610';
		$searchSg[$i]['img']	= '/web/phone_images/nokia_7610.jpg';
		$searchSg[$i]['phone']	= 'Nokia 7610';
		$i++;
		$searchSg[$i]['id']		= 'nokia_e71';
		$searchSg[$i]['img']	= '/web/phone_images/nokia_e71.jpg';
		$searchSg[$i]['phone']	= 'Nokia E71';
	} elseif (strpos($query, 'mo') > 1) {
		$i=0;
		$searchSg[$i]['id']		= 'motorola_a810';
		$searchSg[$i]['img']	= '/web/phone_images/motorola_a810.jpg';
		$searchSg[$i]['phone']	= 'Motorola A810';
		$i++;
		$searchSg[$i]['id']		= 'motorola_e6_rokr';
		$searchSg[$i]['img']	= '/web/phone_images/motorola_e6_rokr.jpg';
		$searchSg[$i]['phone']	= 'Motorola E6 Rockr';
		$i++;
		$searchSg[$i]['id']		= 'motorola_e8_rokr';
		$searchSg[$i]['img']	= '/web/phone_images/motorola_e8_rokr.jpg';
		$searchSg[$i]['phone']	= 'Motorola E8 Rockr';
		$i++;
		$searchSg[$i]['id']		= 'motorola_q9';
		$searchSg[$i]['img']	= '/web/phone_images/motorola_q9.jpg';
		$searchSg[$i]['phone']	= 'Motorola Q9';
	} elseif (strpos($query, 'so') > 1) {
		$i=0;
		$searchSg[$i]['id']		= 'sony-ericsson_k310i';
		$searchSg[$i]['img']	= '/web/phone_images/sony-ericsson_k310i.jpg';
		$searchSg[$i]['phone']	= 'Sony Ericsson K310i';
		$i++;
		$searchSg[$i]['id']		= 'sony-ericsson_k510i';
		$searchSg[$i]['img']	= '/web/phone_images/sony-ericsson_k510i.jpg';
		$searchSg[$i]['phone']	= 'Sony Ericsson K510i';
		$i++;
		$searchSg[$i]['id']		= 'sony-ericsson_k530i';
		$searchSg[$i]['img']	= '/web/phone_images/sony-ericsson_k530i.jpg';
		$searchSg[$i]['phone']	= 'Sony Ericsson K530i';
		$i++;
		$searchSg[$i]['id']		= 'sony-ericsson_w800i';
		$searchSg[$i]['img']	= '/web/phone_images/sony-ericsson_w800i.jpg';
		$searchSg[$i]['phone']	= 'Sony Ericsson W800i';
	} elseif (strpos($query, 'i-') > 1) {
		$i=0;
		$searchSg[$i]['id']		= 'i-mate_jamin';
		$searchSg[$i]['img']	= '/web/phone_images/i-mate_jamin.jpg';
		$searchSg[$i]['phone']	= 'i-mate Jamin';
		$i++;
		$searchSg[$i]['id']		= 'i-mate_jas_jar';
		$searchSg[$i]['img']	= '/web/phone_images/i-mate_jas_jar.jpg';
		$searchSg[$i]['phone']	= 'i-mate Jas Jar';
		$i++;
		$searchSg[$i]['id']		= 'i-mate_k-jam';
		$searchSg[$i]['img']	= '/web/phone_images/i-mate_k-jam.jpg';
		$searchSg[$i]['phone']	= 'i-mate K-Jam';
		$i++;
		$searchSg[$i]['id']		= 'i-mate_k-jam';
		$searchSg[$i]['img']	= '/web/phone_images/i-mate_k-jam.jpg';
		$searchSg[$i]['phone']	= 'i-mate K-Jam';
	}
	
	$output=$searchSg;
	return $output;
}

function do_user_login ($sID, $username, $password) {
	$output['action']	= 'userLogin';
	$output['sid']		= $sID;
	
	$status = rand (0,2);
	switch ($status) {
		case 0: 
			$output['status']	= 'ok';
			$output['systemMsg']['title']	='test';
			$output['systemMsg']['msg']		='test';
			$output['userGreeting']			='greetings '. $username.'! welcome to neXva.com';
			break;
		case 1: 
			$output['status']	= 'error';
			$output['systemMsg']['title']	='login error: username';
			$output['systemMsg']['msg']		='the username you have entered does not exist in neXva.com. please try again.';
			break;
		case 2: 
			$output['status']	= 'error';
			$output['systemMsg']['title']	='login error: password';
			$output['systemMsg']['msg']		='the password you have entered is invalid. please try again.';
			break;
	}
	return $output;
}

function set_user_currency ($sID, $cur) {
	$output['cur']=$cur;
	$output['systemMsg']['title']	='currency settings';
	$output['systemMsg']['msg']		='currency has been changed to '.$cur;
	return ($output);
}

function set_user_language ($sID, $lang) {
	$output['lang']=$lang;
	$output['systemMsg']['title']	='language settings';
	$output['systemMsg']['msg']		='language has been changed to '.$lang;
	
	$output['menu']['primary_about']	='about';
	$output['menu']['primary_adv']		='avdertise';
	$output['menu']['primary_signup']	='sign up';
	$output['menu']['primary_contact']	='contact';
	$output['menu']['prod_more']		='more info';
	$output['menu']['prod_demo']		='download';
	$output['menu']['prod_buy']			='buy';
	$output['menu']['moreinfo']			='more info';
	$output['menu']['download']			='download';
	$output['menu']['buy']				='buy';
	
	return ($output);
}

function do_user_greeting ($sID) {
	$output['msg']='Welcome to neXva.com.';
	return ($output);
}

function do_session_start ($sID) {
	if (!$sID) {
		session_start ();
		$sID=session_id ();
	} else {
		session_start ();
		session_regenerate_id ();
		$sID=session_id();
	}
	$output['sID']=$sID;
	return ($output);
}

function do_cat_list () {
	$i=0;
	do {
		$output[$i]['id'] 	= 'cat_'.$i;
		$output[$i]['name']	= 'Category: '.$i;
		$i++;
	} while ($i<rand(2,6));
	return $output;
}

function do_menu_list ($sID,$block) {		
	$i=0;
	if ($block=='right') {
		$output[$i]['id'] 		= 'notices';
		$output[$i]['title']	= 'notices';
		$i++;
		$output[($i+1)]['id'] 		= 'nexva_rec';
		$output[($i+1)]['title']	= 'neXva recommends';
		$i++;
	} else {
		do {
			$output[$i]['id'] 	= 'menu_'.$i;
			$output[$i]['title']= 'Menu: '.$i;
			$i++;
		} while ($i<rand(1,3));	
			$output[($i+1)]['id'] 		= 'nexva_rec';
			$output[($i+1)]['title']	= 'neXva recommends';
			$i++;
	}
	return $output;
}

function do_menu ($menuID) {
	if ($menuID=='notices') {
		$output[0]['pid'] 	= '';
		$output[0]['id']	= 'notice_0';
		$output[0]['title']	= 'neXva version two. with jquery and ajax. this is a notice.';
		
		$output[1]['pid'] 	= '';
		$output[1]['id']	= 'notice_1';
		$output[1]['title']	= 'the second notice. will be some random message. blah blah blah blah blah.';
	} else {
		$i=0;
		$depth=rand (0,2);
		$output[$i]['pid']='root'; $output[$i]['id']='root_'.$i; $output[$i]['title'] = 'Books & Reference'; $i++;
		$output[$i]['pid']='root'; $output[$i]['id']='root_'.$i; $output[$i]['title'] = 'Goodies & Entertainment'; $i++;
		$output[$i]['pid']='root'; $output[$i]['id']='root_'.$i; $output[$i]['title'] = 'Lifestyle & Wellness'; $i++;
		$output[$i]['pid']='root'; $output[$i]['id']='root_'.$i; $output[$i]['title'] = 'Music & Audio'; $i++;
		$output[$i]['pid']='root'; $output[$i]['id']='root_'.$i; $output[$i]['title'] = 'News & Sports'; $i++;
		$output[$i]['pid']='root'; $output[$i]['id']='root_'.$i; $output[$i]['title'] = 'Social Networks & Messaging'; $i++;
		$output[$i]['pid']='root'; $output[$i]['id']='root_'.$i; $output[$i]['title'] = 'Themes'; $i++;
		$output[$i]['pid']='root'; $output[$i]['id']='root_'.$i; $output[$i]['title'] = 'Tools & Productivity'; $i++;
		$output[$i]['pid']='root'; $output[$i]['id']='root_'.$i; $output[$i]['title'] = 'Travel & GPS'; $i++;
		$output[$i]['pid']='root'; $output[$i]['id']='root_'.$i; $output[$i]['title'] = 'Video & TV'; $i++;
		
		switch ($depth) {
			case 1:
				$j=0;
				$output[$i]['pid']='root_'.$depth; $output[$i]['id']='root_'.$depth.'_'.$j; $output[$i]['title'] = 'Audio books'; $i++; $j++;
				$output[$i]['pid']='root_'.$depth; $output[$i]['id']='root_'.$depth.'_'.$j; $output[$i]['title'] = 'Comics / Manga'; $i++; $j++;
				$output[$i]['pid']='root_'.$depth; $output[$i]['id']='root_'.$depth.'_'.$j; $output[$i]['title'] = 'eBooks'; $i++; $j++;
				$output[$i]['pid']='root_'.$depth; $output[$i]['id']='root_'.$depth.'_'.$j; $output[$i]['title'] = 'Reference / Dictionaries'; $i++; $j++;
				$output[$i]['pid']='root_'.$depth; $output[$i]['id']='root_'.$depth.'_'.$j; $output[$i]['title'] = 'Religion'; $i++; $j++;
				break;
			case 2:
				$j=0;
				$output[$i]['pid']='root_'.$depth; $output[$i]['id']='root_'.$depth.'_'.$j; $output[$i]['title'] = 'Accounts / Tax'; $i++; $j++;
				$output[$i]['pid']='root_'.$depth; $output[$i]['id']='root_'.$depth.'_'.$j; $output[$i]['title'] = 'Currency / Convertors'; $i++; $j++;
				$output[$i]['pid']='root_'.$depth; $output[$i]['id']='root_'.$depth.'_'.$j; $output[$i]['title'] = 'Inventory / Sales'; $i++; $j++;
				$output[$i]['pid']='root_'.$depth; $output[$i]['id']='root_'.$depth.'_'.$j; $output[$i]['title'] = 'Stocks / Finance'; $i++; $j++;
				break;
		}	
	}
	
	return $output;
}

function do_feat_app_list () {
	$i=0;
	do {
		$output[$i]['id'] 	= '00'.$i;
		$output[$i]['name']	= 'Featured App: '.$i;
		$i++;
	} while ($i<20);
	return $output;
}

function do_app_list ($cat) {
	$i=0;
	do {
		$output[$i]['id'] 		= '00'.$i;
		$output[$i]['name']		= 'Featured App: '.$i;
		$output[$i]['poster']	= '/web/prod_images/prod_000.jpg';	
		$output[$i]['price']	= rand (12, 34) + (rand (0, 99))/100;
		$output[$i]['desc']		='prod desc 000-'. $i .'.... cat: ' .$cat.'   Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce scelerisque tristique dictum. Suspendisse potenti. Maecenas massa purus, tristique quis placerat eget, venenatis at libero. Nam eu mauris sit amet nulla tincidunt tincidunt. In ut ipsum sem. Sed posuere pharetra varius.';
		$output[$i]['rating'] 	= 5-(rand(0,4));
		$output[$i]['platform']	= 'Microsoft Windows Mobile!!';
		$output[$i]['size']		= rand (10, 40) + (rand (0, 99))/100;	
		$output[$i]['downloads']= rand (0, 99) + rand (100, 999);		
		$i++;
	} while ($i<4);
	return $output;
}

function do_feat_app_info ($appID) {
	$prodInfo['id'] 		= $appID;
	$prodInfo['title']		= 'Product Name OOO!!-'.$appID;
	$prodInfo['desc'] 		='prod desc 000-'. $appID .'.... Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce scelerisque tristique dictum. Suspendisse potenti. Maecenas massa purus, tristique quis placerat eget, venenatis at libero. Nam eu mauris sit amet nulla tincidunt tincidunt. In ut ipsum sem. Sed posuere pharetra varius. Duis ut ipsum nibh, et fringilla nulla. Phasellus luctus, augue nec hendrerit vestibulum, magna massa bibendum turpis, in tempus velit sapien sit amet nulla. Sed ac augue eu metus pretium pretium elementum sed arcu. Morbi id libero massa, vel tristique nisl. In ipsum eros, fermentum sed ultricies vel, tristique sed mauris.';
	$prodInfo['downloads']	= (12*$appID) + 224;
	$prodInfo['price']		= 112.5+$appID;
	$prodInfo['size']		= 1.15+$appID;	
	$prodInfo['poster']		= '/web/poster_images/poster_'.$appID.'.gif';
	$prodInfo['platform']	= 'Microsoft Windows Mobile';
	$prodInfo['vendor'] 	= 'Vendor 000-'.$appID;
	$prodInfo['rating'] 	= 5-(rand(0,4));
	return $prodInfo;
}

function do_search_suggest ($query) {
	$searchSg = array ('dsfdf df jgkld fjklgjdf', 'df dfgeow324 2 ewr terojter0', 'dffg jeri trejk jklterjklj', 'sfjkrwj rlkwejr klewj rkwjkje');
	$i=0;
	do {
		$item = array ('val'=> $searchSg [$i], 'id' => 'sg_'.$i);
		$output[$i] = $item;
		$i++;
	} while ($i < count($searchSg)); 
	return $output;
}
?> 
