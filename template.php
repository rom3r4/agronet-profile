<?php

/**
 * @file
 * Process theme data.
 *
 * Use this file to run your theme specific implimentations of theme functions,
 * such preprocess, process, alters, and theme function overrides.
 *
 * Preprocess and process functions are used to modify or create variables for
 * templates and theme functions. They are a common theming tool in Drupal, often
 * used as an alternative to directly editing or adding code to templates. Its
 * worth spending some time to learn more about these functions - they are a
 * powerful way to easily modify the output of any template variable.
 *
 * Preprocess and Process Functions SEE: http://drupal.org/node/254940#variables-processor
 * 1. Rename each function and instance of "commons_origins" to match
 *    your subthemes name, e.g. if your theme name is "footheme" then the function
 *    name will be "footheme_preprocess_hook". Tip - you can search/replace
 *    on "commons_origins".
 * 2. Uncomment the required function to use.
 */


/**
 * Implements hook_theme().
 */
function commons_origins_theme($existing, $type, $theme, $path) {
  return array(
    // Register the newly added theme_form_content() hook so we can utilize
    // theme hook suggestions.
    // @see commons_origins_form_alter().
    'form_content' => array(
      'render element' => 'form',
      'path' => drupal_get_path('theme', 'commons_origins') . '/templates/form',
      'template' => 'form-content',
    ),
  );
}

/**
 * Implements hook_preprocess_search_results().
 *
 * Assemble attributes for styling that core does not do so we can keep the
 * tpl files simpler and make maintaining it a bit less worrisome since there
 * are 2 forms of search supported.
 */
function commons_origins_preprocess_search_results(&$vars, $hook) {
  $vars['classes_array'][] = 'search-results-wrapper';
  $vars['title_attributes_array']['class'][] = 'search-results-title';
  $vars['content_attributes_array']['class'][] = 'search-results-content';
  $vars['content_attributes_array']['class'][] = 'commons-pod';
}

/**
 * Preprocess variables for the html template.
 */
function commons_origins_preprocess_html(&$vars) {
  global $theme_key;

  $site_name = variable_get('site_name', 'Commons');

  if (strlen($site_name) > 23) {
    $vars['classes_array'][] = 'site-name-long-2-lines';
  } else if (strlen($site_name) > 15) {
    $vars['classes_array'][] = 'site-name-long';
  }
  $palette = variable_get('commons_origins_palette', 'default');
  if ($palette != 'default') {
    $vars['classes_array'][] = 'palette-active';
    $vars['classes_array'][] = drupal_html_class($palette);
  }

  // Two examples of adding custom classes to the body.

  // Add a body class for the active theme name.
  // $vars['classes_array'][] = drupal_html_class($theme_key);

  // Browser/platform sniff - adds body classes such as ipad, webkit, chrome etc.
  $vars['classes_array'][] = css_browser_selector();

}
//


/**
 * Process variables for the html template.
 */
/* -- Delete this line if you want to use this function
function commons_origins_process_html(&$vars) {
}
// */

/**
 * Implements theme_menu_link().
 */
function commons_origins_menu_link($vars) {
  $output = '';
  $path_to_at_core = drupal_get_path('theme', 'adaptivetheme');

  include_once($path_to_at_core . '/inc/get.inc');

  global $theme_key;
  $theme_name = $theme_key;

  $element = $vars['element'];
  commons_origins_menu_link_class($element);
  $sub_menu = '';

  if ($element['#below']) {
    $sub_menu = drupal_render($element['#below']);
  }

  if (at_get_setting('extra_menu_classes', $theme_name) == 1 && !empty($element['#original_link'])) {
    if (!empty($element['#original_link']['depth'])) {
      $element['#attributes']['class'][] = 'menu-depth-' . $element['#original_link']['depth'];
    }
    if (!empty($element['#original_link']['mlid'])) {
      $element['#attributes']['class'][] = 'menu-item-' . $element['#original_link']['mlid'];
    }
  }

  if (at_get_setting('menu_item_span_elements', $theme_name) == 1 && !empty($element['#title'])) {
    $element['#title'] = '<span>' . $element['#title'] . '</span>';
    $element['#localized_options']['html'] = TRUE;
  }

  if (at_get_setting('unset_menu_titles', $theme_name) == 1 && !empty($element['#localized_options']['attributes']['title'])) {
    unset($element['#localized_options']['attributes']['title']);
  }

  $output = l($element['#title'], $element['#href'], $element['#localized_options']);
  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "</li>";
}

/**
 * Helper function to examine menu links and return the appropriate class.
 */
function commons_origins_menu_link_class(&$element)  {
  if ($element['#original_link']['menu_name'] == 'main-menu') {
    $element['#attributes']['class'][] = drupal_html_class($element['#original_link']['menu_name'] . '-' . $element['#original_link']['router_path']);
  }
}

/**
 * Override or insert variables for the page templates.
 */
function commons_origins_preprocess_page(&$vars) {
  if (module_exists('page_manager')) {
    $p = page_manager_get_current_page();
    if (isset($p['name']) && $p['name'] == 'node_view') {
      $node = $p['contexts']['argument_entity_id:node_1']->data;
      if (module_exists('og') && !og_is_group('node', $node)) {
        $vars['hide_panelized_title'] = 1;
      }
    }
  }
}
function commons_origins_process_page(&$vars) {
}
// */


/**
 * Override or insert variables into the node templates.
 */
function commons_origins_preprocess_node(&$vars) {
  // Append a feature label to featured node teasers.
  if ($vars['teaser'] && $vars['promote']) {
    $vars['submitted'] .= ' <span class="featured-node-tooltip">' . t('Featured') . ' ' . $vars['type'] . '</span>';
  }

  // Some content does not get a user image on the full node.
  $no_avatar = array(
    'event',
    'group',
    'page',
    'wiki',
  );
  if (!$vars['teaser'] && in_array($vars['node']->type, $no_avatar)) {
    $vars['user_picture'] = '';
  }

  // If there does happen to be a user image, add a class for styling purposes.
  if (!empty($vars['user_picture'])) {
    $vars['classes_array'][] = 'user-picture-available';
  }

  // Add classes to render the comment-comments link as a button with a number
  // attached.
  if (!empty($vars['content']['links']['comment']['#links']['comment-comments'])) {
    $comments_link = &$vars['content']['links']['comment']['#links']['comment-comments'];
    $comments_link['attributes']['class'][] = 'link-with-counter';
    $comments_link['title'] = str_replace($vars['comment_count'], '<span class="counter">' . $vars['comment_count'] . '</span>', $comments_link['title']);
  }

  // Push the reporting link to the end.
  if (!empty($vars['content']['links']['flag']['#links']['flag-inappropriate_node'])) {
    $vars['content']['report_link'] = array('#markup' => $vars['content']['links']['flag']['#links']['flag-inappropriate_node']['title']);
  }
  // kpr(render($vars['content']['report_link']));

  if (!empty($vars['content']['links'])) {
    // Hide some of the node links.
    $hidden_links = array(
      'node' => array(
        'node-readmore',
      ),
      'comment' => array(
        'comment-add',
        'comment-new-comments'
      ),
      'flag' => array(
        'flag-inappropriate_node',
      ),
    );
    foreach ($hidden_links as $element => $links) {
      foreach ($links as $link) {
        if (!empty($vars['content']['links'][$element]['#links'][$link])) {
          $vars['content']['links'][$element]['#links'][$link]['#access'] = FALSE;
        }
      }
    }
  }

  // Replace the submitted text on nodes with something a bit more pertinent to
  // the content type.
  if (variable_get('node_submitted_' . $vars['node']->type, TRUE)) {
    $placeholders = array(
      '!type' => '<span class="node-content-type">' . ucfirst($vars['node']->type) . '</span>',
      '!user' => $vars['name'],
      '!date' => $vars['date'],
    );

    $vars['submitted'] = t('!type created by !user on !date', $placeholders);
  }

  // Add a class to the node when there is a logo image.
  if (!empty($vars['field_logo'])) {
    $vars['classes_array'][] = 'logo-available';
  }

  // Move the answer link on question nodes to the top of the content.
  if ($vars['node']->type == 'question' && !empty($vars['content']['links']['answer'])) {
    $vars['content']['answer'] = $vars['content']['links']['answer'];
    $vars['content']['answer']['#attributes']['class'][] = 'node-actions';
    $vars['content']['answer']['#links']['answer-add']['attributes']['class'][] = 'button-alert';
    $vars['content']['answer']['#weight'] = -100;
    $vars['content']['links']['answer']['#access'] = FALSE;
  }
}

/**
 * Implements hook_preprocess_two_33_66().
 */
function commons_origins_preprocess_two_33_66(&$vars, $hook) {
  $menu = menu_get_item();

  // Suggest a variant for the search page so the facets will be wrapped in pod
  // styling.
  if (strpos($menu['path'], 'search') === 0) {
    $vars['theme_hook_suggestions'][] = 'two_33_66__search';
  }
}

function commons_origins_preprocess_three_25_50_25(&$vars, $hook) {
  $menu = menu_get_item();

  // Suggest a variant for the search page so the facets will be wrapped in pod
  // styling.
  if (isset($menu['page_arguments']) && $menu['page_arguments'][0] == 'solr_events') {
    $vars['theme_hook_suggestions'][] = 'three_25_50_25__events';
  }
}

/**
 * Implements hook_preprocess_panels_pane().
 */
function commons_origins_preprocess_panels_pane(&$vars, $hook) {
  $pane = $vars['pane'];

  // Add pod styling to some of the panels panes.
  $not_pods = array(
    'commons_events-commons_events_create_event_link',
  );
  $content_pods = array(
    'commons_question_answers-panel_pane_1',
  );
  if (($pane->panel == 'two_66_33_second' && !in_array($pane->subtype, $not_pods)) || in_array($pane->subtype, $content_pods)) {
    $vars['attributes_array']['class'][] = 'commons-pod';
  }
}

/**
 * Implements hook_preprocess_views_view().
 */
function commons_origins_preprocess_views_view(&$vars, $hook) {
  $view = $vars['view'];

  // Wrap page views in pod styling.
  if ($view->display_handler->plugin_name == 'page') {
    $vars['classes_array'][] = 'commons-pod';
    $vars['classes_array'][] = 'clearfix';
  }

  // Style some views without bottom borders and padding.
  $plain = array(
    'commons_bw_all' => array('default'),
    'commons_bw_polls' => array('default'),
    'commons_bw_posts' => array('default'),
    'commons_bw_q_a' => array('default'),
    'commons_bw_wikis' => array('default'),
    'commons_events_upcoming' => array('panel_pane_2'),
    'commons_featured' => array('panel_pane_1'),
    'commons_groups_directory' => array('panel_pane_1'),
    'commons_groups_recent_content' => array('block'),
    'commons_groups_user_groups' => array('panel_pane_1'),
    'commons_homepage_content' => array('panel_pane_1'),
    'commons_radioactivity_groups_active_in_group' => array('panel_pane_1'),
    'commons_radioactivity_groups_most_active' => array('panel_pane_1'),
  );
  if (isset($plain[$vars['name']]) && in_array($vars['display_id'], $plain[$vars['name']])) {
    $vars['classes_array'][] = 'view-plain';
  }
}

/**
 * Implements hook_preprocess_form().
 *
 * Since Commons Origins overrides the default theme_form() function, we will
 * need to perform some processing on attributes to make it work in a template.
 */
function commons_origins_preprocess_form(&$vars, $hook) {
  // Bootstrap the with some of Drupal's default variables.
  template_preprocess($vars, $hook);

  $element = &$vars['element'];
  if (isset($element['#action'])) {
    $element['#attributes']['action'] = drupal_strip_dangerous_protocols($element['#action']);
  }
  element_set_attributes($element, array('method', 'id'));
  if (empty($element['#attributes']['accept-charset'])) {
    $element['#attributes']['accept-charset'] = "UTF-8";
  }
  $vars['attributes_array'] = $element['#attributes'];

  // Give the search form on the search page pod styling.
  if (isset($element['#search_page']) || (isset($element['module']) && ($element['module']['#value'] == 'search_facetapi' || $element['module']['#value'] == 'user'))) {
    $vars['attributes_array']['class'][] = 'commons-pod';
  }

  $pods = array(
    'user-login',
    'user-pass',
    'user-register-form',
  );

  if (in_array($element['#id'], $pods)) {
    $vars['attributes_array']['class'][] = 'commons-pod';
  }
}

/**
 * Implements hook_process_form().
 *
 * Since Commons Origins overrides the default theme_form() function, we will
 * need to perform some processing on attributes to make it work in a template.
 */
function commons_origins_process_form(&$vars, $hook) {
  // Crunch down attribute arrays.
  template_process($vars, $hook);
}

/**
 * Implements hook_preprocess_form_content().
 */
function commons_origins_preprocess_form_content(&$vars, $hook) {
  // Bootstrap the with some of Drupal's default variables.
  template_preprocess($vars, $hook);

  if (isset($vars['form']['supplementary'])) {
    foreach ($vars['form']['supplementary'] as &$field) {
      if (is_array($field) && isset($field['#theme_wrappers'])) {
        $field['#theme_wrappers'][] = 'container';
        $field['#attributes']['class'][] = 'commons-pod';
      }
    }
  }
}

/**
 * Implements hook_process_form_content().
 */
function commons_origins_process_form_content(&$vars, $hook) {
  // Crunch down attribute arrays.
  template_process($vars, $hook);
}

/**
 * Implements hook_preprocess_views_view_unformatted().
 */
function commons_origins_preprocess_views_view_unformatted(&$vars) {
  // Prevent the avatars in the activity stream blocks from bleeding into the
  // rows below them.
  if ($vars['view']->name == 'commons_activity_streams_activity') {
    foreach ($vars['classes_array'] as &$classes) {
      $classes .= ' clearfix';
    }
  }
}

/**
 * Implements hook_form_alter().
 */
function commons_origins_form_alter(&$form, &$form_state, $form_id) {
  // Give forms a common theme function so we do not have to declare every
  // single form we want to override in hook_theme().
  if (is_array($form['#theme'])) {
    $hooks = array('form_content');
    $form['#theme'] = array_merge($form['#theme'], $hooks);
  }
  else {
    $form['#theme'] = array(
      $form['#theme'],
      'form_content',
    );
  }

  // Description text on these fields is redundant.
  if ($form_id == 'user_login') {
    $form['name']['#description'] = '';
    $form['pass']['#description'] = '';
  }

  if ($form_id == 'user_register_form') {
    $form['account']['mail']['#description'] = t('Password reset and notification emails will be sent to this address.');
  }

  if (isset($form['#node_edit_form']) && $form['#node_edit_form']) {
    // Vertical tabs muck things up, so things need to be shuffled to get rid
    // of them.
    $general_settings = array();
    foreach ($form as $id => $field) {
      if (is_array($field) && isset($field['#group']) && $field['#group'] == 'additional_settings') {
        $general_settings[$id] = $field;
        $general_settings[$id]['#collapsible'] = TRUE;
        $general_settings[$id]['#collapsed'] = TRUE;
        unset($general_settings[$id]['#group']);
      }
    }
    if (!empty($general_settings)) {
      $form['general_settings'] = array(
        '#theme_wrappers' => array('container'),
        '#attributes' => array(
          'class' => array('general-settings'),
        ),
        '#weight' => 100,
        'general_settings' => $general_settings,
      );
      $form['additional_settings']['#access'] = FALSE;
    }

    // Declare the fields to go into each column.
    $supplementary = array(
      'event_topics',
      'field_topics',
      'general_settings',
    );

    foreach ($supplementary as $field) {
      if (isset($form[$field])) {
        // Translate the field to the appropriate container.
        $form['supplementary'][$field] = $form[$field];

        // Remove access to the old placement instead of unset() to maintain
        // the legacy information.
        $form[$field]['#access'] = FALSE;
      }
    }
  }
}

/**
 * Implements hook_css_alter().
 */
function commons_origins_css_alter(&$css) {
  if (isset($css['profiles/commons/modules/contrib/rich_snippets/rich_snippets.css'])) {
    unset($css['profiles/commons/modules/contrib/rich_snippets/rich_snippets.css']);
  }
}

/**
 * Override or insert variables into the comment templates.
 */
/* -- Delete this line if you want to use these functions
function commons_origins_preprocess_comment(&$vars) {
}
function commons_origins_process_comment(&$vars) {
}
// */


/**
 * Override or insert variables into the block templates.
 */
/* -- Delete this line if you want to use these functions
function commons_origins_preprocess_block(&$vars) {
}
function commons_origins_process_block(&$vars) {
}
// */

/**
 * Overrides theme_links() for nodes.
 *
 * This allows for the theme to set a link's #access argument to FALSE so it
 * will not render.
 */
function commons_origins_links($vars) {
  $links = $vars['links'];
  $attributes = $vars['attributes'];
  $heading = $vars['heading'];
  global $language_url;
  $output = '';

  if (count($links) > 0) {
    $output = '';

    // Treat the heading first if it is present to prepend it to the
    // list of links.
    if (!empty($heading)) {
      if (is_string($heading)) {
        // Prepare the array that will be used when the passed heading
        // is a string.
        $heading = array(
          'text' => $heading,
          // Set the default level of the heading.
          'level' => 'h2',
        );
      }
      $output .= '<' . $heading['level'];
      if (!empty($heading['class'])) {
        $output .= drupal_attributes(array('class' => $heading['class']));
      }
      $output .= '>' . check_plain($heading['text']) . '</' . $heading['level'] . '>';
    }

    $output .= '<ul' . drupal_attributes($attributes) . '>';

    $num_links = count($links);
    $i = 1;

    foreach ($links as $key => $link) {
      if (!isset($link['#access']) || $link['#access'] !== FALSE) {
        $class = array($key);

        // Add first, last and active classes to the list of links to help out themers.
        if ($i == 1) {
          $class[] = 'first';
        }
        if ($i == $num_links) {
          $class[] = 'last';
        }
        if (isset($link['href']) && ($link['href'] == $_GET['q'] || ($link['href'] == '<front>' && drupal_is_front_page())) && (empty($link['language']) || $link['language']->language == $language_url->language)) {
          $class[] = 'active';
        }
        $output .= '<li' . drupal_attributes(array('class' => $class)) . '>';

        if (isset($link['href'])) {
          // Pass in $link as $options, they share the same keys.
          $output .= l($link['title'], $link['href'], $link);
        }
        elseif (!empty($link['title'])) {
          // Some links are actually not links, but we wrap these in <span> for adding title and class attributes.
          if (empty($link['html'])) {
            $link['title'] = check_plain($link['title']);
          }
          $span_attributes = '';
          if (isset($link['attributes'])) {
            $span_attributes = drupal_attributes($link['attributes']);
          }
          $output .= '<span' . $span_attributes . '>' . $link['title'] . '</span>';
        }

        $i++;
        $output .= "</li>\n";
      }
    }

    $output .= '</ul>';
  }

  return $output;
}

/**
 * Overrides theme_field__addressfield().
 */
function commons_origins_field__addressfield($variables) {
  $output = '';

  // Add Microformat classes to each address.
  foreach($variables['items'] as &$address) {
    // Only display an address if it has been populated. We determine this by
    // validating that the administrative area has been populated.
    if ($address['#address']['administrative_area']) {
      $address['#theme_wrappers'][] = 'container';
      $address['#attributes']['class'][] = 'adr';
      if (!empty($address['street_block']['thoroughfare'])) {
        $address['street_block']['thoroughfare']['#attributes']['class'][] = 'street-address';
      }
      if (!empty($address['street_block']['premise'])) {
        $address['street_block']['premise']['#attributes']['class'][] = 'extended-address';
      }
      if (!empty($address['locality_block']['locality'])) {
        $address['locality_block']['locality']['#suffix'] = ',';
      }
      if (!empty($address['locality_block']['administrative_area'])) {
        $address['locality_block']['administrative_area']['#attributes']['class'][] = 'region';
      }
      if (!empty($address['country'])) {
        $address['country']['#attributes']['class'][] = 'country-name';
      }
    }
    else {
      // Deny access to incomplete addresses.
      $address['#access'] = FALSE;
    }
  }

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<div class="field-label"' . $variables['title_attributes'] . '>' . $variables['label'] . ':&nbsp;</div>';
  }

  // Render the items.
  $output .= '<div class="field-items"' . $variables['content_attributes'] . '>';
  foreach ($variables['items'] as $delta => $item) {
    $classes = 'field-item ' . ($delta % 2 ? 'odd' : 'even');
    $output .= '<div class="' . $classes . '"' . $variables['item_attributes'][$delta] . '>' . drupal_render($item) . '</div>';
  }
  $output .= '</div>';

  // Render the top-level DIV.
  $output = '<div class="' . $variables['classes'] . '"' . $variables['attributes'] . '>' . $output . '</div>';

  return $output;
}

/**
 * Implements hook_preprocess_views_view_field().
 */
function commons_origins_preprocess_views_view_field(&$vars, $hook) {
  // Make sure empty addresses are not displayed.
  // Views does not use theme_field__addressfield(), so we need to process
  // these implementations separately.
  if (isset($vars['theme_hook_suggestion']) && $vars['theme_hook_suggestion'] == 'views_view_field__field_address') {
    $needs_rebuild = FALSE;
    foreach ($vars['row']->field_field_address as $key => $address) {
      if (!$address['raw']['administrative_area']) {
        // If an address is incomplete, remove it and tell the system a
        // rebuild is needed.
        unset($vars['row']->field_field_address[$key]);
        $needs_rebuild = TRUE;
      }
    }

    // Only spend the resources rebuilding if it is needed. Views already has
    // the content rendered by the time it gets to this display, so the output
    // needs rebuilt if anything has changed.
    if ($needs_rebuild) {
      $vars['output'] = $vars['field']->advanced_render($vars['row']);
    }
  }
}
