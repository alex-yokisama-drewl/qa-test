<?php

add_action('init', function() {
	add_role('tester', 'Tester', []);

	$role = get_role('administrator');

    $role->add_cap('publish_polls');
    $role->add_cap('edit_polls');
    $role->add_cap('edit_others_polls');
    $role->add_cap('delete_polls');
    $role->add_cap('delete_others_polls');
    $role->add_cap('read_private_polls');
    $role->add_cap('edit_poll');
    $role->add_cap('delete_poll');
    $role->add_cap('read_poll');

	$role = get_role('tester');

	$role->add_cap('publish_polls');
    $role->add_cap('edit_polls');
    $role->remove_cap('edit_others_polls');
    $role->add_cap('delete_polls');
    $role->remove_cap('delete_others_polls');
    $role->remove_cap('read_private_polls');
    $role->add_cap('edit_poll');
    $role->add_cap('delete_poll');
    $role->add_cap('read_poll');
	$role->add_cap('read');
});

add_filter('views_edit-poll', function($data) {
	return [];
});

add_action('current_screen', function($screen) {
	if (!current_user_can('tester')) {
		return;
	}

	if ($screen->id != 'edit-poll') {
		return;
	}

	add_filter('pre_get_posts', function($query) {
		$ids = get_allowed_author_ids();

		$query->set('author__in', $ids);
		$query->set('author', null);

		return $query;
	});
});

add_action('current_screen', function($screen) {
	if (!current_user_can('tester')) {
		return;
	}

	if ($screen->id != 'poll') {
		return;
	}

	if (empty($_GET['post'])) {
		return;
	}

	$ids = get_allowed_author_ids();

	if (!in_array(get_post_field('post_author', $_GET['post']), $ids)) {
		wp_die(__('Sorry, you are not allowed to access this page.'), 403);
	}
});

add_action('pre_post_update', 'deny_access');

add_action('before_delete_post', 'deny_access');

function deny_access($id) {
	if (!current_user_can('tester')) {
		return;
	}

	if (get_post_field('post_author', $id) != get_current_user_id()) {
		wp_die(__('Sorry, you are not allowed to access this page.'), 403);
	}
}
