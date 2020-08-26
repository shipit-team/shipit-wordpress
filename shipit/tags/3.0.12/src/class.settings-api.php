<?php
if (!class_exists('Shipit_Settings')) {
  class Shipit_Settings {
    protected $settings_sections = array();
    protected $settings_fields = array();

    public function __construct() {
      add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    function admin_enqueue_scripts() {
      wp_enqueue_style('wp-color-picker');
      wp_enqueue_media();
      wp_enqueue_script('wp-color-picker');
      wp_enqueue_script('jquery');
    }

    function set_sections($sections) {
      $this->settings_sections = $sections;
      return $this;
    }

    function add_section($section) {
      $this->settings_sections[] = $section;
      return $this;
    }

    function set_fields($fields) {
      $this->settings_fields = $fields;
      return $this;
    }

    function add_field($section, $field) {
      $defaults = array(
        'name'  => '',
        'label' => '',
        'desc'  => '',
        'type'  => 'text'
      );
      $arg = wp_parse_args($field, $defaults);
      $this->settings_fields[$section][] = $arg;
      return $this;
    }

    function admin_init() {
      foreach ($this->settings_sections as $section) {
        // false == get_option($section['id'])
        if (!get_option($section['id'])) {
          add_option($section['id']);
        }
        if (isset($section['desc']) && !empty($section['desc'])) {
          $section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
          $callback = function() use ($section) {
            echo str_replace('"', '\"', $section['desc']);
          };
        } else if (isset($section['callback'])) {
          $callback = $section['callback'];
        } else {
          $callback = null;
        }
        add_settings_section($section['id'], $section['title'], $callback, $section['id']);
      }

      foreach ($this->settings_fields as $section => $field) {
        foreach ($field as $option) {
          $name = $option['name'];
          $type = isset($option['type']) ? $option['type'] : 'text';
          $label = isset($option['label']) ? $option['label'] : '';
          $callback = isset($option['callback']) ? $option['callback'] : array($this, 'callback_' . $type);
          $args = array(
            'id'                => $name,
            'class'             => isset($option['class']) ? $option['class'] : $name,
            'label_for'         => "{$section}[{$name}]",
            'desc'              => isset($option['desc']) ? $option['desc'] : '',
            'name'              => $label,
            'section'           => $section,
            'size'              => isset($option['size']) ? $option['size'] : null,
            'options'           => isset($option['options']) ? $option['options'] : '',
            'std'               => isset($option['default']) ? $option['default'] : '',
            'sanitize_callback' => isset($option['sanitize_callback']) ? $option['sanitize_callback'] : '',
            'type'              => $type,
            'placeholder'       => isset($option['placeholder']) ? $option['placeholder'] : '',
            'min'               => isset($option['min']) ? $option['min'] : '',
            'max'               => isset($option['max']) ? $option['max'] : '',
            'step'              => isset($option['step']) ? $option['step'] : '',
          );

          add_settings_field("{$section}[{$name}]", $label, $callback, $section, $section, $args);
        }
      }

      foreach ($this->settings_sections as $section) {
        register_setting($section['id'], $section['id'], array($this, 'sanitize_options'));
      }
    }

    public function get_field_description($args) {
      if (!empty($args['desc'])) {
        $desc = sprintf('<p class="description">%s</p>', $args['desc']);
      } else {
        $desc = '';
      }
      return $desc;
    }

    function callback_text($args) {
      $value       = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
      $size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
      $type        = isset($args['type']) ? $args['type'] : 'text';
      $placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';
      $html        = sprintf('<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder);
      $html       .= $this->get_field_description($args);
      echo $html;
    }

    function callback_url($args) {
      $this->callback_text($args);
    }

    function callback_number($args) {
      $value       = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
      $size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
      $type        = isset($args['type']) ? $args['type'] : 'number';
      $placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';
      $min         = ($args['min'] == '') ? '' : ' min="' . $args['min'] . '"';
      $max         = ($args['max'] == '') ? '' : ' max="' . $args['max'] . '"';
      $step        = ($args['step'] == '') ? '' : ' step="' . $args['step'] . '"';
      $html        = sprintf('<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step);
      $html       .= $this->get_field_description($args);
      echo $html;
    }

    function callback_checkbox($args) {
      $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
      $html  = '<fieldset>';
      $html  .= sprintf('<label for="wpuf-%1$s[%2$s]">', $args['section'], $args['id']);
      $html  .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id']);
      $html  .= sprintf('<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked($value, 'on', false));
      $html  .= sprintf('%1$s</label>', $args['desc']);
      $html  .= '</fieldset>';
      echo $html;
    }

    function callback_multicheck($args) {
      $value = $this->get_option($args['id'], $args['section'], $args['std']);
      $html  = '<fieldset>';
      $html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id']);
      foreach ($args['options'] as $key => $label) {
        $checked = isset($value[$key]) ? $value[$key] : '0';
        $html    .= sprintf('<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
        $html    .= sprintf('<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked($checked, $key, false));
        $html    .= sprintf('%1$s</label><br>',  $label);
      }
      $html .= $this->get_field_description($args);
      $html .= '</fieldset>';
      echo $html;
    }

    function callback_radio($args) {
      $value = $this->get_option($args['id'], $args['section'], $args['std']);
      $html  = '<fieldset>';
      foreach ($args['options'] as $key => $label) {
        $html .= sprintf('<label for="wpuf-%1$s[%2$s][%3$s]">',  $args['section'], $args['id'], $key);
        $html .= sprintf('<input type="radio" class="radio" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked($value, $key, false));
        $html .= sprintf('%1$s</label><br>', $label);
      }

      $html .= $this->get_field_description($args);
      $html .= '</fieldset>';
      echo $html;
    }

    function callback_select($args) {
      $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
      $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
      $html  = sprintf('<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id']);
      foreach ($args['options'] as $key => $label) {
        $html .= sprintf('<option value="%s"%s>%s</option>', $key, selected($value, $key, false), $label);
      }
      $html .= sprintf('</select>');
      $html .= $this->get_field_description($args);
      echo $html;
    }

    function callback_textarea($args) {
      $value       = esc_textarea($this->get_option($args['id'], $args['section'], $args['std']));
      $size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
      $placeholder = empty($args['placeholder']) ? '' : ' placeholder="'.$args['placeholder'].'"';
      $html        = sprintf('<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value);
      $html        .= $this->get_field_description($args);
      echo $html;
    }

    function callback_html($args) {
      echo $this->get_field_description($args);
    }

    function callback_wysiwyg($args) {
      $value = $this->get_option($args['id'], $args['section'], $args['std']);
      $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : '500px';
      echo '<div style="max-width: ' . $size . ';">';
      $editor_settings = array(
        'teeny'         => true,
        'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
        'textarea_rows' => 10
      );
      if (isset($args['options']) && is_array($args['options'])) {
        $editor_settings = array_merge($editor_settings, $args['options']);
      }
      wp_editor($value, $args['section'] . '-' . $args['id'], $editor_settings);
      echo '</div>';
      echo $this->get_field_description($args);
    }

    function callback_file($args) {
      $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
      $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
      $id    = $args['section']  . '[' . $args['id'] . ']';
      $label = isset($args['options']['button_label']) ? $args['options']['button_label'] : __('Choose File');
      $html  = sprintf('<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
      $html  .= '<input type="button" class="button wpsa-browse" value="' . $label . '" />';
      $html  .= $this->get_field_description($args);
      echo $html;
    }

    function callback_password($args) {
      $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
      $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
      $html  = sprintf('<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
      $html  .= $this->get_field_description($args);
      echo $html;
    }

    function callback_color($args) {
      $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
      $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
      $html  = sprintf('<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std']);
      $html  .= $this->get_field_description($args);
      echo $html;
    }

    function callback_pages($args) {
      $dropdown_args = array(
        'selected' => esc_attr($this->get_option($args['id'], $args['section'], $args['std'])),
        'name'     => $args['section'] . '[' . $args['id'] . ']',
        'id'       => $args['section'] . '[' . $args['id'] . ']',
        'echo'     => 0
      );
      $html = wp_dropdown_pages($dropdown_args);
      echo $html;
    }

    function sanitize_options($options) {
      if (!$options) return $options;

      foreach($options as $option_slug => $option_value) {
        $sanitize_callback = $this->get_sanitize_callback($option_slug);
        if ($sanitize_callback) {
          $options[$option_slug] = call_user_func($sanitize_callback, $option_value);
          continue;
        }
      }
      return $options;
    }

    function get_sanitize_callback($slug = '') {
      if (empty($slug)) return false;

      foreach($this->settings_fields as $section => $options) {
        foreach ($options as $option) {
          if ($option['name'] != $slug) {
            continue;
          }

          return isset($option['sanitize_callback']) && is_callable($option['sanitize_callback']) ? $option['sanitize_callback'] : false;
        }
      }
      return false;
    }

    function get_option($option, $section, $default = '') {
      $options = get_option($section);
      if (isset($options[$option])) return $options[$option];

      return $default;
    }

    function show_navigation() {
      $html = '<h2 class="nav-tab-wrapper">';
      $count = count($this->settings_sections);
      if ($count === 1) return;

      foreach ($this->settings_sections as $tab) {
        $html .= sprintf('<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title']);
      }
      $html .= '</h2>';
      echo $html;
    }

    function show_forms() {
      ?>
        <div class="metabox-holder">
          <?php foreach ($this->settings_sections as $form) { ?>
            <div id="<?php echo $form['id']; ?>" class="group" style="display: none;">
              <form method="post" action="options.php">
                <?php
                  do_action('wsa_form_top_' . $form['id'], $form);
                  settings_fields($form['id']);
                  do_settings_sections($form['id']);
                  do_action('wsa_form_bottom_' . $form['id'], $form);
                ?>
                <?php if (isset($this->settings_fields[$form['id']])) { ?>
                  <div style="padding-left: 10px">
                    <?php submit_button(); ?>
                  </div>
                <?php } ?>
              </form>
            </div>
          <?php } ?>
        </div>
      <?php
      $this->script();
    }

    function script() {
      ?>
        <script>
          jQuery(document).ready(function($) {
            $('.wp-color-picker-field').wpColorPicker();
            $('.group').hide();
            var activetab = '';
            if (typeof(localStorage) != 'undefined') {
              activetab = localStorage.getItem("activetab");
            }
            if (window.location.hash) {
              activetab = window.location.hash;
              if (typeof(localStorage) != 'undefined') {
                localStorage.setItem("activetab", activetab);
              }
            }

            if (activetab != '' && $(activetab).length) {
              $(activetab).fadeIn();
            } else {
              $('.group:first').fadeIn();
            }
            $('.group .collapsed').each(function() {
              $(this).find('input:checked').parent().parent().parent().nextAll().each(function() {
                if ($(this).hasClass('last')) {
                  $(this).removeClass('hidden');
                  return false;
                }
                $(this).filter('.hidden').removeClass('hidden');
              });
            });

            if (activetab != '' && $(activetab + '-tab').length) {
              $(activetab + '-tab').addClass('nav-tab-active');
            } else {
              $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
            }

            $('.nav-tab-wrapper a').click(function(evt) {
              $('.nav-tab-wrapper a').removeClass('nav-tab-active');
              $(this).addClass('nav-tab-active').blur();
              var clicked_group = $(this).attr('href');
              if (typeof(localStorage) != 'undefined') {
                localStorage.setItem("activetab", $(this).attr('href'));
              }
              $('.group').hide();
              $(clicked_group).fadeIn();
              evt.preventDefault();
            });

            $('.wpsa-browse').on('click', function(event) {
              event.preventDefault();
              var self = $(this);
              var file_frame = wp.media.frames.file_frame = wp.media({
                title: self.data('uploader_title'),
                button: {
                  text: self.data('uploader_button_text'),
                },
                multiple: false
              });
              file_frame.on('select', function() {
                attachment = file_frame.state().get('selection').first().toJSON();
                self.prev('.wpsa-url').val(attachment.url).change();
              });
              file_frame.open();
            });
          });
        </script>
      <?php
      $this->_style_fix();
    }

    function _style_fix() {
      global $wp_version;
      if (version_compare($wp_version, '3.8', '<=')) {
        ?>
          <style type="text/css">
            .form-table th { padding: 20px 10px; }
            #wpbody-content .metabox-holder { padding-top: 5px; }
          </style>
        <?php
      }
    }
  }
}
?>