<?php 
require_once('../../../wp-load.php');
//var_dump($wpdb);
//var_dump ($_REQUEST);
if ($_REQUEST['rssurl'] != null){
	$rssurl = $_REQUEST['rssurl'];
	$title = "";
	if ($_REQUEST['title'] != null){
		$title = $_REQUEST['title'];
	}
	/*$contact = "";
	if ($_REQUEST['contact'] != null){
		$contact = $_REQUEST['contact'];
	}*/
	request_process($rssurl, $title);//, $contact);
} else {
	echo "Please pass your rssurl";
}

$ag_table_name = $wpdb->prefix . "autoaggregate";
function request_process($rssurl, $title){//, $contact){
	global $wpdb, $ag_table_name;
	$sql = "SELECT * FROM $ag_table_name WHERE url = '$rssurl'";
	//echo $sql;
	$ab_lists = $wpdb->get_results($sql);
	//var_dump($ab_lists);
	//var_dump(count($ab_lists));
	//echo "<br>";
	if (count($ab_lists) > 0) {
		echo "<h1>Sorry</h1>";
		echo "Your RSS URL has been entered before, any query please feel free to contact us here????";
	} else {
		$table_array = array( 
			'title' => $title, 
			'type' => 1, 
			'url' => $rssurl,
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
			'usefeeddate' => 1,
			'auto_aggregate_status' => 1,
			);
		$rows_affected = $wpdb->insert( $ag_table_name, $table_array);
		//var_dump( $rows_affected);
		//var_dump($table_array);
		//var_dump($ag_table_name);
		echo "<h1>Thanks</h1>";
		echo "Thanks very much for you to tell us your RSS URL";
	}
}

echo "<br>";
//echo $rssurl, $title, $contact;
?>