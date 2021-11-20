<?php if ( ! defined( 'W4OS_ADMIN' ) ) die;

function w4os_get_users_ids_and_uuids() {
		global $wpdb, $w4osdb;

		$GridAccounts = $w4osdb->get_results("SELECT Email as email, PrincipalID, FirstName, LastName FROM UserAccounts
			WHERE active = 1
			AND Email is not NULL AND Email != ''
			AND FirstName != '" . get_option('w4os_model_firstname') . "'
			AND LastName != '" . get_option('w4os_model_lastname') . "'
			", OBJECT_K);
		foreach (	$GridAccounts as $key => $row ) {
			if(empty($row->email)) continue;
			// $GridAccounts[$row->email] = (array)$row;
			$accounts[$key] = (array)$row;
		}

		$WPGridAccounts = $wpdb->get_results("SELECT user_email as email, ID as user_id, meta_value AS w4os_uuid
			FROM $wpdb->users LEFT JOIN $wpdb->usermeta
			ON ID = user_id AND meta_key = 'w4os_uuid' AND meta_value != '' AND meta_value != '" . W4OS_NULL_KEY . "'", OBJECT_K);

		foreach (	$WPGridAccounts as $key => $row ) {
			if(empty($row->email)) continue;
			// $WPGridAccounts[$row->email] = (array)$row;
			if (empty($accounts[$row->email])) {
				$accounts[$row->email] = (array)$row;
			} else {
				$accounts[$row->email] =  array_merge( $accounts[$row->email], (array)$row );
			}
		}

		return $accounts;
}

function w4os_count_users() {
  global $wpdb, $w4osdb;

  $accounts = w4os_get_users_ids_and_uuids();

  $count['wp_users'] = count_users()['total_users'];
  $count['grid_accounts'] = 0;
  $count['wp_linked'] = 0;
  $count['wp_only'] = NULL;
  $count['grid_only'] = NULL;
	$count['sync'] = 0;
  foreach ($accounts as $key => $account) {
    if(!isset($account['PrincipalID']) || w4os_empty($account['PrincipalID'])) $account['PrincipalID'] = NULL;
    else $count['grid_accounts']++;
    if(isset($account['w4os_uuid']) && w4os_empty($account['w4os_uuid'])) $account['w4os_uuid'] = NULL;
		if(!w4os_empty($account['PrincipalID']) &! w4os_empty($account['w4os_uuid']) ) {
			$count['wp_linked']++;
			if($account['PrincipalID'] == $account['w4os_uuid']) $count['sync']++;
			else $count['wp_only']++;
		}
    else if($account['PrincipalID']) $count['grid_only'] += 1;
  }

  $count['models'] = $w4osdb->get_var("SELECT count(*) FROM UserAccounts
  WHERE FirstName = '" . get_option('w4os_model_firstname') . "'
  OR LastName = '" . get_option('w4os_model_lastname') . "'
  ");

  $count['tech'] = $w4osdb->get_var("SELECT count(*) FROM UserAccounts
  WHERE (Email IS NULL OR Email = '')
  AND FirstName != '" . get_option('w4os_model_firstname') . "'
  AND LastName != '" . get_option('w4os_model_lastname') . "'
  AND FirstName != 'GRID'
  AND LastName != 'SERVICE'
  ");
  return $count;
}


function w4os_sync_users() {
  $accounts = w4os_get_users_ids_and_uuids();
	$messages=array();
	$users_created=[];
	$users_updated=[];
	foreach ($accounts as $key => $account) {
    // First cleanup NULL_KEY and other empty UUIDs
    if(!isset($account['PrincipalID']) || w4os_empty($account['PrincipalID'])) $account['PrincipalID'] = NULL;
    if(!isset($account['w4os_uuid']) || w4os_empty($account['w4os_uuid'])) $account['w4os_uuid'] = NULL;

    if( isset($account['PrincipalID']) &! w4os_empty($account['PrincipalID']) ) {
			if ( $account['PrincipalID'] == $account['w4os_uuid'] ) {
				// already linked, no action needed
			} else if ( isset($account['user_id']) &! empty($account['user_id']) ) {
				// wrong reference, but an avatar exists for this WP user, replace reference
				$result = update_user_meta( $account['user_id'], 'w4os_uuid', $account['PrincipalID'] );
				if($result) $users_updated[] = sprintf('%s %s (%s)', $account['FirstName'], $account['LastName'], $account['email']);
				else $errors[] = '<p class=error>' .  sprintf(__('Error while updating %s %s (%s) %s', 'w4os'), $account['FirstName'], $account['LastName'], $account['email'], $result) . '</p>';
			} else {
				// No user with this email, create one
				$newid = wp_insert_user(array(
					'user_login' => strtolower($account['FirstName'] . "." . $account['LastName']),
					// 'user_pass' => wp_generate_password(),
					'user_email' => $account['email'],
					'first_name' => $account['FirstName'],
					'last_name' => $account['LastName'],
					'role' => 'grid_user',
					'display_name' => trim($account['FirstName'] . ' ' . $account['LastName']),
				));
				if(is_wp_error( $newid )) {
					$error_message = $newid->get_error_message();
				} else {
					$result = update_user_meta( $newid, 'w4os_uuid', $account['PrincipalID'] );
					if($result) {
						update_user_meta( $newid, 'w4os_firstname', $account['FirstName'] );
						update_user_meta( $newid, 'w4os_lastname', $account['LastName'] );
						update_user_meta( $newid, 'w4os_avatarname', trim($account['FirstName'] . ' ' . $account['LastName']) );
						// update_user_meta( $user->ID, 'w4os_profileimage', $account['profileImage'] );
						// $user = get_user_by('ID', $newid);
						// w4os_profile_sync($user);
						$users_created[] = sprintf('%s %s (%s)', $account['FirstName'], $account['LastName'], $account['email']);

					} else $errors[] = '<p class=error>' .  sprintf(__('Error while updating newly created user %s for %s %s (%s) %s', 'w4os'), $newid, $account['FirstName'], $account['LastName'], $account['email'], $result) . '</p>';
					// $messages[] = "new user $user->ID created";
				}
			}
		}
	}
			// 	if($user) {
			// 		$result = w4os_profile_sync($user, $account['PrincipalID']);
			// 		if($result != $account['PrincipalID']) {
			// 			$error_message = __("User created but could not link it.", 'w4os');
			// 		}
			// 	} else {
			// 		$error_message = __("User was created but could not be retrieved. Weird. Should not happen.", 'w4os');
			// 	}
			// if( isset($account['user_id']) && is_numeric($account['user_id']) && $account['user_id'] > 0 ) {
	// 		$email = $account['email'];
			// $user = get_user_by('email', $email);
			// if($user) {
	// 			// If there is a user with this email
	// 			$account['user_id'] = $user->ID;
	// 			$result = w4os_profile_sync($user, $account['PrincipalID']);
 	// 			if($result != $account['PrincipalID']) {
	// 				$error_message = __("Error while trying to link WP user with same email address.", 'w4os');
	// 		} else {
	// 				update_user_meta( $account['user_id'], 'w4os_uuid', $account['PrincipalID'] );
	// 				$users_created[] = sprintf('%s %s (%s)', $account['FirstName'], $account['LastName'], $account['email']);
	// 			} else {
	// 				else $error_message = 'grid side ' . $account['PrincipalID'] . ' wp side ' . $account['w4os_uuid'];
	// 			}
	// 		}
	// 		if(isset($error_message)) {
	// 			$errors[] = sprintf(__('Error while syncing avatar %s (%s): %s', 'w4os'), $account['FirstName'] . " " . $account['LastName'], $account['email'], $error_message);
	// 		} else {
	// 			$users_created[] = sprintf('%s %s (%s)', $account['FirstName'], $account['LastName'], $account['email']);
	// 		}
  //   } else {
	// 		delete_user_meta( $account['user_id'], 'w4os_uuid' );
	// 	  delete_user_meta( $account['user_id'], 'w4os_firstname' );
	// 	  delete_user_meta( $account['user_id'], 'w4os_lastname' );
	// 	  delete_user_meta( $account['user_id'], 'w4os_avatarname' );
	// 	  delete_user_meta( $account['user_id'], 'w4os_profileimage' );
	//
  //     // no such avatar, delete reference
  //     $users_dereferenced[] = $account['user_id'];
	if(!empty($users_updated)) $messages[] = sprintf(_n(
		'%d reference updated',
		'%d references updated',
		count($users_updated),
		'w4os',
	), count($users_updated)) . ': ' . join(', ', $users_updated);
	if(!empty($users_created)) $messages[] = '<p>' . sprintf(_n(
    '%d new WordPress account created',
    '%d new WordPress accounts created',
    count($users_created),
    'w4os',
  ), count($users_created)) . ': ' . join(', ', $users_created);
  if(!empty($users_dereferenced)) $messages[] = sprintf(_n(
    '%d broken reference removed',
    '%d broken references removed',
    count($users_dereferenced),
    'w4os',
  ), count($users_dereferenced));

	// // add_action('admin_init', 'w4os_profile_sync_all');
	w4os_profile_sync_all();
	update_option('w4os_sync_users', NULL);
	// // return '<pre>' . print_r($messages, true) . '</pre>';
	if(!empty($errors)) $messages[] = '<p class=sync-errors><ul><li>' . join('</li><li>', $errors) . '</p>';
	// $messages[] = w4os_array2table($accounts, 'accounts', 2);
	if(!empty($messages)) return '<div class=messages><p>' . join('</p><p>', $messages) . '</div>';
}