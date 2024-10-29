<?php
/**
 * Helper functions.
 *
 * @package WbPo
 */



/**
 * var dump funcation.
 *
 * @return void
 */
function dd() {
		  echo '<pre>';
		array_map(
			function( $x ) {
					var_dump( $x );
			},
			func_get_args()
		);
		 die;
}



/**
 * Get Setting  options.0
 *
 * @since 1.0.0
 * @param string $option
 * @param string $section
 * @param string $default
 * @return mixed
 */
function wbpo_get_setting( string $option, string $section, $default = '' ) {

	$options = get_option( $section );

	if ( isset( $options[ $option ] ) ) {
		return $options[ $option ];
	}

	return $default;
}


function wbpo_is_pro() {
	return defined('WBPOP_VERSION');
}

function wbpo_is_pro_text ( ) {
	return '<a target="_blank" href="' . WBPO_PRO_URL . '" class="wbpo-pro-text">(PRO)</a>';
}


/**
 * Allow tags in html for helper.
 *
 * @since 1.0.0
 * @see esc_html or strip_tags
 */
function wpbpo_allow_tags(){
	$default_attribs = array(
		'id' => array(),
		'class' => array(),
		'title' => array(),
		'style' => array(),
		'data' => array(),
		'data-mce-id' => array(),
		'data-mce-style' => array(),
		'data-mce-bogus' => array(),
	);

	$allowed_tags = array(
			'div'           => $default_attribs,
			'span'          => $default_attribs,
			'p'             => $default_attribs,
			'input'             => array_merge( $default_attribs, array(
				'type' => array(),
				'name' => array(),
				'value' => array(),
				'placeholder' => array(),
				'required' => array(),
				'checked' => array(),
				'disabled' => array(),
			) ),
			'fieldset' => $default_attribs,
			'label' => array_merge( $default_attribs, array(
				'for' => array(),
			) ),
			'select' => array_merge( $default_attribs, array(
				'name' => array(),
			) ),
			'option' => array(
				'value' => array(),
				'selected' => array(),
			),
			'textarea' => array(
				'rows' => array(),
				'cols' => array(),
			),
			'a'             => array_merge( $default_attribs, array(
					'href' => array(),
					'target' => array('_blank', '_top'),
			) ),
			'img'             => array_merge( $default_attribs, array(
					'src' => array(),
			) ),
			'h1'             =>  $default_attribs,
			'h2'             =>  $default_attribs,
			'h3'             =>  $default_attribs,
			'h4'             =>  $default_attribs,
			'h5'             =>  $default_attribs,
			'h6'             =>  $default_attribs,
			'u'             =>  $default_attribs,
			'i'             =>  $default_attribs,
			'q'             =>  $default_attribs,
			'b'             =>  $default_attribs,
			'ul'            => $default_attribs,
			'ol'            => $default_attribs,
			'bdi'            => $default_attribs,
			'del'            => $default_attribs,
			'li'            => $default_attribs,
			'br'            => $default_attribs,
			'hr'            => $default_attribs,
			'strong'        => $default_attribs,
			'blockquote'    => $default_attribs,
			'del'           => $default_attribs,
			'strike'        => $default_attribs,
			'em'            => $default_attribs,
			'code'          => $default_attribs,
	);

	return $allowed_tags;
}