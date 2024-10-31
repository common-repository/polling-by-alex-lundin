<?php

if (!function_exists('asl_polling_db_table_name')) {
	function asl_polling_db_table_name()
	{
		return 'asl_polling_items';
	}
}

if (!function_exists('asl_polling_admin_role')) {
	function asl_polling_admin_role()
	{
		if (current_user_can('administrator')) {
			return 'administrator';
		}
		$roles = apply_filters('asl_polling_admin_role', array('administrator'));
		if (is_string($roles)) {
			$roles = array($roles);
		}
		foreach ($roles as $role) {
			if (current_user_can($role)) {
				return $role;
			}
		}
		return false;
	}
}

/**
 * Prints admin styles
 */
function aslPollsAdminPrintStyles() {
	add_action('admin_print_styles', function () {
		?>
		<style>
            #adminmenu #toplevel_page_asl_polling li.asl_polling_help:before {
                background: #b4b9be;
                content: "";
                display: block;
                height: 1px;
                margin: 5px auto 0;
                width: calc(100% - 24px);
                opacity: .4;
            }
		</style>
		<?php
	});
}

if (!function_exists('asl_polling_get_data_provider')) {
    function asl_polling_get_data_provider($pollId) {
        $provider = get_post_meta($pollId, '_asl_polling_data_provider', true);
        if (!$provider) {
            $provider = 'default';
        }
        return $provider;
    }
}

function get_item( $id ) {
	global $wpdb;
	$dbName      = $wpdb->prefix . asl_polling_db_table_name();
	$headPost    = $wpdb->get_results( $wpdb->prepare( "SELECT `post_title`, `post_content` FROM $wpdb->posts WHERE id = %d;", $id ) );
	$contentPost = $wpdb->get_results( $wpdb->prepare("SELECT `id`, `moderate`, `rating`, `value`, `settings` FROM $dbName WHERE `item_id` = %d;", $id ));

	$response = [
		'id'          => $id,
		'name'        => $headPost[0]->post_title,
		'description' => $headPost[0]->post_content,
	];

	foreach ( $contentPost as $item ) {

		if ( $item->settings === 'headPlus' ) {
			$response['headPlus'] = $item->value;
		}

		if ( $item->settings === 'headMinus' ) {
			$response['headMinus'] = $item->value;
		}

		if ( $item->settings === 'minus' ) {
			$response['minuses'][] = [
				'position' => $item->settings,
				'rating'   => $item->rating,
				'text'     => $item->value,
				'publish'  => $item->moderate === '1',
				'id'       => $item->id
			];
		}

		if ( $item->settings === 'plus' ) {
			$response['pluses'][] = [
				'position' => $item->settings,
				'rating'   => $item->rating,
				'text'     => $item->value,
				'publish'  => $item->moderate === '1',
				'id'       => $item->id
			];
		}
	}

	return $response;
}

