<?php
/*
Plugin Name: Auto Aggregate
Plugin URI: http://cs.nott.ac.uk/~jus
Description: Automatically creates posts and builds content from RSS or ATOM feeds, blog searches, and other sources.
Author: Jianhua Shao
Author URI: http://cs.nott.ac.uk/~jus
Version: 0.1
*/

//var_dump($wpdb);
$ag_table_name = $wpdb->prefix . "autoaggregate";
$ab_table_name = $wpdb->prefix . "autoblogged";
//var_dump($ag_table_name);

if (!class_exists('autoaggregate')) {
	class autoaggregate	{

		//---------------------------------------------------------------
		function autoaggregate() {
			$this->__construct();
		}

		//---------------------------------------------------------------
		function __construct() {
			global $wpdb, $ab_options, $feedtypes;

			// WordPress hooks
			add_action("admin_menu", array(&$this,"ag_addAdminPages"));
			register_activation_hook(__FILE__,"ag_installOnActivation");

			// Load php4 compatibility functions if needed
			if (version_compare(PHP_VERSION, '5.0.0', '<')) require_once(dirname(__FILE__).'/php4-compat.php');
			//$ab_options = ag_getOptions();

			$feedtypes = array(
			"1" => "RSS Feed",
			"2" => "Google Blog Search",
			"3" => "IceRocket Blog Search",
			"4" => "Twitter Search",
			"7" => "Yahoo! News Search",
			"8" => "Flickr Tag Search",
			"9" => "YouTube Tag Search",
			"10" => "Yahoo! Video Search",
			);
		}


		//---------------------------------------------------------------
		function ag_addAdminPages(){

			add_menu_page('AutoAggregate', 'AutoAggregate', 'manage_options', 'AutoAggregate', 'ab_queue' );
			add_submenu_page('AutoAggregate', 'AutoAggregate Queue', 'Queue', 'manage_options', 'Queue', 'ab_queue');
			add_submenu_page('AutoAggregate', 'AutoAggregate Proofed', 'Proofed', 'manage_options', 'Proofed', 'ab_proofed');
			add_submenu_page('AutoAggregate', 'AutoAggregate Rejected', 'Rejected', 'manage_options', 'Rejected', 'ab_rejected');

			//load the scripts we will need
			if (stristr($_REQUEST['page'], 'AutoAggregate')) {
				wp_enqueue_script('post');
				wp_enqueue_script('thickbox');
				wp_enqueue_script('postbox');
				wp_enqueue_script('admin-categories');
				wp_enqueue_script('admin-tags');
			}
		}
		
		
		//var_dump($_REQUEST);
		 

	}
}

function auto_aggregate_accept($id){
	global $wpdb, $ag_table_name, $ab_table_name;
	$sql = "SELECT * FROM $ag_table_name WHERE id = '$id'";
	$ab_lists = $wpdb->get_results($sql);
	if ($ab_lists) {
		foreach ($ab_lists as $ab_list) {
			$table_array = array( 
			'title' => $ab_list->title, 
			'type' => 1, 
			'url' => $ab_list->url,
			'category' => null,
			'enabled' => 1,
			'addothercats' => 1,
			'addcatsastags' => 1,
			'tags' => 'a:0:{}',
			'includeallwords' => null,
			'includeanywords' => null,
			'includephrase' => null,
			'includenowords' => null,
			'searchfor' => 'a:0:{}',
			'replacewith' => 'a:0:{}',
			'templates' => '<p>%excerpt%</p>
%if:video%<p>%video%</p>%endif:video%
%if:thumbnail%<p>%thumbnail%</p>%endif:thumbnail%
[Read more here|Read the original here|Read more from the original source|Continued here|Read more|More here|View original post here|More|See more here|See original here|Originally posted here|Here is the original post|See the original post|The rest is here|Read the rest here|See the rest here|Go here to read the rest|Go here to see the original|See the original post here|Read the original post|Original post|Read the original|Link|Excerpt from|View post|Visit link|Follow this link|Continue reading here|See the article here|Read this article|Read more]:[<br />| ]
<a target=\"_blank\" href=\"%link%\" title=\"%title%\">%title%</a>
',
			'poststatus' => 'publish',
			'customfield' => 'a:0:{}',
			'customfieldvalue' => 'a:0:{}',
			'saveimages' => 0,
			'createthumbs' => 1,
			'playerwidth' => 250,
			'playerheight' => 206,
			'uselinkinfo' => 0,
			'useauthorinfo' => 0,
			'customplayer' => null,
			'taggingengine' => 0,
			'randomcats' => 1,
			'usepostcats' => 1,
			'addpostcats' => 0,
			'author' => '(Use random author)',
			'alt_author' => '(Create new author)',
			'schedule' => 0,
			'updatefrequency' => 0,
			'post_processing' => 1,
			'max_posts' => 20,
			'posts_ratio' => 100,
			'last_updated' => '0000-00-00',
			'update_countdown' => 0,
			'last_ping' => '000-00-00',
			'stats' => null,
			'usefeeddate' => 1
			);
			$rows_affected = $wpdb->insert( $ab_table_name, $table_array);
			$wpdb->query("UPDATE $ag_table_name SET auto_aggregate_status = 2 WHERE id = $id");
		}
	}	
}

function auto_aggregate_reject($id){
	global $wpdb, $ag_table_name, $ab_table_name;
	$sql = "SELECT * FROM $ag_table_name WHERE id = '$id'";
	$ab_lists = $wpdb->get_results($sql);
	if ($ab_lists) {
		foreach ($ab_lists as $ab_list) {
			$wpdb->query("UPDATE $ag_table_name SET auto_aggregate_status = 3 WHERE id = $id");
		}
	}
}


// Main page
if (!function_exists('ab_queue')) {
	function ab_queue() {
		global $wpdb, $ag_table_name;
		if (!class_exists('autoaggregate')) ag_createClass();
		//var_dump($_GET);
		switch ($_GET['action']) {
			case 'Accept':
			auto_aggregate_accept($_GET['id']);
			break;

			case 'Reject':
			auto_aggregate_reject($_GET['id']);
			break;
		}
		echo '<div class="wrap">';
		echo '<p>List of feed request in the queue.</p>';
		echo '</div>';
		$sql = "SELECT * FROM $ag_table_name WHERE auto_aggregate_status = 1";
		echo "<table><tr><th>ID</th><th>Title</th><th>RSSURL</th><th>Action</th></tr>";
		$ab_lists = $wpdb->get_results($sql);
		if ($ab_lists) {
			foreach ($ab_lists as $ab_list) {
				echo "<tr>";
				echo "<td>".$ab_list->id."</td>";
				echo "<td>".$ab_list->title."</td>";
				echo "<td>".$ab_list->url."</td>";
				echo '<td>
					<form action="" method="get">
					<input type="hidden" name="page" value="AutoAggregate">
					<input type="hidden" name="id" value="'.$ab_list->id.'">
					<input type="submit" name="action" value="Accept">
					<input type="submit" name="action" value="Reject">
					</form>
					</td>';
				echo "<tr>";
			}
		}
		echo "</table>";
		return;
	}
}
// Main page
if (!function_exists('ab_proofed')) {
	function ab_proofed() {
		global $wpdb, $ag_table_name;
		if (!class_exists('autoaggregate')) ag_createClass();
		echo '<div class="wrap">';
		echo '<p>List of feed request in the queue.</p>';
		echo '</div>';
		$sql = "SELECT * FROM $ag_table_name WHERE auto_aggregate_status = 2";
		echo "<table><tr><th>ID</th><th>Title</th><th>RSSURL</th></tr>";
		$ab_lists = $wpdb->get_results($sql);
		if ($ab_lists) {
			foreach ($ab_lists as $ab_list) {
				echo "<tr>";
				echo "<td>".$ab_list->id."</td>";
				echo "<td>".$ab_list->title."</td>";
				echo "<td>".$ab_list->url."</td>";
				echo "<tr>";
			}
		}
		echo "</table>";
		return;
	}
}
// Main page
if (!function_exists('ab_rejected')) {
	function ab_rejected() {
		global $wpdb, $ag_table_name;
		if (!class_exists('autoaggregate')) ag_createClass();
		echo '<div class="wrap">';
		echo '<p>List of feed request in the queue.</p>';
		echo '</div>';
		$sql = "SELECT * FROM $ag_table_name WHERE auto_aggregate_status = 3";
		echo "<table><tr><th>ID</th><th>Title</th><th>RSSURL</th></tr>";
		$ab_lists = $wpdb->get_results($sql);
		if ($ab_lists) {
			foreach ($ab_lists as $ab_list) {
				echo "<tr>";
				echo "<td>".$ab_list->id."</td>";
				echo "<td>".$ab_list->title."</td>";
				echo "<td>".$ab_list->url."</td>";
				echo "<tr>";
			}
		}
		echo "</table>";
		return;
	}
}


//---------------------------------------------------------------
if (!function_exists('ag_createClass')) {
	function ag_createClass() {
		// Create class instance
		if (class_exists('autoaggregate')) {
			global $autoaggregate;
			global $wp_version;
			$autoaggregate = new autoaggregate();
		}
	}
}

add_action('plugins_loaded', 'ag_createClass');

// Activate the plugin and create/upgrade the table as necessary
function ag_installOnActivation($force_upgrade = false) {
	global $wpdb, $ag_table_name;
	$installed_ver = get_option('autoaggregate_db_version');
	
	//var_dump($installed_ver != DB_SCHEMA_AB_VERSION,  $force_upgrade == true);
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

function auto_aggregate_install(){
	global $wpdb, $ag_table_name;
	$autoaggregate_db_version = get_option('autoaggregate_db_version');
	//var_dump($wpdb);
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
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	add_option("autoaggregate_db_version", $autoaggregate_db_version);
}

register_activation_hook(__FILE__,'auto_aggregate_install');

?>