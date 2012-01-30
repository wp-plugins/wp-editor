<div id="save-result"></div>
<div class="wrap">
  <?php screen_icon(); ?>
  <h2><?php _e('Edit Plugins', 'wpeditor'); ?></h2>
  <?php if(in_array($data['file'], (array) get_option('active_plugins', array()))) { ?>
    <div class="updated">
      <p><?php _e('<strong>This plugin is currently activated!<br />Warning:</strong> Making changes to active plugins is not recommended.  If your changes cause a fatal error, the plugin will be automatically deactivated.', 'wpeditor'); ?></p>
    </div>
	<?php } ?>
  <div class="fileedit-sub">
    <div class="alignleft">
      <h3>
        <?php
        	if(is_plugin_active($data['plugin'])) {
        		if(is_writeable($data['real_file'])) {
        			echo __('Editing <span class="current_file">', 'wpeditor') . $data['file'] . __('</span> (active)', 'wpeditor');
        		}
        		else {
        			echo __('Browsing <span class="current_file">', 'wpeditor') . $data['file'] . __('</span> (active)', 'wpeditor');
        	  }
        	} else {
        		if (is_writeable($data['real_file'])) {
        			echo __('Editing <span class="current_file">', 'wpeditor') . $data['file'] . __('</span> (inactive)', 'wpeditor');
        		}
        		else {
        			echo __('Browsing <span class="current_file">', 'wpeditor') . $data['file'] . __('</span> (inactive)', 'wpeditor');
        	  }
        	}
        ?>
      </h3>
    </div>
    <div class="alignright">
    	<form action="plugins.php?page=wpeditor_plugin" method="post">
    		<strong><label for="plugin"><?php _e('Select plugin to edit:', 'wpeditor'); ?></label></strong>
    		<select name="plugin" id="plugin">
          <?php
          	foreach($data['plugins'] as $plugin_key => $a_plugin) {
          		$plugin_name = $a_plugin['Name'];
          		if($plugin_key == $data['plugin']) {
          			$selected = ' selected="selected"';
          		}
          		else {
          			$selected = '';
          		}
          		$plugin_name = esc_attr($plugin_name);
          		$plugin_key = esc_attr($plugin_key); ?>
          		<option value="<?php echo $plugin_key; ?>" <?php echo $selected; ?>><?php echo $plugin_name; ?></option>
          	<?php
          	}
          ?>
    		</select>
        <input type='submit' name='submit' class="button-secondary" value="<?php _e('Select', 'wpeditor'); ?>" />
    	</form>
    </div>
    <br class="clear" />
  </div>

  <div id="templateside">
  	<h3><?php _e('Plugin Files', 'wpeditor'); ?></h3>
    <div id="plugin-editor-files">
      <ul id="plugin-folders" class="plugin-folders"></ul>
    </div>
  </div>
  
  <form name="template" id="template_form" action="" method="post" class="ajax-editor-update">
    <?php wp_nonce_field('edit-plugin_' . $data['real_file']); ?>
    <div>
      <textarea cols="70" rows="25" name="new-content" id="new-content" tabindex="1"><?php echo $data['content'] ?></textarea>
      <input type="hidden" name="action" value="save_files" />
      <input type="hidden" name="_success" id="_success" value="<?php _e('The file has been updated successfully.', 'wpeditor'); ?>" />
      <input type="hidden" id="file" name="file" value="<?php echo esc_attr($data['file']); ?>" />
      <input type="hidden" id="plugin-dirname" name="plugin" value="<?php echo esc_attr($data['plugin']); ?>" />
      <input type="hidden" id="path" name="path" value="<?php echo esc_attr($data['real_file']); ?>" />
      <input type="hidden" name="scroll_to" id="scroll_to" value="<?php echo $data['scroll_to']; ?>" />
      <input type="hidden" name="content-type" id="content-type" value="<?php echo $data['content-type']; ?>" />
      <?php
        $pathinfo = pathinfo($data['plugin']);
      ?>
      <input type="hidden" name="extension" id="extension" value="<?php echo $pathinfo['extension']; ?>" />
    </div>
    <?php if(is_writeable($data['real_file'])): ?>
      <p class="submit">
      	<?php
      		if(isset($_GET['phperror'])) {
      			echo '<input type="hidden" name="phperror" value="1" />'; ?>
      			<input type="submit" name="submit" class="button-primary" value="<?php _e('Update File and Attempt to Reactivate', 'wpeditor'); ?>" />
      		<?php } else { ?>
      			<input type="submit" name='submit' class="button-primary" value="<?php _e('Update File', 'wpeditor'); ?>" />
      		<?php
      		}
      	?>
      </p>
    <?php else: ?>
      <p>
        <em><?php _e('You need to make this file writable before you can save your changes. See <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">the Codex</a> for more information.'); ?></em>
      </p>
    <?php endif; ?>
  </form>
  <script type="text/javascript">
    /* <![CDATA[ */
    jQuery(document).ready(function($){
      $('#template_form').submit(function(){ 
      	$('#scroll-to').val( $('#new-content').scrollTop() ); 
      });
      $('#new-content').scrollTop($('#scroll-to').val());
    });
    (function($){
      var c;
      var url = ajaxurl;
      var path = '<?php echo urlencode((WPWINDOWS) ? str_replace("/", "\\", $data["real_file"]) : $data["real_file"]); ?>';
      $('#plugin-folders').folders({
        url: url,
        path: path,
        encoded: 1
      }).delegate('a','click',function() {
        $('#plugin-folders li').removeClass('selected');
        c = $(this).parent().addClass('selected').data('path')
      });
    })(jQuery);
    runCodeMirror('<?php echo $pathinfo["extension"]; ?>');
    function runCodeMirror(extension) {
      if(extension === 'php') {
        var mode = 'application/x-httpd-php';
      }
      else if(extension === 'css') {
        var mode = 'css';
      }
      else if(extension === 'js') {
        var mode = 'javascript';
      }
      else if(extension === 'html' || extension === 'htm') {
        var mode = 'text/html';
      }
      else if(extension === 'xml') {
        var mode = 'application/xml';
      }
      <?php
      if(WPEditorSetting::getValue('plugin_editor_theme')) { ?>
        var theme = '<?php echo WPEditorSetting::getValue("plugin_editor_theme"); ?>';
      <?php }
      else { ?>
        var theme = 'default';
      <?php }
      if(WPEditorSetting::getValue('enable_plugin_active_line')) { ?>
        var activeLine = 'activeline-' + theme;
      <?php } ?>
      editor = CodeMirror.fromTextArea(document.getElementById('new-content'), {
        mode: mode,
        theme: theme,
        <?php
        if(WPEditorSetting::getValue('enable_plugin_line_numbers')) { ?>
          lineNumbers: true,
        <?php } ?>
        lineWrapping: true, // set line wrapping here
        onCursorActivity: function() {
          editor.setLineClass(hlLine, null);
          hlLine = editor.setLineClass(editor.getCursor().line, activeLine);
        },
        onChange: function() {
          changeTrue();
        },
        extraKeys: {
          'F11': toggleFullscreenEditing, 
          'Esc': toggleFullscreenEditing
        } // set fullscreen options here
      });
      var hlLine = editor.setLineClass(0, activeLine);
    }
    /* ]]> */
  </script>
</div>
<div class="alignright">
</div>
<br class="clear" />