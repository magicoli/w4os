<?php if ( ! defined( 'W4OS_PLUGIN' ) ) die;

function w4os_array2table($array, $class="", $level = 1 ) {
	if(empty($array)) return;
	if($level == 1) {
		$result="";
		foreach($array as $key => $value) {
			$result.="<tr><td class=gridvar>" . __($key, 'w4os') . "</td><td class=gridvalue>$value</td></tr>";
		}
		if(!empty($result)) {
			$result="<table class='$class'>$result</table>";
		}
		return $result;
	}
	if($level == 2) {
		$html = "<table class=$class>";
		$array_head = $array;
		$array_head = array_shift($array_head);
		if(is_array($array_head)) {
			$html.='<tr><th></th>';
			foreach($array_head as $column => $value) {
				$html.="<th>$column</th>";
			}
			$html.='</tr>';
		}
		foreach($array as $key => $row) {
			if(is_array($row)) {
				$html.="<tr><th>$key</th>";
				foreach($row as $column => $value) {
					$html.="<td class=col-$column>$value</td>";
				}
				$html.='</tr>';
			}
		}
		$html .= '</table>';
		return $html;
	}
}

function w4os_notice ($message, $status="") {
  echo "<div class='notice notice-$status'><p>$message</p></div>";
}

function w4os_gen_uuid() {
 $uuid = array(
  'time_low'  => 0,
  'time_mid'  => 0,
  'time_hi'  => 0,
  'clock_seq_hi' => 0,
  'clock_seq_low' => 0,
  'node'   => array()
 );

 $uuid['time_low'] = mt_rand(0, 0xffff) + (mt_rand(0, 0xffff) << 16);
 $uuid['time_mid'] = mt_rand(0, 0xffff);
 $uuid['time_hi'] = (4 << 12) | (mt_rand(0, 0x1000));
 $uuid['clock_seq_hi'] = (1 << 7) | (mt_rand(0, 128));
 $uuid['clock_seq_low'] = mt_rand(0, 255);

 for ($i = 0; $i < 6; $i++) {
  $uuid['node'][$i] = mt_rand(0, 255);
 }

 $uuid = sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
  $uuid['time_low'],
  $uuid['time_mid'],
  $uuid['time_hi'],
  $uuid['clock_seq_hi'],
  $uuid['clock_seq_low'],
  $uuid['node'][0],
  $uuid['node'][1],
  $uuid['node'][2],
  $uuid['node'][3],
  $uuid['node'][4],
  $uuid['node'][5]
 );

 return $uuid;
}

function w4os_admin_notice($notice, $class='info', $dismissible=true ) {
  if(empty($notice)) return;
  if($dismissible) $is_dismissible = 'is-dismissible';
	if(is_admin() && wp_get_current_user()) {
  add_action( 'admin_notices', function() use ($notice, $class, $is_dismissible) {
    ?>
    <div class="notice notice-<?=$class?> <?=$is_dismissible?>">
        <p><strong><?php echo W4OS_PLUGIN_NAME; ?></strong>: <?php _e( $notice, 'band-tools' ); ?></p>
    </div>
    <?php
  } );
	} else {
		w4os_transient_admin_notice($notice, $class, $dismissible, __FUNCTION__);
	}
}

function w4os_transient_admin_notice( $notice, $class='info', $dismissible=true, $key = NULL ) {
	$transient_key = sanitize_title(W4OS_PLUGIN_NAME . '_w4os_transient_admin_notices');

	$queue = get_transient( $transient_key );

	if(!is_array($queue)) $queue = array($queue);
	$queue[] = array('notice' => $notice, 'class' => $class, 'dismissible' => $dissmissible);
	set_transient( $transient_key, $queue );
}

function w4os_get_transient_admin_notices() {
	if(!is_admin()) return;
	$transient_key = sanitize_title(W4OS_PLUGIN_NAME . '_w4os_transient_admin_notices');
	$queue = get_transient( $transient_key );
	if(!is_array($queue)) $queue = array($queue);
	foreach($queue as $key => $notice) {
		if(!is_array($notice)) continue;
		w4os_admin_notice($notice['notice'], $notice['class'], $notice['dismissible'] );
	}
	delete_transient( $transient_key );
}
add_action('admin_head', 'w4os_get_transient_admin_notices');

function w4os_fast_xml($url) {
	// Exit silently if required php modules are missing
	if ( ! function_exists('curl_init') ) return NULL;
	if ( ! function_exists('simplexml_load_string') ) return NULL;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$html = curl_exec($ch);
	curl_close($ch);
	$xml = simplexml_load_string($html);
	return $xml;
}

function w4os_get_grid_info($rechecknow = false) {
	$grid_info = get_option('w4os_grid_info');

	if($rechecknow || get_option('w4os_check_urls_now'))
	return w4os_update_grid_info(true);

	if(!empty($grid_info))
	return json_decode($grid_info, true);

	return w4os_update_grid_info();
}

function w4os_update_grid_info($rechecknow = false) {
	if(defined('W4OS_GRID_INFO_CHECKED') &! $rechecknow) return get_option('w4os_grid_info');
	define('W4OS_GRID_INFO_CHECKED', true);
	$local_uri = 'http://localhost:8002';
	$check_login_uri = ( get_option('w4os_login_uri') ) ? 'http://' . get_option('w4os_login_uri') : $local_uri ;
	$check_login_uri = preg_replace('+http://http+', 'http', $check_login_uri);
	// $xml = simplexml_load_file($check_login_uri . '/get_grid_info');
	$xml = w4os_fast_xml($check_login_uri . '/get_grid_info');

	if(!$xml) return false;
	if($check_login_uri == $local_uri) w4os_admin_notice(__('A local Robust server has been found. Please check Login URI and Grid name configuration.', 'w4os'), 'success');

	$grid_info = (array) $xml;
	if ( get_option('w4os_profile_page' ) == 'provide' && empty($grid_info['profile'])) $grid_info['profile'] = W4OS_PROFILE_URL;
	if ( ! empty($grid_info['login']) ) update_option('w4os_login_uri', preg_replace('+/*$+', '', preg_replace('+https*://+', '', $grid_info['login'])));
	if ( ! empty($grid_info['gridname']) ) update_option('w4os_grid_name', $grid_info['gridname']);

	if(isset($urls) && is_array($urls)) w4os_get_urls_statuses($urls, get_option('w4os_check_urls_now'));

	update_option('w4os_grid_info', json_encode($grid_info));
	return $grid_info;
}

function w4os_give_settings_url($message = '') {
	return sprintf(
		"<p>%s<a href='%s'>%s</a></p>",
		$message,
		get_admin_url('', 'admin.php?page=w4os_settings'),
		__("OpenSimulator settings page", 'w4os'),
	);
}

function w4os_upload_dir($subfolder='') {
	$upload = wp_upload_dir();
	$upload_dir = $upload['basedir'];
	$dirs[] = $upload_dir;
	$dirs[] = 'w4os';
	$dirs = array_merge($dirs, array_map('sanitize_file_name',explode('/',preg_replace(':/*$:','',$subfolder))));
	$upload_dir=implode('/', $dirs);
	if (! is_dir($upload_dir)) {
		mkdir( $upload_dir, 0755, true );
	}
	return $upload_dir;
}


function w4os_get_asset_url($uuid = W4OS_NULL_KEY, $format=W4OS_ASSETS_DEFAULT_FORMAT) {
  if(get_option('w4os_provide_asset_server')) {
    $upload_dir = esc_attr(wp_upload_dir()['baseurl'] ) . '/w4os/' . W4OS_ASSETS_CACHE_IMG_FOLDER;
    return "$upload_dir/$uuid.$format";
  } else if(!empty(W4OS_WEB_ASSETS_SERVER_URI)) {
    return W4OS_WEB_ASSETS_SERVER_URI . "$uuid";
  }
	return false;
}

function w4os_grid_status_text() {
	global $w4osdb;

	$status = wp_cache_get( 'gridstatus', 'w4os' );
	if (false === $status) {
		// $cached="uncached";
		if($w4osdb -> check_connection())
		{
			$lastmonth=time() - 30*86400;

			$urlinfo=explode(":", get_option('w4os_login_uri'));
			$host=$urlinfo['0'];
			$port=$urlinfo['1'];
			$fp = @fsockopen($host, $port, $errno, $errstr, 1.0);
			if ($fp) {
				$gridonline = __("Yes", 'w4os' );
			} else {
				$gridonline = __("No", 'w4os' );
			}
			$filter="";
			if(get_option('w4os_exclude_models')) {
				$filter .= "u.FirstName != '" . get_option('w4os_model_firstname') . "'
				AND u.LastName != '" . get_option('w4os_model_lastname') . "'";
			}
			if(get_option('w4os_exclude_nomail')) {
				$filter .= " AND u.Email != ''";
			}
			if(!empty($filter)) $filter = "$filter AND ";
		}
		$status = array(
			__('Grid online', 'w4os') => $gridonline,
			__('Members', 'w4os') => number_format_i18n($w4osdb->get_var("SELECT COUNT(*)
			FROM UserAccounts as u WHERE $filter active=1" )),
			__('Active members (30 days)', 'w4os') => number_format_i18n($w4osdb->get_var("SELECT COUNT(*)
			FROM GridUser as g, UserAccounts as u WHERE $filter PrincipalID = UserID AND g.Login > $lastmonth" )),
		);
		$status[__('Members in world', 'w4os')] = number_format_i18n($w4osdb->get_var("SELECT COUNT(*)
		FROM Presence AS p, UserAccounts AS u
		WHERE $filter RegionID != '00000000-0000-0000-0000-000000000000'
		AND p.UserID = u.PrincipalID;" ));
		// 'Active citizens (30 days)' => number_format_i18n($w4osdb->get_var("SELECT COUNT(*)
		// FROM GridUser as g, UserAccounts as u WHERE g.UserID = u.PrincipalID AND Login > $lastmonth" )),
		if(!get_option('w4os_exclude_hypergrid')) {
			$status[__('Active users (30 days)', 'w4os')] = number_format_i18n($w4osdb->get_var("SELECT COUNT(*)
			FROM GridUser WHERE Login > $lastmonth" ));
			$status[__('Total users in world', 'w4os')] = number_format_i18n($w4osdb->get_var("SELECT COUNT(*)
			FROM Presence
			WHERE RegionID != '00000000-0000-0000-0000-000000000000';	"));
		}
		$status[__('Regions', 'w4os')] = number_format_i18n($w4osdb->get_var("SELECT COUNT(*)
		FROM regions"));
		$status[__('Total area', 'w4os')] = number_format_i18n($w4osdb->get_var("SELECT round(sum(sizex * sizey / 1000000),2)
		FROM regions"), 2)  . "&nbsp;km²";
		wp_cache_add( 'gridstatus', $status, 'w4os');
	}
	return $status;
}

function w4os_empty($var) {
	if (!$var) return true;
	if (empty($var)) return true;
	if ($var == W4OS_NULL_KEY) return true;
	return false;
}

function w4os_get_url_status($url, $output = NULL, $force = false) {
	if(empty($url)) {
		$status_code = '';
	} else {
		$url_transient_key = sanitize_title('w4os_url_status_' . $url);
		$status_code = ($force) ? false : get_transient( $url_transient_key );
		if( ! $status_code ) {
			$headers = @get_headers($url, true);
			$status_code = preg_replace('/.* ([0-9]+) .*/', '$1', $headers['0']);
			if(!$status_code) $status_code = 0;
			set_transient( $url_transient_key, $status_code, 3600 );
			set_transient('w4os_get_url_status_checked', time());
		}
	}
	switch (substr($status_code, 0, 1)) {
		case "":
		$status_icon='no';
		break;

		case "2":
		case "3":
		$status_icon='yes';
		$success=true;
		break;

		case "4":
		if($status_code == 418) {
			$status_icon='coffee';
			$success=false;
			break;
		}

		default:
		$status_icon='warning';
		$success=false;
	}
	if($output == 'icon') {
		return sprintf('<span class="w4os-url-status w4os-url-status-%1$s dashicons dashicons-%2$s"></span>', $status_code, $status_icon);
	} else if($output == 'boolean') {
		return (!empty($success)) ? $success : NULL;
	} else {
		return $status_code;
	}
}

function w4os_get_urls_statuses($urls = array(), $force = false) {
	set_transient('w4os_get_url_status_checked', time());
	// Avoid concurrent checks
	if ( get_transient( 'w4os_get_urls_statuses_lock' ) ) return;
	if($force) {
		set_transient( 'w4os_get_urls_statuses_lock', true, 3600 );
		set_transient('w4os_get_url_status_checked', time());
	}

	if(is_array($urls)) foreach($urls as $key => $url) {
		if(esc_url_raw($url) == $url) {
			w4os_get_url_status($url, NULL, $force);
		}
	}
	if($force) delete_transient( 'w4os_get_urls_statuses_lock' );
}

function register_w4os_get_urls_statuses_async_cron()
{
	if ( false === as_next_scheduled_action( 'w4os_get_urls_statuses' ) &! get_transient( 'w4os_get_urls_statuses_lock') ) {
		as_schedule_cron_action(time(), '*/5 * * * *', 'w4os_get_urls_statuses');
	}
}
add_action('init','register_w4os_get_urls_statuses_async_cron');

function w4os_grid_login_uri() {
	if(defined('W4OS_GRID_LOGIN_URI')) return W4OS_GRID_LOGIN_URI;
	return 'http://' . esc_attr(get_option('w4os_login_uri'));
}

function w4os_grid_running() {
	$url = w4os_grid_login_uri() . '/get_grid_info';
	$headers = @get_headers($url, true);
	$status_code = preg_replace('/.* ([0-9]+) .*/', '$1', $headers['0']);
	return ($status_code == 200);
}

function w4os_status_icon($bool = NULL) {
	if($bool == true) $status_icon = 'yes';
	else if ($bool == false) $status_icon = 'warning';
	else $status_icon = 'no';
	return sprintf('<span class="w4os-url-status w4os-url-status-%1$s dashicons dashicons-%1$s"></span>', $status_icon);
}

function w4os_format_ini($array) {
	if(empty($array)) return;
	$content = '<div class=iniconfig>';
	foreach($array as $inifile => $sections) {
		$content .= '<p class="inifile dashicons-before dashicons-media-text">';
		$content .= sprintf(__('%s', 'w4os'), $inifile);
		$content .= '<pre>';
		foreach($sections as $section => $params) {
			$content.= "$section<br>";
			foreach($params as $param => $value) {
				$content .= "  $param = $value";
			}
		}
		$content .= '</pre></p>';
	}
	return $content;
}
