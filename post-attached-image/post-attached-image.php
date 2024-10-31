<?php
/*
Plugin Name: Post Attached Image
Version: 1.0.5
Plugin URI: http://dima.design.lviv.ua/post-attached-image
Description: Adds a preview image suggested by user to every post in list and a large image on the single page
Author: Dmitry Svarytsevych
Author URI: http://design.lviv.ua/

*/

### Збереження зображень #####################################################################################################################

// Додати необхiднi поля в форму створення запису
add_action('admin_head', 'poster_admin_head');
function poster_admin_head() {
 	echo '<script type="text/javascript" src="' . get_bloginfo('wpurl') .'/wp-content/plugins/post-attached-image/multifile.js"></script>
	';
}

add_action("edit_form_advanced","poster_add_form");
function poster_add_form() {
  global $wpdb, $post;

//	echo '
?>
<div id="advancedstuff" class="dbx-group">

    <script type="text/javascript">
		var form = document.getElementById('post');
		//form.enctype = "multipart/form-data"; //FireFox, Opera, et al
		form.encoding = "multipart/form-data";
		form.setAttribute('enctype', 'multipart/form-data');
		</script>

<div class="dbx-b-ox-wrapper">
<fieldset id="trackbacksdiv" class="dbx-box">
<div class="dbx-h-andle-wrapper">
<h3 class="dbx-handle">Post image:</h3>
</div>
<div class="dbx-c-ontent-wrapper">
<div class="dbx-content">
		<div style="padding-left: 10px;">
		<strong>File:</strong>
		<input enctype="multipart/form-data" id="pthumb_element" type="file" name="pthumb_1" />
		
		<div id="files_list">
			<h3>Selected Files: <small>You can upload up to 5 photos at once</small></h3>
		</div>
			<script type="text/javascript">
				var multi_selector = new pMultiSelector( document.getElementById( 'files_list' ), 5 );
				multi_selector.addElement( document.getElementById( 'pthumb_element' ) );
		</script>
		</div>
</div>
</div>
</fieldset>
</div>

</div>
<?
//';

  return true;
}

// Зберегти зображення
add_action("save_post","poster_savedata");
function poster_savedata($id) {
  global $wpdb, $post, $HTTP_POST_FILES, $_POST;
  $post_files = $HTTP_POST_FILES;

  reset($post_files);
  $ret = true;
  
	while (list($file_key, $file_item) = each ($post_files)) { 
		if (strstr($file_key,'pthumb_') && $file_item['tmp_name'] != '' && $file_item['tmp_name'] == 0) {
			$farr['tmp_name'] = $file_item['tmp_name'];
			if ($file_key == 'pthumb_0') {
				$farr['name'] = $id;
			}else{
				$farr['name'] = $id.'_'.substr($file_key,-1);
			}
			$farr['type'] = $file_item['type'];
		
			create_tnail($farr, get_option('pthumb_width'), get_option('pthumb_height'), get_option('pthumb_jpeg'), '', get_option('pthumb_proportion'), false);
			create_tnail($farr, get_option('pthumb_thwidth'), get_option('pthumb_thheight'), get_option('pthumb_thjpeg'), get_option('pthumb_prefix'), get_option('pthumb_thproportion'), true);
		}else{
			$ret = false;
		}
	}
	
	return $ret;
}


### Параметри плагiна #####################################################################################################################

// Create the default path, status and script location (should only execute once)
add_option(pthumb_prefix, "thumb_", 'File prefix in thumbnails: prefixXXXX.JPG');
add_option(pthumb_sufix, "pos", 'File suffix in thumbnails: XXXXXsuffix.JPG');
add_option(pthumb_width, 350, 'Width of the large image');
add_option(pthumb_height, 300, 'Height of the large image');
add_option(pthumb_jpeg, 75, 'Quality of the image');
add_option(pthumb_thwidth, 150, 'Width of the thumbnail image');
add_option(pthumb_thheight, 100, 'Height of the thumbnail image');
add_option(pthumb_thjpeg, 75, 'Quality of the image');
add_option(pthumb_proportion, "width", 'Method of resizing images');
add_option(pthumb_thproportion, "smart", 'Method of resizing images');
add_option(pthumb_display, "<div style=\"width: ".get_option('pthumb_thwidth')."px; float: left\">{image}</div><div style=\"float: left\">{text}</div><br clear=\"all\" />", 'Template of displaying images');
add_option(pthumb_display_single, "<div align=\"center\">{image}</div>{text}", 'Template of displaying images on single page');

add_action('admin_menu', 'add_poster_thumb_option_page');
function add_poster_thumb_option_page() {
	// Hook in the options page function
	add_options_page('Post Thumbnails', 'P-Image', 8, __FILE__, 'poster_thumb_options_page');
}

function poster_thumb_options_page() {
		
	// If we are a postback, store the options
 	if (isset($_POST['info_update'])) {
 		
		update_option(pthumb_prefix, $_POST['pthumb_prefix']);
		update_option(pthumb_sufix, $_POST['pthumb_sufix']);
		if (intval($_POST['pthumb_width']) > 10) update_option(pthumb_width, $_POST['pthumb_width']);
		if (intval($_POST['pthumb_height']) > 10) update_option(pthumb_height, $_POST['pthumb_height']);
		if (intval($_POST['pthumb_jpeg']) > 10) update_option(pthumb_jpeg, $_POST['pthumb_jpeg']);
		if (intval($_POST['pthumb_thwidth']) > 10) update_option(pthumb_thwidth, $_POST['pthumb_thwidth']);
		if (intval($_POST['pthumb_thheight']) > 10) update_option(pthumb_thheight, $_POST['pthumb_thheight']);
		if (intval($_POST['pthumb_thjpeg']) > 10) update_option(pthumb_thjpeg, $_POST['pthumb_thjpeg']);
		update_option(pthumb_proportion, $_POST['pthumb_proportion']);
		update_option(pthumb_thproportion, $_POST['pthumb_thproportion']);
		$_POST['pthumb_display'] = stripslashes($_POST['pthumb_display']);
		update_option(pthumb_display, $_POST['pthumb_display']);
		$_POST['pthumb_display_single'] = stripslashes($_POST['pthumb_display_single']);
		update_option(pthumb_display_single, $_POST['pthumb_display_single']);

		// Give an updated message
		echo "<div class='updated'><p><strong>Post Image options updated</strong></p></div>";
	}

	// Output a simple options page
	?>
		<form method="post" action="options-general.php?page=post-attached-image.php">
		<div class="wrap">
			<h2>Post Thumbnails Options</h2>
			<fieldset class='options'>
				<table class="editform" cellspacing="2" cellpadding="5" width="100%">
					<tr>
						<th width="30%" valign="top" style="padding-top: 10px;">
							<label for="pthumb_prefix">File prefix:</label>
						</th>
						<td>
							<?php
							echo "<input type='text' size='50' ";
							echo "name='pthumb_prefix' ";
							echo "id='pthumb_prefix' ";
							echo "value='".get_option(pthumb_prefix)."' />\n";
							?>
						</td>
					</tr>
					<tr>
						<th valign="top" style="padding-top: 10px;">
							<label for="pthumb_sufix">File sufix:</label>
						</th>
						<td>
							<?php
							echo "<input type='text' size='50' ";
							echo "name='pthumb_sufix' ";
							echo "id='pthumb_sufix' ";
							echo "value='".get_option(pthumb_sufix)."' />\n";
							?>
						</td>
					</tr>
					<tr>
						<th valign="top" style="padding-top: 10px;">
							<label for="pthumb_display">Display template on category page:</label>
						</th>
						<td>
							<?php
							echo "<textarea rows=\"4\" style=\"width: 360px;\" ";
							echo "name='pthumb_display' ";
							echo "id='pthumb_display' ";
							echo ">".str_replace("\"","&quot;",str_replace(">","&gt;",str_replace("<","&lt;",get_option(pthumb_display))))."</textarea>\n";
							?>
						</td>
					</tr>
					<tr>
						<th valign="top" style="padding-top: 10px;">
							<label for="pthumb_display">Display template on single post page:</label>
						</th>
						<td>
							<?php
							echo "<textarea rows=\"4\" style=\"width: 360px;\" ";
							echo "name='pthumb_display_single' ";
							echo "id='pthumb_display_single' ";
							echo ">".str_replace("\"","&quot;",str_replace(">","&gt;",str_replace("<","&lt;",get_option(pthumb_display_single))))."</textarea>\n";
							?>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="submit">
							<input type='submit' name='info_update' value='Update Options' />
						</td>
					</tr>

					<tr>
						<th valign="top" style="padding-top: 15px; font-size: 1.2em;">
							Image size
						</th>
						<td></td>
					</tr>
					<tr>
						<th valign="top" style="padding-top: 10px;">
							<label for="pthumb_width">Width:</label>
						</th>
						<td>
							<?php
							echo "<input type='text' size='50' ";
							echo "name='pthumb_width' ";
							echo "id='pthumb_width' ";
							echo "value='".get_option(pthumb_width)."' />\n";
							?>
						</td>
					</tr>
					<tr>
						<th valign="top" style="padding-top: 10px;">
							<label for="pthumb_height">Height:</label>
						</th>
						<td>
							<?php
							echo "<input type='text' size='50' ";
							echo "name='pthumb_height' ";
							echo "id='pthumb_height' ";
							echo "value='".get_option(pthumb_height)."' />\n";
							?>
						</td>
					</tr>
					<tr>
						<th valign="top">
							Proportions:
						</th>
						<td>
							<?php
							echo "<label>\n";
							echo "<input type='radio' ";
							echo "name='pthumb_proportion' ";
							echo "value='smart'";
							if(get_option(pthumb_proportion) == 'smart') echo " checked='checked'";
							echo " />&nbsp;Save needed image <b>size</b> (crop image)</label><br />\n";

							echo "<label>\n";
							echo "<input type='radio' ";
							echo "name='pthumb_proportion' ";
							echo "value='width'";
							if(get_option(pthumb_proportion) == 'width') echo " checked='checked'";
							echo " />&nbsp;Save needed image <b>width</b></label><br />\n";
							
							echo "<label>\n";
							echo "<input type='radio' ";
							echo "name='pthumb_proportion' ";
							echo "value='height'";
							if(get_option(pthumb_proportion) == 'height') echo " checked='checked'";
							echo " />&nbsp;Save needed image <b>height</b></label><br />\n";
						
							?>
						</td>
					</tr>
					<tr>
						<th valign="top" style="padding-top: 10px;">
							<label for="pthumb_jpeg">Quality:</label>
						</th>
						<td>
							<?php
							echo "<input type='text' size='50' ";
							echo "name='pthumb_jpeg' ";
							echo "id='pthumb_jpeg' ";
							echo "value='".get_option(pthumb_jpeg)."' />\n";
							?>
						</td>
					</tr>
					
					<tr>
						<th valign="top" style="padding-top: 15px; font-size: 1.2em;">
							Thumbnail size
						</th>
						<td></td>
					</tr>
					<tr>
						<th valign="top" style="padding-top: 10px;">
							<label for="pthumb_thwidth">Width:</label>
						</th>
						<td>
							<?php
							echo "<input type='text' size='50' ";
							echo "name='pthumb_thwidth' ";
							echo "id='pthumb_thwidth' ";
							echo "value='".get_option(pthumb_thwidth)."' />\n";
							?>
						</td>
					</tr>
					<tr>
						<th valign="top" style="padding-top: 10px;">
							<label for="pthumb_thheight">Height:</label>
						</th>
						<td>
							<?php
							echo "<input type='text' size='50' ";
							echo "name='pthumb_thheight' ";
							echo "id='pthumb_thheight' ";
							echo "value='".get_option(pthumb_thheight)."' />\n";
							?>
						</td>
					</tr>
					<tr>
						<th valign="top">
							Proportions:
						</th>
						<td>
							<?php
							echo "<label>\n";
							echo "<input type='radio' ";
							echo "name='pthumb_thproportion' ";
							echo "value='smart'";
							if(get_option(pthumb_thproportion) == 'smart') echo " checked='checked'";
							echo " />&nbsp;Save needed image <b>size</b> (crop image)</label><br />\n";

							echo "<label>\n";
							echo "<input type='radio' ";
							echo "name='pthumb_thproportion' ";
							echo "value='width'";
							if(get_option(pthumb_thproportion) == 'width') echo " checked='checked'";
							echo " />&nbsp;Save needed image <b>width</b></label><br />\n";
							
							echo "<label>\n";
							echo "<input type='radio' ";
							echo "name='pthumb_thproportion' ";
							echo "value='height'";
							if(get_option(pthumb_thproportion) == 'height') echo " checked='checked'";
							echo " />&nbsp;Save needed image <b>height</b></label><br />\n";
						
							?>
						</td>
					</tr>


					<tr>
						<th valign="top" style="padding-top: 10px;">
							<label for="pthumb_thjpeg">Quality:</label>
						</th>
						<td>
							<?php
							echo "<input type='text' size='50' ";
							echo "name='pthumb_thjpeg' ";
							echo "id='pthumb_thjpeg' ";
							echo "value='".get_option(pthumb_thjpeg)."' />\n";
							?>
						</td>
					</tr>
					
					<tr>
						<td colspan="2" class="submit">
							<input type='submit' name='info_update' value='Update Options' />
						</td>
					</tr>
				</table>
			</fieldset>
		</div>
		</form>
	<?php
}


### Обробка зображень #####################################################################################################################

function _sysB_chkgd2()
 {
 	$rep=false;
 	if(isset($GLOBALS["gBGDVersion"])) {
 		$rep=$GLOBALS["gBGDVersion"];
 	} else {
 		if(function_exists("gd_info")) {
 			$gdver=gd_info();
 			$GLOBALS["gBGDVersion"]=$rep=preg_replace("/^(.*)(\d+?\.?\d+?\.?)(.*)$/", "\\2", $gdver["GD Version"]);
 		} else {
 			$arr=get_loaded_extensions();
 			if(in_array("gd", $arr) and $im=@imagecreatetruecolor(1,1)) {
 				imagedestroy($im);
 				$GLOBALS["gBGDVersion"]=$rep="2.0";
 			}elseif (in_array("gd", $arr) and $im=@imagecreate(1,1)){
 				imagedestroy($im);
 				$GLOBALS["gBGDVersion"]=$rep="1.6";
 			}
 		}
 	}
 	return $GLOBALS["gBGDVersion"];
 }

function create_tnail($file, $w, $h, $quality, $name_prefix, $resize_type, $unlink=false){
	$GLOBALS["gBGDVersion"]=_sysB_chkgd2();
	if(intval($GLOBALS["gBGDVersion"])>=2){
		$resample_function="imagecopyresampled";
		$create_function="imageCreateTrueColor";
	}else{
		$resample_function="imagecopyresized";
		$create_function="imageCreate";
	}

	$name_sufix = get_option('pthumb_sufix');
	$uploads = wp_upload_dir();

	$info=getimagesize($file["tmp_name"]);
	switch ($info[2]) {
		case 1:
		$img=imagecreatefromgif($file["tmp_name"]);
		break;
		case 2:
		$img=imagecreatefromjpeg($file["tmp_name"]);
		break;
		case 3:
		$img=imagecreatefrompng($file["tmp_name"]);
		break;
	}

	if((($info[0]/$info[1])<=($w/$h) || $resize_type == 'width') && $resize_type != 'height'){
		$new_w=$w;
		$new_h=(int)(($w/$info[0])*$info[1]);
		if( !@($tmp_im=$create_function($new_w, $new_h)) ) return false;
		$resample_function($tmp_im, $img, 0, 0, 0, 0, $new_w, $new_h, $info[0], $info[1]);

		imagedestroy($img);
		if ($resize_type == 'smart') {
			$im = ($create_function($w, $h));
			$offset = (int)(($new_h-$h)/2);
			ImageAlphaBlending($im, true);
			imagecopy($im, $tmp_im, 0,0,0,$offset,$w, $h);
		}else{ 
			$im = ($create_function($new_w, $new_h));
			ImageAlphaBlending($im, true);
			imagecopy($im, $tmp_im, 0,0,0,0,$new_w, $new_h);
		}
	}elseif ((($info[0]/$info[1])>($w/$h) || $resize_type == 'height') && $resize_type != 'width'){
		$new_h=$h;
		$new_w=(int)(($h/$info[1])*$info[0]);
		if( !@($tmp_im=$create_function($new_w, $new_h)) ) return false;
		$resample_function($tmp_im, $img, 0, 0, 0, 0, $new_w, $new_h, $info[0], $info[1]);

		imagedestroy($img);
		if ($resize_type == 'smart') {
			$im = ($create_function($w, $h));
			$offset = (int)(($new_w-$w)/2);
			ImageAlphaBlending($im, true);
			imagecopy($im, $tmp_im, 0,0,$offset,0,$w, $h);
		}else{ 
			$im = ($create_function($new_w, $new_h));
			ImageAlphaBlending($im, true);
			imagecopy($im, $tmp_im, 0,0,0,0,$new_w, $new_h);
		}
	}

		$filename = $uploads['path'] . '/' . $name_prefix . $file["name"] . $name_sufix . '.jpg';
		if (file_exists($filename)) unlink($filename);
		imagejpeg($im, $filename, $quality);

		imagedestroy($im);
		imagedestroy($tmp_im);

		if ($unlink) @unlink($file["tmp_name"]);

	return array("init_width"=>$info[0], "init_height"=>$info[1]);
}

add_filter('the_content', 'poster_thumb_display', 10);
add_filter('the_excerpt', 'poster_thumb_display', 10);
function poster_thumb_display ($content) {
	global $id;
	global $post;
	
	$file = $id . get_option('pthumb_sufix') . '.jpg';
	$uploads = poster_upload_dir($post->post_modified);
	if (is_single()) {
			$image = '';
			$rep = false;
		if (file_exists($uploads['path'].'/'.$file)) {
			$image = '<img src="'.$uploads['url'].'/'.$file.'" alt="'.the_title('','',false).'" title="'.the_title('','',false).'" />';
			$rep = true;
		}
		for ($im = 1; $im <=5; $im++) {
			$file = $id.'_'.$im.get_option('pthumb_sufix').'.jpg';
			if (file_exists($uploads['path'].'/'.$file)) {
				$image .= '<img src="'.$uploads['url'].'/'.$file.'" alt="'.the_title('','',false).' #'.$im.'" title="'.the_title('','',false).' #'.$im.'" />';
				$rep = true;
			}
		}
		if ($rep) {
			$rpl = array($image,$content);
			$trpl = array('{image}','{text}');
			$content = str_replace($trpl,$rpl,get_option('pthumb_display_single'));
		}
	}else{
		if (file_exists($uploads['path'].'/'.get_option('pthumb_prefix').$file)) {
			$image = '<a href="'.get_permalink().'" title="'.the_title('','',false).'"><img src="'.$uploads['url'].'/'.get_option('pthumb_prefix').$file.'" alt="'.the_title('','',false).'" title="'.the_title('','',false).'" /></a>';
			$rpl = array($image,$content);
			$trpl = array('{image}','{text}');
    	$content = str_replace($trpl,$rpl,get_option('pthumb_display'));
		}
	}
	
	return $content;
}

function poster_upload_dir($time) {
	global $post;

	$siteurl = get_option('siteurl');
	//prepend ABSPATH to $dir and $siteurl to $url if they're not already there
	$path = str_replace(ABSPATH, '', trim(get_option('upload_path')));
	$dir = ABSPATH . $path;
	$url = trailingslashit($siteurl) . $path;

	if ( $dir == ABSPATH ) { //the option was empty
		$dir = ABSPATH . 'wp-content/uploads';
	}

	if ( defined('UPLOADS') ) {
		$dir = ABSPATH . UPLOADS;
		$url = trailingslashit($siteurl) . UPLOADS;
	}

	if ( get_option('uploads_use_yearmonth_folders')) {
		// Generate the yearly and monthly dirs
		$y = substr( $time, 0, 4 );
		$m = substr( $time, 5, 2 );
		$dir = $dir . "/$y/$m";
		$url = $url . "/$y/$m";
	}

	// Make sure we have an uploads dir
	if ( ! wp_mkdir_p( $dir ) ) {
		$message = sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?'), $dir);
		return array('error' => $message);
	}

		$uploads = array('path' => $dir, 'url' => $url, 'error' => false);
	return apply_filters('upload_dir', $uploads);
}

?>