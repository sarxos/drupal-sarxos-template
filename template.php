<?php


/**
 * Checks if current page is administration, installation or upgrade screen.
 */
function sarxos_isadmininstallorupdate() {
	static $admin_install_or_update = NULL;
	if ($admin_install_or_update === NULL) {
		$break = explode('/', $_SERVER['SCRIPT_NAME']);
		$pfile = $break[count($break) - 1];
		$admin_install_or_update = arg(0) === 'admin' || $pfile === 'install.php' || $pfile === 'update.php';
	}
	return $admin_install_or_update;
}


/**
 * Preprocess page hook impl.
 */
function sarxos_preprocess_page(&$variables) {

	// setup RSS feed link
	$variables['main_feed'] = l(t('Subscribe'), 'rss.xml', array('attributes' => array('class' => 'subscribe')));

	// setup footer message
	if (empty($variables['footer_message'])) {
		$variables['footer_message'] = '&copy; ' . $variables['site_name'] . ' 2012';
	}
	
	// setup new jquery
	if (sarxos_isadmininstallorupdate()) {
		return;
	}
	if (!empty($variables['scripts']) && isset($scripts['core']['misc/jquery.js'])) {
		$scripts = drupal_add_js();
		unset($scripts['core']['misc/jquery.js']);                  // replace old jQuery with new one
		$variables['scripts'] = drupal_get_js('header', $scripts);  // re-render scripts variable
		$processedJS = true;
	}
}


/**
 * Set up CSS classes for the node so we don't have to do it in the template.
 */
function sarxos_preprocess_node(&$vars) {
	static $first;
	$vars['classes'] = array('post');
	$vars['classes'][] = 'post-' . str_replace('_', '-', $vars['node']->type);
	if (!isset($first)) {
		$first = TRUE;
		$vars['classes'][] = 'first';
	}
	if ($vars['sticky']) {
		$vars['classes'][] = 'sticky';
	}
	if ($vars['status']) {
		$vars['classes'][] = 'post-unpublished';
	}
	$vars['classes'] = implode(' ', $vars['classes']);

	// Populate more granular submitted-by information.
	$vars['postdate'] = format_date($vars['node']->created, 'custom', 'd F Y');
	$vars['author'] = theme('username', $vars['node']);

	$vars['comments_link'] = l(
		$vars['comment_count'], 'node/' . $vars['node']->nid,
		array(
			'fragment' => 'comment',
			'title' => t('Comment on !title', array('!title' => $vars['node']->title))
		)
	);
	
	$vars['links'] =
		'<div class="btn-toolbar">' .
		'<div class="btn-group">';

	foreach ($vars['node']->links as $link) {
		$path = $link['href'];
		$text = $link['title'];
		$title = $link['attributes']['title'];
		if (strlen($text) > 0 && strlen($path) > 0) {
			$vars['links'] .= "<a href='$path' title='$title' class='btn'>$text</a>";
		}
	}
	
	$vars['links'] .= '</div></div>';
}


function sarxos_preprocess_search_theme_form(&$vars) {
	$vars['theme_directory'] = drupal_get_path('theme', 'sarxos');
}


function sarxos_preprocess_comment_wrapper(&$vars) {
	$node = $vars['node'];
	$vars['header'] = t('<strong>!count comments</strong> on %title', array(
		'!count' => $node->comment_count, 
		'%title' => $node->title
	));
}


function sarxos_preprocess_comment(&$vars) {
	$vars['classes'] = array('comment');
	if ($vars['zebra'] == 'odd') {
		$vars['classes'][] = 'alt';
	}
	if ($vars['comment']->uid == $vars['node']->uid) {
		$vars['classes'][] = 'authorcomment';
	}
	$vars['classes'] = implode(' ', $vars['classes']);
}
