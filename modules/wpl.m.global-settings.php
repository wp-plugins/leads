<?php
	if (isset($_GET['page'])&&($_GET['page']=='wpleads_global_settings'&&$_GET['page']=='wpleads_global_settings'))
	{
		add_action('admin_init','wpleads_global_settings_enqueue');
		function wpleads_global_settings_enqueue()
		{		
			wp_enqueue_style('wpl-css-global-settings-here', WPL_URL . '/css/wpl.admin-global-settings.css');
			//wp_enqueue_script('wpl-js-global-settings', WPL_URL . '/js/admin.global-settings.js');			
		}
	}
	
	/*SETUP NAVIGATION AND DISPLAY ELEMENTS*/
	$tab_slug = 'wpl-main';
	$wpleads_global_settings[$tab_slug]['label'] = 'Global Settings';	
	
	$wpleads_global_settings[$tab_slug]['options'][] = wpleads_add_option($tab_slug,"text","tracking-ids","","IDs of forms to track!","Enter in a value found in a HTML form's id attribute to track it as a conversion. Gravity Forms, Contact Form 7, and Ninja Forms are automatically tracked (no need to add their IDs in here)", $options=null);
	/*SETUP END*/



	function wpleads_get_global_settings_elements()
	{
		global $wpleads_global_settings;
		return $wpleads_global_settings;
	}	
	
	function wpleads_display_global_settings_js()
	{	
		global $wpleads_global_settings;
		$wpleads_global_settings = wpleads_get_global_settings_elements();
		
		if (isset($_GET['tab']))
		{
			$default_id = $_GET['tab'];
		}
		else
		{
			$default_id ='wpl-main';
		}
			
		?>
		<script type='text/javascript'>
			jQuery(document).ready(function() 
			{
				jQuery('#<? echo $default_id; ?>').css('display','block');
				 setTimeout(function() {
	     			var getoption = document.URL.split('&option=')[1];
					var showoption = "#" + getoption;
					jQuery(showoption).click();
    			}, 100);
				
				<?php
				foreach ($wpleads_global_settings as $key => $array)
				{
				?>
					jQuery('.wpl-nav-tab').live('click', function() {
					
						var this_id = this.id.replace('tabs-','');
						//alert(this_id);
						jQuery('.wpl-tab-display').css('display','none');
						jQuery('#'+this_id).css('display','block');
						jQuery('.wpl-nav-tab').removeClass('nav-tab-special-active');
						jQuery('.wpl-nav-tab').addClass('nav-tab-special-inactive');
						jQuery('#tabs-'+this_id).addClass('nav-tab-special-active');						
						jQuery('#id-open-tab').val(this_id);

						
					});
				<?php
				}
				?>
			});			
		</script>
		<?php
	}
	
	function wpleads_display_global_settings()
	{	
		global $wpdb;
		global $wpleads_global_settings;
		$wpleads_global_settings = wpleads_get_global_settings_elements();
		$active_tab = 'main'; 
		if (isset($_REQUEST['open-tab']))
		{
			$active_tab = $_REQUEST['open-tab'];
		}

		wpleads_display_global_settings_js();
		wpleads_save_global_settings();

		echo '<h2 class="nav-tab-wrapper">';		
	
		foreach ($wpleads_global_settings as $key => $data)
		{
			?>
			<a  id='tabs-<?php echo $key; ?>' class="wpl-nav-tab nav-tab nav-tab-special<?php echo $active_tab == $key ? '-active' : '-inactive'; ?>"><?php echo $data['label']; ?></a> 
			<?php
		}
		echo '</h2>';
		echo "<form action='edit.php?post_type=wp-lead&page=wpleads_global_settings' method='POST'>";
		echo "<input type='hidden' name='nature' value='wpl-global-settings-save'>";
		echo "<input type='hidden' name='open-tab' id='id-open-tab' value='{$active_tab}'>";
		foreach ($wpleads_global_settings as $key => $array)
		{
			$these_settings = $wpleads_global_settings[$key]['options'];	
			wpleads_render_global_settings($key,$these_settings, $active_tab);
		}
		echo '<div style="float:left;padding-left:9px;padding-top:20px;">
				<input type="submit" value="Save Settings" tabindex="5" id="wpl-button-create-new-group-open" class="button-primary" >
			</div>';
		echo "</form>";
		
	}
	
	function wpleads_save_global_settings() 
	{
		//echo "here";exit;
		$wpleads_global_settings = wpleads_get_global_settings_elements();
		
		if (!isset($_POST['nature']))
			return;
	
		
		foreach ($wpleads_global_settings as $key=>$array)
		{	
			$wpleads_options = $wpleads_global_settings[$key]['options'];		
			//echo 1; 

			// loop through fields and save the data
			foreach ($wpleads_options as $option) 
			{
				//echo $option['id'].":".$_POST['main-landing-page-auto-format-forms']."<br>";
				$old = get_option($option['id']);				
				$new = $_POST[$option['id']];	
			
				if ((isset($new) && $new !== $old )|| !isset($old) ) 
				{
					//echo $option['id'];exit;
					$bool = update_option($option['id'],$new);								
				} 
				elseif ('' == $new && $old) 
				{
					$bool = update_option($option['id'],$option['default']);
				}
			} // end foreach		
		}
		
	}
	
?>