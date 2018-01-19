<?php
/**
 * Implements hook_form_FORM_ID_alter().
 *
 * @param $form
 *   The form.
 * @param $form_state
 *   The form state.
 */
function pece_scholarly_lite_form_system_theme_settings_alter(&$form, &$form_state) {
  
  $form['mtt_settings']['tabs']['looknfeel']['custom_color_scheme'] = array(
    '#type' => 'checkbox',
    '#title' => t('Custom Color Scheme'),
    '#description'   => t('Select the color scheme you prefer.'),
    '#default_value' => theme_get_setting('custom_color_scheme'),
  );

  $form['mtt_settings']['tabs']['looknfeel']['color_scheme_settings'] = array(
    '#type' => 'container',
    '#states' => array(
      'invisible' => array(
       ':input[name="custom_color_scheme"]' => array('checked' => FALSE),
      ),
    ),
  );
  $form['mtt_settings']['tabs']['looknfeel']['color_scheme_settings']['color_primary'] = array(
    '#type' => 'textfield',
    '#title' => t('Primary Color'),
    '#description'   => t('Select the primary color. e.g. #A1A1A1'),
    '#default_value' => theme_get_setting('color_primary'),
    '#element_validate' => array('pece_scholarly_lite_color_validate'),    
    '#states' => array(
      'required' => array(
       ':input[name="custom_color_scheme"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['mtt_settings']['tabs']['looknfeel']['color_scheme_settings']['color_secondary'] = array(
    '#type' => 'textfield',
    '#title' => t('Secondary Color'),
    '#description'   => t('Select the secondary color. e.g. #A1A1A1'),
    '#default_value' => theme_get_setting('color_secondary'),
    '#element_validate' => array('pece_scholarly_lite_color_validate'),
    '#states' => array(
      'required' => array(
       ':input[name="custom_color_scheme"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['mtt_settings']['tabs']['looknfeel']['color_scheme_settings']['color_tertiary'] = array(
    '#type' => 'textfield',
    '#title' => t('Tertiary Color'),
    '#description'   => t('Enter the terteary color. e.g. #A1A1A1'),
    '#default_value' => theme_get_setting('color_tertiary'),
    '#element_validate' => array('pece_scholarly_lite_color_validate'),    
    '#states' => array(
      'required' => array(
       ':input[name="custom_color_scheme"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['#submit'][] = 'pece_scholarly_lite_system_theme_settings_submit';
}

/**
 * Form Element Validate function for color scheme overrides.
 */
function pece_scholarly_lite_color_validate($element, &$form_state, $form) {
  $values = $form_state['values'];
  if ($values['custom_color_scheme'] && empty($values[$element['#name']])) 
    form_error($element, t('Custom scheme - %name is required.', array('%name' => $element['#title'])));
}

/**
 * Submit function for color scheme overrides.
 * Apply colors and create stylesheet file.
 */
function pece_scholarly_lite_system_theme_settings_submit($form, $form_state) {

  $values = $form_state['values'];

  // Exit when color overrides is not set.
  if (!$values['custom_color_scheme'])
    return;

  // Prepare override stylesheet template.
  $theme_path = drupal_get_path('theme', 'pece_scholarly_lite');
  $base_style = file_get_contents($theme_path . '/assets/overrides/scheme_override.base.css'); 
  $directory = 'public://pece_scholarly_lite/';
  $status = file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

  // Update colors on template styles.
  $scheme_override = str_replace('_color_primary_', $form_state['values']['color_primary'], $base_style);
  $scheme_override = str_replace('_color_alpha_primary_', hex2rgba($form_state['values']['color_primary'], 0.95), $scheme_override);
  $scheme_override = str_replace('_color_secondary_', $form_state['values']['color_secondary'], $scheme_override);
  $scheme_override = str_replace('_color_alpha_secondary_', hex2rgba($form_state['values']['color_secondary'], 0.95), $scheme_override);
  $scheme_override = str_replace('_color_tertiary_', $form_state['values']['color_tertiary'], $scheme_override);

  // Saves the file to the site's public directory and replaces it if already exists.
  $file = file_save_data($scheme_override, 'public://pece_scholarly_lite/scheme_override.css', FILE_EXISTS_REPLACE);
  if (!$file) {
    drupal_set_message(t('Error on saving Custom color scheme.'), 'error');
    return;
  };

  // Enforce reload for all users.
  _drupal_flush_css_js();

  drupal_clear_css_cache();
  drupal_clear_js_cache();
  
  // Clear the page cache, since cached HTML pages might link to old CSS and
  // JS aggregates.
  cache_clear_all('*', 'cache_page', TRUE);

  drupal_set_message(t('Custom color scheme succesfully created.'));
}

/* Convert hexdec color string to rgb(a) string */
function hex2rgba($color, $opacity = false) {
  $default = 'rgb(0,0,0)';

  //Return default if no color provided
  if(empty($color))
    return $default; 

  //Sanitize $color if "#" is provided 
  if ($color[0] == '#' ) {
    $color = substr( $color, 1 );
  }

  //Check if color has 6 or 3 characters and get values
  if (strlen($color) == 6) {
    $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
  } elseif ( strlen( $color ) == 3 ) {
   $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
  } else {
    return $default;
  }

  //Convert hexadec to rgb
  $rgb =  array_map('hexdec', $hex);

  //Check if opacity is set(rgba or rgb)
  if($opacity){
    if(abs($opacity) > 1)
      $opacity = 1.0;
    $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
  } else {
   $output = 'rgb('.implode(",",$rgb).')';
  }

  //Return rgb(a) color string
  return $output;
}