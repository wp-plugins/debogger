<?php

/*
Plugin Name: DeBogger
Plugin URI: http://www.pross.org.uk
Description: A simple tool for debugging themes.
Author: Simon Prosser
Version: 0.3
Author URI: http://www.pross.org.uk
*/

add_action('init', 'bog_debug', 5);
if ( is_admin() ):
add_action('admin_footer', 'bog_footer');
add_action('admin_head', 'bog_head');
else:
add_action('wp_footer', 'bog_footer');
add_action('wp_head', 'bog_head');
endif;
// error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
global $debog_notice;
global $_notice_count;
global $debog_warn;
global $_warn_count;
    switch ($errno) {
    case E_NOTICE:
		if (!preg_match('/\/wp-includes\//',$errfile)):
			$_warn_count++;
			$terrfile = strxchr($errfile, '/wp-content/');
			$errfile = str_replace( $terrfile[0], '', $errfile ) ;
			$debog_msg = '<b>-- Debug: </b>' . $errstr . ' on line ' . $errline . ' of ' . $errfile . '<br />';
			//if (!preg_match("/" . strip_tags($debog_msg) . "/", strip_tags($debog_warn) ) ) {
				$debog_warn .= $debog_msg;
		//	}
			endif;
    break;
    case E_USER_NOTICE:
		$_notice_count++;
	//	$backtrace = adodb_backtrace();
		$debog_notice .= '<b>=> </b> ' . $errstr . '<br />';
    break;
    }
    /* Don't execute PHP internal error handler */
    return true;
}

// set debug on/off
function myblank() {

// do nothing
}

function show_normal() {
	return false;
}

function bog_debug() {
	$bogger = get_option('debog');
	if (!$bogger)	{
			$bogger = 'on';
					}
if ($bogger === 'on')	{
	set_error_handler("myErrorHandler");
						}
if ($bogger === 'off')	{
	set_error_handler("show_normal");
						}
if ($bogger === 'sup')	{
	set_error_handler("myblank");
						}
$debog_warn = '';
$debog_notice = '';
$debog = '';
$_notice_count = 0;
$_warn_count = 0;

}

//add links to footer:

function bog_footer() {
global $my_error;
global $_notice_count;
global $_warn_count;
global $debog_notice;
global $debog_warn;

if (isset($_GET['bog'])):
	$nonce=$_REQUEST['_wpnonce'];
	if (!wp_verify_nonce($nonce, 'bog-nonce') ):
		die('Security check');
	else:
	// security pass! 
	if ($_GET['bog'] === 'on')	{
		update_option('debog', 'on');
		set_error_handler("myErrorHandler");
								}
	if ($_GET['bog'] == 'off')	{
		update_option('debog', 'off');
		set_error_handler("show_normal");
								}
	if ($_GET['bog'] == 'sup') 	{
		update_option('debog', 'sup');
		set_error_handler("myblank");
								}
	endif;
	endif;
global $user_ID; if( $user_ID ) :
	if( current_user_can('level_10') ) :
		echo '</div></div></div></div></div></div></div></div></div>'; // just in case there are uncloded divs!

// ok were ready!
			$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$w3c =  bog_check_( $url );
	$color = '99FF99';
	if ( $w3c != 'W3C Valid!(cached)' && $w3c != 'W3C Valid!(not cached)' ) { 
		$color = 'FF9999';
		}	
		if ( $debog_notice ) {
		$color = 'FF9999';
		}
		if ( $debog_warn ) {
		$color = 'FF9999';
		}

		echo '<div style="background-color: #' . $color .'; text-align: left; display: block; clear: both; margin-left: auto; margin-right: auto; border: 1px dashed red; width: 70%; color: #000; padding: 10px;">';

		$nonce= wp_create_nonce('bog-nonce');
		echo '<a style="color: #000;" href="' . strtok( esc_url( $_SERVER['REQUEST_URI'] ), '?' ) . '?_wpnonce=' . $nonce . '&amp;bog=on">Activate Debog</a>&nbsp;&nbsp;&nbsp;';
		echo '<a style="color: #000;" href="' . strtok( esc_url( $_SERVER['REQUEST_URI'] ), '?' ) . '?_wpnonce=' . $nonce . '&amp;bog=off">Normal</a>&nbsp;&nbsp;&nbsp;';
		echo '<a style="color: #000;" href="' . strtok( esc_url( $_SERVER['REQUEST_URI'] ), '?' ) . '?_wpnonce=' . $nonce . '&amp;bog=sup">Suppress Debug</a>&nbsp;&nbsp;&nbsp;';

			$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			echo $w3c;

	echo '<span style="text-align: right; float: right; color: #000;"><small>Debogger by Pross&nbsp;&nbsp;&nbsp;bog status: <strong>'. get_option('debog') . '</strong>';



echo ( defined('FIXPRESS') ) ? '&nbsp;&nbsp;Using FixPress v' . FIXPRESS : '';
//		echo 
//	endif;
	echo '</span>';
		if ($debog_notice):
		echo "<br /><br /><h3>Need to be fixed: $_notice_count</h3>";
		echo $debog_notice;
		endif;
	if ($debog_warn):
		echo "<br /><h3>Other warnings: $_warn_count</h3>";
		echo $debog_warn;
	endif;

if ($debog_notice || $debog_warn ):
$data = get_theme_data( TEMPLATEPATH . '/style.css' );
echo '<a onmouseclick="ShowContent(\'uniquename\'); return true;" href="javascript:ShowContent(\'uniquename\')">[show]</a>';
echo '
<div id="uniquename" 
   style="display:none; 
      border-style: solid; 
      background-color: white; 
      padding: 5px;">';
?>
<span>
Theme Review: '''<?php echo $data[ 'Name' ] . ' v' . $data[ 'Version' ]; ?>'''<br />
=> Themes should be reviewed using '''define('WP_DEBUG', true);''' in wp-config.php<br />
=> Themes should be reviewed using the test data from the [http://codex.wordpress.org/Theme_Unit_Test Theme Checklists]<br />
----<br />
'''WP_DEBUG et al.:'''<br />
<?php if (!empty( $debog_warn ) ) echo $debog_warn; ?>
<?php if (!empty( $debog_notice ) ) echo $debog_notice; ?>
----<br />
'''Theme Test Data:'''<br />
{ INSERT REVIEW }
<br />
----<br />
Overall: '''not-accepted'''<br />
- Items marked (=>) '''must''' be addressed.<br />
- Other items noted should be addressed and corrected as needed.<br />
- Additional review may be required once the above issues are resolved.<br />
</span>
</div>
<?php
endif;
	echo '</div>';
	endif;
endif;
}

//strxchr(string haystack, string needle [, bool int leftinclusive [, bool int rightinclusive ]])
function strxchr($haystack, $needle, $l_inclusive = 0, $r_inclusive = 0){
   if(strrpos($haystack, $needle)){
       //Everything before last $needle in $haystack.
       $left =  substr($haystack, 0, strrpos($haystack, $needle) + $l_inclusive);
        //Switch value of $r_inclusive from 0 to 1 and viceversa.
       $r_inclusive = ($r_inclusive == 0) ? 1 : 0;
        //Everything after last $needle in $haystack.
       $right =  substr(strrchr($haystack, $needle), $r_inclusive);
       //Return $left and $right into an array.
       return array($left, $right);
   } else {
       if(strrchr($haystack, $needle)) return array('', substr(strrchr($haystack, $needle), $r_inclusive));
       else return false;
   }
}


function bog_check($url) {
require_once 'Services/W3C/HTMLValidator.php';

$v = new Services_W3C_HTMLValidator();
$r = $v->validate($url);
if ($r->isValid()) {
    return $url.' is valid!';
} else {


return $url.' is NOT valid! ' . count($r->errors) . ' errors ' . count($r->warnings) . ' warnings. (<a target="_blank" href="http://validator.w3.org/check?uri=' . $url . '&charset=%28detect+automatically%29&doctype=Inline">W3C</a>)';

	
}




















}





















function bog_check_($url) {

		function checkcache($url) {
				$w3c_url = 'http://validator.w3.org/check?uri=' .$url. '&charset=%28detect+automatically%29&doctype=Inline';
				$result = array();
				$timeout = 60; // cache timeout
				$cache_dir = WP_PLUGIN_DIR . '/debogger/cache/';
				
				$cache_file = $cache_dir . md5($url) . '.cache';;
				if(!file_exists($cache_file) OR filemtime($cache_file) < (time() - $timeout)){
				if( !class_exists( 'WP_Http' ) )
				include_once( ABSPATH . WPINC. '/class-http.php' );
				$result_1 = wp_remote_retrieve_body( wp_remote_get($w3c_url) );
				$result[0] =  $result_1;
				$result[1] = '(not cached)';
				file_put_contents($cache_file, $result_1, LOCK_EX);
				} else {
					$result[0] = file_get_contents($cache_file);
					$result[1] = '(cached)';
					}
				return $result;
				}

$html = array();
$html = checkcache($url);
if (empty($html)) {
	return 'error';
	}
$doc = new DOMDocument();
$doc->loadHTML($html[0]);
$res = $doc->getElementById('congrats');
if (isset($res)) { $res = $doc->getElementById('congrats')->nodeValue; }
  if($res == 'Congratulations') {
	return 'W3C Valid!' . $html[1];
  } else {
	return 'Not W3C valid (<a href="http://validator.w3.org/check?uri=' . $url . '&charset=%28detect+automatically%29&doctype=Inline">errors</a>)' . $html[1];
  }
}



function bog_head() {
global $user_ID;
if( $user_ID ) :
        if( current_user_can('level_10') ) :
echo '
<script type="text/javascript"><!--
function HideContent(d) {
if(d.length < 1) { return; }
document.getElementById(d).style.display = "none";
}
function ShowContent(d) {
if(d.length < 1) { return; }
document.getElementById(d).style.display = "block";
}
function ReverseContentDisplay(d) {
if(d.length < 1) { return; }
if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
else { document.getElementById(d).style.display = "none"; }
}
//--></script>
';
endif;
endif;
}
