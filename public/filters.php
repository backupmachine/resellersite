<?php

	$purchase_request_filters = array
	(
		"prkey" => array
		(
				"filter"=>FILTER_SANITIZE_ENCODED,
				"options"=>array
				(
						"min_range"=>10,
						"max_range"=>40
				)
		),
	);
	
	$cancellation_request_filters = array
	(
			"pckey" => array
			(
					"filter"=>FILTER_SANITIZE_ENCODED,
					"options"=>array
					(
							"min_range"=>10,
							"max_range"=>40
					)
			),
	);
	
	$purchase_request_post_filters = array
	(
		"selected_package_definition" => array
		(
				"filter"=>FILTER_SANITIZE_ENCODED,
				"options"=>array
				(
						"min_range"=>2,
						"max_range"=>20
				)					
		)	
	);

	$signup_filters = array
	(
		"first_name" => array
		(
				"filter"=>FILTER_SANITIZE_STRING,
				"options"=>array
				(
						"min_range"=>1,
						"max_range"=>120
				)
		),
		"last_name" => array
		(
				"filter"=>FILTER_SANITIZE_STRING,
				"options"=>array
				(
						"min_range"=>1,
						"max_range"=>120
				)
		),
		"username" => array
		(
				"filter"=>FILTER_SANITIZE_STRING,
				"options"=>array
				(
						"min_range"=>4,
						"max_range"=>120
				)
		),
		"email" => array
		(
				"filter"=>FILTER_SANITIZE_EMAIL,
				"options"=>array
				(
						"min_range"=>4,
						"max_range"=>120
				)
		),
		"password" => array
		(
				"filter"=>FILTER_SANITIZE_STRING,
				"options"=>array
				(
						"min_range"=>6,
						"max_range"=>120
				)
		)
	);

	$login_filters = array
	(
			"username" => array
			(
					"filter"=>FILTER_SANITIZE_STRING,
					"options"=>array
					(
							"min_range"=>4,
							"max_range"=>120
					)
			),
			"password" => array
			(
					"filter"=>FILTER_SANITIZE_STRING,
					"options"=>array
					(
							"min_range"=>6,
							"max_range"=>120
					)
			)
	);
?>