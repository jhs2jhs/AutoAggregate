/*global $wpdb;
var_dump(ABSPATH.'wp-load.php');
var_dump($wdpb);
var_dump(dirname(__FILE__).'/img/process.png');
//require_once(dirname(__FILE__).'/ab-functions.php');

add_action('admin_menu', 'my_plugin_menu');
//var_dump(ABSPATH.'wp-content/plugins/auto_aggregate/img/process.png');

function my_plugin_menu() {
	add_menu_page(
		'AutoAggregate Options', 
		'AutoAggregate', 'manage_options', 
		'AutoAggregate', 
		'my_plugin_options', 
		dirname(__FILE__).'/img/process.png');
}
function my_plugin_options() {
	global $wpdb;
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';
	
	$table_name = $wdpb->prefix."autoblogged";
	$sql = "SELECT * FROM $table_name";
	echo $sql;
	var_dump($wdpb);
	$ab_lists = $wpdb->get_results($sql);
	if ($ab_lists) {
		foreach ($ab_lists as $ab_list) {
			foreach ($ab_list as $ab) {
				echo "hello ".$ab."<br>";
			}
		}
	}
}*/




// Activate the plugin and create/upgrade the table as necessary
function ag_installOnActivation($force_upgrade = false) {
	global $wpdb, $ag_table_name;
	$installed_ver = get_option('autoaggregate_db_version');
	
	var_dump($installed_ver != DB_SCHEMA_AB_VERSION,  $force_upgrade == true);
	echo "hello world";

	// Run if installing for the first time or if upgrading from a previous version
	if ($installed_ver != DB_SCHEMA_AB_VERSION || $force_upgrade == true) {
		$sql = "CREATE TABLE " . $ag_table_name . " (
		`id` mediumint(9) NOT NULL auto_increment,
		`title` varchar(75) NOT NULL,
		`type` tinyint(4) NOT NULL,
		`url` text NOT NULL,
		`category` text,
		`enabled` tinyint(1),
		`addothercats` tinyint(1) NOT NULL,
		`addcatsastags` tinyint(1) NOT NULL,
		`tags` varchar(255) NOT NULL,
		`includeallwords` varchar(255) NOT NULL,
		`includeanywords` varchar(255) NOT NULL,
		`includephrase` varchar(255) NOT NULL,
		`includenowords` varchar(255) NOT NULL,
		`searchfor` text NOT NULL,
		`replacewith` text NOT NULL,
		`templates` text NOT NULL,
		`poststatus` varchar(10) NOT NULL,
		`customfield` longtext NOT NULL,
		`customfieldvalue` longtext NOT NULL,
		`saveimages` tinyint(1) NOT NULL,
		`createthumbs` tinyint(1) NOT NULL,
		`playerwidth` smallint(6) NOT NULL,
		`playerheight` smallint(6) NOT NULL,
		`uselinkinfo` tinyint(1) NOT NULL,
		`useauthorinfo` tinyint(1) NOT NULL,
		`customplayer` varchar(255) NOT NULL,
		`taggingengine` tinyint(1) NOT NULL,
		`randomcats` tinyint(1) NOT NULL,
		`usepostcats` tinyint(1) NOT NULL,
		`addpostcats` tinyint(1) NOT NULL,
		`author` text NOT NULL,
		`alt_author` text NOT NULL,
		`schedule` tinyint(1) NOT NULL,
		`updatefrequency` tinyint(4) NOT NULL,
		`post_processing` tinyint(1) NOT NULL,
		`max_posts` tinyint(4) NOT NULL,
		`posts_ratio` tinyint(4) NOT NULL,
		`last_updated` date NOT NULL,
		`update_countdown` tinyint(4) NOT NULL,
		`last_ping` date NOT NULL,
		`stats` text default NULL,
		`usefeeddate` tinyint(1) NOT NULL,
		`auto_aggregate_status` tinyint(1) NOT NULL,
		UNIQUE KEY `id` (`id`)
		);";

		if (file_exists(ABSPATH . "wp-admin/upgrade-functions.php")) {
			require_once(ABSPATH . "wp-admin/upgrade-functions.php");
		} elseif (file_exists(ABSPATH . "wp-admin/includes/upgrade-functions.php")) {
			require_once(ABSPATH . "wp-admin/upgrade-functions.php");
		}


		if (function_exists('dbDelta')) {
			$alterations = dbDelta($sql);
			//											echo "<ol>\n";
			//											foreach($alterations as $alteration) echo "<li>$alteration</li>\n";
			//											echo "</ol>\n";

			if (!empty($wpdb->last_error)) __d($wpdb->last_error, 'Last SQL error', 'blue');

			if (count($alterations) == 0) {
				if ($force_upgrade == true) {
					echo '<div id="dbupgrade" class="updated fade"><p><strong>'.__("Database Upgrade: ").'</strong>Your AutoAggregate database is already up-to-date.</div>';
				}
			} else {
				$sql = 'ALTER TABLE `'.$ag_table_name.'` CHANGE `url` `url` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL';
				$ret = $wpdb->query($sql);
				//if (!empty($wpdb->last_error)) __d($wpdb->last_error, 'Last SQL error', 'purple');

				$sql = 'ALTER TABLE `'.$ag_table_name.'` CHANGE `category` `category` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL';
				$ret = $wpdb->query($sql);
				//if (!empty($wpdb->last_error)) __d($wpdb->last_error, 'Last SQL error', 'orange');

				$sql = 'ALTER TABLE `'.$ag_table_name.'` CHANGE `searchfor` `searchfor` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL';
				$ret = $wpdb->query($sql);

				$sql = 'ALTER TABLE `'.$ag_table_name.'` CHANGE `replacewith` `replacewith` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL';
				$ret = $wpdb->query($sql);

				//Now do a check to make sure the update succeeded
				foreach ($wpdb->get_col("DESC ".ab_tableName(), 0) as $column ) {
					if ($column == 'last_ping') { // using a new column name from the latest upgrade
						update_option("autoaggregate_db_version", DB_SCHEMA_AB_VERSION);
						echo '<div id="dbupgrade" class="updated fade"><p><strong>'.__("Database Upgraded: ").'</strong>Your AutoAggregate database has just been upgraded to version '.DB_SCHEMA_AB_VERSION.'.</div>';
						$upgraded=true;
						continue;
					}
				}
				if (!$upgraded) {
					//if (function_exists('wp_nonce_url')) $navlink = wp_nonce_url($_SERVER['PHP_SELF'].'?page=AutoBloggedSupport&upgrade_db=1', 'autoblogged-nav');
					echo '<div id="sn-warning" class="error"><p><strong>'.__("Database Error: ").'</strong>Warning: Unable to install or upgrade the database. Please contact technical support or <a href="'.$navlink.'">click here</a> to try again.</div>';
				}
			}

			//change permissions of cache dir
			//@chmod(ab_plugin_dir() . '/cache', 0765);

		}
	} // end if
} // end function
