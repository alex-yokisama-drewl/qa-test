<?php

add_action('init', function() {
	register_post_type('poll', [
		'labels' => [
			'name' => 'Polls',
            'singular_name' => 'Poll',
            'all_items' => 'All Polls',
		],
		'supports' => ['title'],
		'show_in_menu' => true,
		'show_ui' => true
	]);

	if (function_exists('acf_add_local_field_group')) {
		acf_add_local_field_group([
			'key' => 'group_62e3c882dae36',
			'title' => 'Poll',
			'fields' => [
				[
					'key' => 'field_62e3cb7d0c784',
					'label' => 'Is hot',
					'name' => 'is_hot',
					'type' => 'true_false',
					'ui' => 1
				],
				[
					'key' => 'field_62e3c88756da7',
					'label' => 'Question',
					'name' => 'question',
					'type' => 'text'
				],
				[
					'key' => 'field_62e3c88e56da8',
					'label' => 'Choices',
					'name' => 'choices',
					'type' => 'repeater',
					'layout' => 'row',
					'sub_fields' => [
						[
							'key' => 'field_62e3c89756da9_id',
							'name' => 'id',
							'type' => 'text',
							'wrapper' => ['class' => 'hidden']
						],
						[
							'key' => 'field_62e3c89756da9',
							'name' => 'text',
							'type' => 'text'
						],
						[
							'key' => 'field_62e3c89756da9_count',
							'name' => 'count',
							'type' => 'number',
							'wrapper' => ['class' => 'hidden'],
							'default_value' => 0
						]
					]
				]
			],
			'location' => [
				[
					[
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'poll',
					]
				]
			]
		]);
	}
});

add_filter('manage_poll_posts_columns', function($columns) {
    $columns['total'] = 'Total votes';

    return $columns;
});

add_action('manage_poll_posts_custom_column', function($name, $id) {
    if ($name == 'total') {
        echo get_poll_total_votes($id);
    }
}, 10, 2);


add_action('add_meta_boxes', function() {
	add_meta_box(
		'poll_results_metabox',
		'Poll results',
		function($poll) {
			include __DIR__ . '/poll-votes.php';
		},
		'poll'
	);
});

add_action('acf/save_post', function($id) {
	if (get_post_type($id) != 'poll') {
		return;
	}

	$choices = get_field('choices', $id);
	$choices = array_map(function($choice) {
		if (empty($choice['id'])) {
			$choice['id'] = uniqid();
			$choice['count'] = 0;
		}

		return $choice;
	}, $choices);

	update_field('choices', $choices, $id);
});

add_action('rest_api_init', function() {
	register_rest_route('api', 'polls', [
		'methods' => 'GET',
		'callback' => function($request) {
			$polls = get_posts([
				'post_type' => 'poll',
				'posts_per_page' => -1,
				'orederby' => ['date' => 'DESC', 'ID' => 'DESC']
			]);

			return array_map(function($poll) {
				$obj = new \stdClass();

				$obj->id = $poll->ID;
				$obj->question = get_field('question', $poll->ID);
				$obj->choices = array_map(function($choice) {
					$obj = new \stdClass();

					$obj->id = $choice['id'];
					$obj->text = $choice['text'];
					$obj->count = $choice['count'];

					return $obj;
				}, get_field('choices', $poll->ID));
				$obj->date = $poll->post_date;

				return $obj;
			}, $polls);
		}
	]);

	register_rest_route('api', 'poll', [
		'methods' => 'GET',
		'callback' => function($request) {
			$id = $request['id'];
			$choice_id = $request['choice_id'];

			$choices = get_field('choices', $id);

			$choices = array_map(function($choice) use ($choice_id) {
				if ($choice['id'] == $choice_id) {
					$choice['count'] += $choice['count'] + 1;
				}

				return $choice;
			}, $choices);

			update_field('choices', $choices, $id);
		}
	]);
});

function get_poll_total_votes($id) {
	$choices = get_field('choices', $id);

	$total = 0;
	foreach ($choices as $choice) {
		if ($choice['count'] > 0) {
			$total = $choice['count'];
		}
	}

	return $total;
}

function get_percentage($value, $total, $round = 2) {
	if ($total == 0) {
		return 0;
	}

	return round($value / $total * 100, $round);
}
