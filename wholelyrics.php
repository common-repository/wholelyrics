<?php
/*
Plugin Name: WholeLyrics
Plugin URI: http://www.wholelyrics.com/
Description: Display a selected lyric to your blog with customizable colors.
Author: WholeLyrics Team
Version: 1.1.1
Author URI: http://www.wholelyrics.com/contact/
*/

define('WL_VERSION', '1.1.1');

add_action('widgets_init', 'wholelyrics_init');
add_action('admin_menu', 'wholelyrics_menu');

function wholelyrics_init(){
    if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control'))  return;

    register_widget_control(array('Whole Lyrics', 'widgets'), 'wholelyrics_control', 330, 355);
    register_sidebar_widget(array('Whole Lyrics', 'widgets'), 'wholelyrics_widget');
    add_action('admin_head', 'wholelyrics_menu_header');

    $options = get_option('wholelyrics_widget');
    
    if(strlen($options['code'])<2){
        $df = array(); $df['title'] = 'Lyrics'; $df['code'] = '62WL3SHO';
        $df['c1'] = 'EDEDED'; $df['c2'] = '141414'; $df['c3'] = 'BABABA';
        $df['c4'] = 'F5FF1F'; $df['c5'] = '000000'; $df['c6'] = 'ADADAD';
        $df['s']  = 8;
        update_option('wholelyrics_widget', $df);

        $sidebars_widgets = wp_get_sidebars_widgets();
        if ( empty( $sidebars_widgets ) )
        $sidebars_widgets = wp_get_widget_defaults();
        if ( empty( $sidebars_widgets['sidebar-1'] ) )
        $sidebars_widgets['sidebar-1'] = array();

        if(!in_array('whole-lyrics', $sidebars_widgets['sidebar-1'])){
            $sidebars_widgets['sidebar-1'][] = 'whole-lyrics';
            wp_set_sidebars_widgets( $sidebars_widgets );
        }
    }
}
function wholelyrics_widget($args) {
    extract($args);
    $options     = get_option('wholelyrics_widget');
    $bs_title    = $options['title'];
    $bs_code     = ((!empty($options['code']))?$options['code']:'62WL3SHO');
    
    # Widget Style
    $widget_style = array();
    $widget_style['v'] = 1;
    $c1 = $options['c1'];
    $c2 = $options['c2'];
    $c3 = $options['c3'];
    $c4 = $options['c4'];
    $c5 = $options['c5'];
    $c6 = $options['c6'];
    $s  = $options['s'];
    $e  = $options['e'];

    $raw_result = httpRequest('code='.$bs_code, 'www.wholelyrics.com', '/rest/widgets/');
    $song = unserialize($raw_result[1]);
    $response = '<iframe src="http://www.wholelyrics.com/widget/show/'.$song['slug_artist'].'.'.
                $song['slug_song'].'/?'."c1={$c1}&c2={$c2}&c3={$c3}&c4={$c4}&c5={$c5}&c6={$c6}&s={$s}&".
                '" marginwidth="0" marginheight="0" scrolling="no" frameborder="0"'.
                ' height="201" width="155" id="wholel-iframe" title="Widget for '.$song['title'].' Lyrics"></iframe>';
	$response = str_replace(':widget:',$response, stripslashes($e));
    echo $before_widget . $before_title . $bs_title . $after_title . $response . $after_widget;
}
function wholelyrics_control(){
    echo '<a href="'. get_settings('siteurl') . '/wp-admin/plugins.php?page=wholelyrics-options'.'" target="_blank">Configuration Page</a>';
}
function wholelyrics_menu_header() {
$wholelyrics_path =  get_settings('siteurl') . "/wp-content/plugins/wholelyrics";
$options     = get_option('wholelyrics_widget');
$bs_code     = ((!empty($options['code']))?$options['code']:'62WL3SHO');
 echo <<<HEADER
 <script type="text/javascript" src="{$wholelyrics_path}/farbtastic/jQuery.js"></script>
 <script type="text/javascript" src="{$wholelyrics_path}/farbtastic/farbtastic.js"></script>
 <link rel="stylesheet" href="{$wholelyrics_path}/farbtastic/farbtastic.css" type="text/css" />
 <link rel="stylesheet" href="farbtastic.css" type="text/css" />
 <style type="text/css" media="screen">
   .colorwell { border: 2px solid #fff; width: 6em; text-align: center; cursor: pointer; }
   body .colorwell-selected { border: 2px solid #000; font-weight: bold; }
 </style>
 
 <script type="text/javascript" charset="utf-8">
  function updateWidget(){
      var c1 = $('#color1').val().replace('#','');var c2 = $('#color2').val().replace('#','');var c3 = $('#color3').val().replace('#','');
      var c4 = $('#color4').val().replace('#','');var c5 = $('#color5').val().replace('#','');var c6 = $('#color6').val().replace('#','');
      var s = $('#wholelyrics-speed').val();
      document.getElementById('widgetTest').src='http://www.wholelyrics.com/widget/test/?code={$bs_code}&c1='+c1+'&c2='+c2+'&c3='+c3+'&c4='+c4+'&c5='+c5+'&c6='+c6+'&s='+s+'&';
  }
 </script>
HEADER;
}
function wholelyrics_control_menu() {
    $options = get_option('wholelyrics_widget');
    if(strlen($options['code'])<2)
        $options = array('title'=>'Lyrics', 'code'=>'62WL3SHO', 'c1' => 'EDEDED', 'c2' => '141414', 'c3' => 'BABABA', 'c4' => 'F5FF1F', 'c5' => '000000', 'c6' => 'ADADAD', 's' => '8');
    if($_POST['wholelyrics-submit']){
        $options['title']         = trim(strip_tags(stripslashes($_POST['wholelyrics-title'])));
        $options['code']          = trim(strip_tags(stripslashes($_POST['wholelyrics-code'])));
        # Colors
        $options['c1']             = (ereg('[^A-Fa-f0-9]', $_POST['c1']))?trim($_POST['c1'],'#'):'274690';
        $options['c2']             = (ereg('[^A-Fa-f0-9]', $_POST['c2']))?trim($_POST['c2'],'#'):'E8EDF9';
        $options['c3']             = (ereg('[^A-Fa-f0-9]', $_POST['c3']))?trim($_POST['c3'],'#'):'CC7401';
        $options['c4']             = (ereg('[^A-Fa-f0-9]', $_POST['c4']))?trim($_POST['c4'],'#'):'058A05';
        $options['c5']             = (ereg('[^A-Fa-f0-9]', $_POST['c5']))?trim($_POST['c5'],'#'):'FFFFFF';
        $options['c6']             = (ereg('[^A-Fa-f0-9]', $_POST['c6']))?trim($_POST['c6'],'#'):'274690';
        $options['s']              = (intval($_POST['wholelyrics-speed']))?intval($_POST['wholelyrics-speed']):8;
        $options['e']              = (strlen($_POST['wholelyrics-enclose'])>8)?$_POST['wholelyrics-enclose']:':widget:';

        update_option('wholelyrics_widget', $options);
    }
    $bs_title         = htmlspecialchars($options['title'], ENT_QUOTES);
    $bs_code          = htmlspecialchars($options['code'], ENT_QUOTES);
    ob_start();
    if (!empty($_POST['save'])) { ?>
    <div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
    <?php } ?>
    <form action="" method="post" id="wholelyrics-conf" name="wholelyrics">
        <div class="wrap">
            <h2>Whole Lyrics Configuration</h2>
    <?php
        echo '<p>
                <label for="wholelyrics-title">Widget Title:
                <input style="width: 180px;" id="wholelyrics-title" name="wholelyrics-title" type="text" value="'.$bs_title.'" />
                </label>
                <small>You can also leave this blank</small>
             </p>
             <p>
                <label for="wholelyrics-code">Lyrics Code:
                <input style="width: 180px;" id="wholelyrics-code" name="wholelyrics-code" type="text" value="'.$bs_code.'" />
                </label>
             </p>
             <p>
                <label for="wholelyrics-speed">Scroll Speed:
                <input style="width: 180px;" id="wholelyrics-speed" name="wholelyrics-speed" type="text" value="'.$options['s'].'" onchange="updateWidget();" />
                </label>
                <small>Between 1 to 50 (1 is the fastest)</small>
             </p>
             <p>
                <label for="wholelyrics-enclose">Enclose HTML Code:
                <input style="width: 180px;" id="wholelyrics-enclose" name="wholelyrics-enclose" type="text" value="'.($options['e']?htmlspecialchars(stripcslashes($options['e'])):'<div>:widget:<div>').'" />
                </label>
                <small>Widget enclosed HTML code. Should have a <b>:widget:</b> word inside (eg. &lt;div class="myclass"&gt;:widget:&lt;/div&gt;)</small>
             </p>
             <p><small>To get the Lyrics Code, go to <a href="http://www.wholelyrics.com/" target="_blank">'.
             'WholeLyrics.com</a> and search your favorite song. Under the lyric\'s page, copy the <b>Widget Code</b>'.
             ' located at the left column of the page (this code has an <b>8 characters</b>)</small>.</p>
             <h3><b>Customize Widget Colors</b></h3>
             <table width="800px">
                <tr>
                    <td width="30%">
                        <label for="color1">Text Color:</label><input type="text" id="color1" onblur="updateWidget();" name="c1" class="colorwell" value="#'.$options['c1'].'" />
                    </td>
                    <td rowspan="6" width="30%"><div id="picker" style="float: right;"></div></td>
                    <td rowspan="6" width="35%" align="center">
                        <iframe id="widgetTest" src="http://www.wholelyrics.com/widget/test/?code='.$bs_code.'&c1=d6d6d6&c2=000000&c3=fa0000&c4=fffd3d&c5=0f0094&c6=99a5c2&s='.$options['s'].'" marginwidth="0" marginheight="0" scrolling="no" frameborder="0" height="201" width="155" id="wholel-iframe" title="Widget for Lyrics"></iframe>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <label for="color2">Background Color:</label><input type="text" id="color2" onblur="updateWidget();" name="c2" class="colorwell" value="#'.$options['c2'].'" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <label for="color3">Border Color:</label><input type="text" id="color3" onblur="updateWidget();" name="c3" class="colorwell" value="#'.$options['c3'].'" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <label for="color4">Header Text Color:</label><input type="text" id="color4" onblur="updateWidget();" name="c4" class="colorwell" value="#'.$options['c4'].'" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <label for="color5">Header Background Color:</label><input type="text" id="color5" onblur="updateWidget();" name="c5" class="colorwell" value="#'.$options['c5'].'" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <label for="color6">Header Border Color:</label><input type="text" id="color6" onblur="updateWidget();" name="c6" class="colorwell" value="#'.$options['c6'].'" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="button" value="Preview changes" class="button" style="width:130px;padding:2px 5px; font-weight: bold; background:#ddd; border: 1px solid; border-style: outset;">
                    </td>
                    <td>&nbsp;</td>
                </tr>
             </table>
             <br /><br />
             <small>Home Page: <a href="http://www.wholelyrics.com/" target="_blank">'.'www.WholeLyrics.com</a></small>
             </p>
             <input type="hidden" id="wholelyrics-submit" name="wholelyrics-submit" value="1" />';
         ?>
            <p class="submit">
                <input type="submit" name="save" value="<?php echo __('Save Changes', 'wholelyrics');?>" />
            </p>
        </div>
    </form>
    <script type="text/javascript" charset="utf-8">
    function initWL(){
        var f = $.farbtastic('#picker');
        var p = $('#picker').css('opacity', 0.50)
        var selected;
        $('.colorwell')
          .each(function () { f.linkTo(this); $(this).css('opacity', 0.75); })
          .focus(function() {
            if (selected) {
              $(selected).css('opacity', 0.65).removeClass('colorwell-selected');
            }
            f.linkTo(this);
            p.css('opacity', 1);
            $(selected = this).css('opacity', 1).addClass('colorwell-selected');
          });
        updateWidget();
    }
    initWL();
    </script>
    <?php
    ob_end_flush();
}
function httpRequest($request, $host, $path) {
    global $wp_version;
    $http_request  = "POST $path HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . get_option('blog_charset') . "\r\n";
    $http_request .= "Content-Length: " . strlen($request) . "\r\n";
    $http_request .= "User-Agent: WholeLyrics Wordpress Widget\r\n";
    $http_request .= "\r\n";
    $http_request .= $request;
    $http_request .= "\r\n\r\n";
    $response = '';
    if( false != ( $fs = @fsockopen($host, 80, $errno, $errstr, 10) ) ) {
        fwrite($fs, $http_request);
        while ( !feof($fs) )
            $response .= fgets($fs, 4096);
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);
    }
    return $response;
}
function wholelyrics_menu(){
    add_submenu_page('plugins.php', __('Whole Lyrics', 'wholelyrics'), __('Whole Lyrics', 'wholelyrics'), 'manage_options', 'wholelyrics-options', 'wholelyrics_control_menu');
}
