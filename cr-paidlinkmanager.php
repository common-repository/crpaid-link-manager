<?php
/*
Plugin Name: [CR]Paid Link Manager
Plugin URI: http://bayu.freelancer.web.id/crpaid-link-manager-plugin-to-manage-your-paid-links-life-cycle/
Description: This plugin will help you manager which area you have for each link group and how long each plugin will live
Version: 0.5
Author: Arief Bayu Purwanto
Author URI: http://bayu.freelancer.web.id/
*/

/*
Main feature:
 * Ability to separate group
 * Ability to set per link age
 * Ability to store per link notes (usefull for client info, etc. So that you can remind them link expiring)
*/


/*
 TODO:
  * [partial] Add plugin API for use by other plugins of themes
  * [OK] Add expiring cron to send soon to be expired cron
  * Add javascript on date range input
  * Add another config for separator in 'coma' mode
  * [OK] Add infinity period for particular links
  * Ask community!
*/

define('CR_PAID_LINK_MANAGER_DATABASE_VERSION', '1.1');

add_action('init', 'cr_paid_link_manager_init');
function cr_paid_link_manager_init(){
    cr_paid_link_manager_admin_warnings();
}

function cr_paid_link_manager_admin_warnings(){
	//FIXME: to be defined
	/*if ( !get_option('cl_post_pingfm_api_key') && !isset($_POST['submit']) ) {
		function cr_post_2_pingfm_warning() {
			echo "
			<div id='crpost2pingfm-warning' class='updated fade'><p><strong>[CR]Post2PingFM is almost ready</strong> You must <a href='plugins.php?page=cr_post_2_pingfm_submit_config_form'>enter your Ping.FM API Key</a> for it to work.</p></div>
			";
		}
		add_action('admin_notices', 'cr_post_2_pingfm_warning');
		return;
	}*/
}


add_action('admin_menu', 'cr_paid_link_manager_submit_config_admin');
function cr_paid_link_manager_submit_config_admin()
{
	add_menu_page( 'PLManager', 'PLManager', 8, 'cr_paid_link_manager_admin_menu');
	add_submenu_page( 'cr_paid_link_manager_admin_menu', 'PLManager', 'LINK Group', 8, 'cr_paid_link_manager_admin_menu', 'cr_paid_link_manager_admin_menu');
	add_submenu_page( 'cr_paid_link_manager_admin_menu', 'PLManager', 'LINK List', 8, 'cr_paid_link_manager_group_list_html', 'cr_paid_link_manager_group_list_html');
}

function cr_paid_link_manager_admin_menu()
{
	global $wpdb;
	
	$act = isset( $_GET['act'] ) ? $_GET['act'] : '';

	$table_link_groups = $wpdb->prefix . "cr_plmanager_link_groups";
	$table_link_list   = $wpdb->prefix . "cr_plmanager_link_list";


	if('edit' == $act && isset( $_POST['submit']) )
	{
		$wpdb->update($table_link_groups, array('group_name'=>$_POST['group_name'], 'group_description'=>$_POST['group_description']), array('group_id'=>$_POST['group_id'] ));
	}
	else if(in_array($act, array('', 'add')) && isset( $_POST['submit']) )
	{
		$wpdb->insert($table_link_groups, array('group_name'=>$_POST['group_name'], 'group_description'=>$_POST['group_description']));
	}
	else if('delete' == $act && isset( $_POST['submit']) )
	{
		$wpdb->query("DELETE FROM $table_link_groups WHERE group_id = '" . $_POST['group_id'] . "'");
		$wpdb->query("DELETE FROM $table_link_list WHERE group_id = '" . $_POST['group_id'] . "'");
	}


	$table_body = '';
	
	$link_groups = $wpdb->get_results("SELECT * FROM $table_link_groups");

	$cnt = 0;
	foreach( $link_groups as $group)
	{
		$cnt++;
		$alternate = ($cnt %2 == 0 ? 'class="alternate"' : '');
		$table_body .= "<tr $alternate>
	<td>" . $group->group_id . "</td>
	<td>" . $group->group_name . "</td>
	<td>" . $group->group_description . "</td>
	<td>[<a href='".$_SERVER["REQUEST_URI"]."&act=edit&group_id=" . $group->group_id . "'>Edit</a>] -
		[<a href='".$_SERVER["REQUEST_URI"]."&act=delete&group_id=" . $group->group_id . "'>Delete</a>]</td>
	</tr>";
	}

?>
<div class="wrap">
<h2>[CR]Paid Link Manager - LINK Groups Configuration</h2>

<p>Here you can set a defined groups for your links.</p>

<table class="widefat fixed">
<thead>
	<tr>
		<th class="manage-column">Group ID</th>
		<th class="manage-column">Group Name</th>
		<th class="manage-column">Description</th>
		<th class="manage-column">Action</th>
	</tr>
<thead>
<tbody>
	<?php echo $table_body; ?>
</tbody>
</table>
<a href="<?php echo $_SERVER["PHP_SELF"]."?page=cr_paid_link_manager_admin_menu"; ?>">Add new link group</a>
<?php

if( in_array($act, array('new', 'edit')) && !isset($_POST['submit']) )
{
	$group_id = isset( $_GET['group_id'] ) ? trim( $_GET['group_id'] ) : 0;
	$sql = "SELECT * FROM $table_link_groups WHERE group_id = '".$wpdb->escape($group_id)."'";
	
	$group = $wpdb->get_row( $sql );
?>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<br />
<h3><?php echo ('edit' !== $act ? 'Add' : 'Edit')?> Link Group</h3>
<h4>Group Name</h4>
<input type="text" name="group_name" value="<?php echo $group->group_name; ?>" style="width: 80%">
<h4>Group Description</h4>
<textarea name="group_description" style="width: 80%; height: 100px;"><?php echo $group->group_description; ?></textarea>

<div class="submit">
<input type="hidden" name="group_id" value="<?php echo $group->group_id; ?>" /></div>
<input type="submit" name="submit" value="Submit" /></div>
</form>
<?php
}
else if('delete' == $act && !isset($_POST['submit']) )
{
	$group_id = isset( $_GET['group_id'] ) ? trim( $_GET['group_id'] ) : 0;
	$sql = "SELECT * FROM $table_link_groups WHERE group_id = '".$wpdb->escape($group_id)."'";
	
	$group = $wpdb->get_row( $sql );
?>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<br />
<h3>Delete Link Group</h3>
<div class="submit">
<strong>WARNING: Deleting link group will also delete it's individual link attached to it!</strong>
<input type="hidden" name="group_id" value="<?php echo $group->group_id; ?>" /></div>
<input type="submit" name="submit" value="DELETE" /></div>
</form>
<?php
}
?>

</div>
<?php
	cr_paid_link_manager_admin_footer_info();
}

function cr_paid_link_manager_group_list_html()
{
	global $wpdb;
	
	$act = isset( $_GET['act'] ) ? $_GET['act'] : '';
	$group_id = isset( $_GET['group_id'] ) ? $_GET['group_id'] : '0';

	$table_link_groups = $wpdb->prefix . "cr_plmanager_link_groups";
	$table_link_list   = $wpdb->prefix . "cr_plmanager_link_list";

	if('edit' == $act && isset( $_POST['submit']) )
	{
		$wpdb->update($table_link_list, array('link_text'=>$_POST['link_text'],
				'link_url'=>$_POST['link_url'],
				'link_date1'=>$_POST['link_date1'],
				'link_date2'=>$_POST['link_date2'],
				'link_note'=>$_POST['link_note']),
			array('link_id'=>$_POST['link_id'] ));
	}
	else if(in_array($act, array('new', 'add')) && isset( $_POST['submit']) )
	{
		$wpdb->insert($table_link_list, array('link_group_id'=>$group_id,
				'link_text'=>$_POST['link_text'],
				'link_url'=>$_POST['link_url'],
				'link_date1'=>$_POST['link_date1'],
				'link_date2'=>$_POST['link_date2'],
				'link_note'=>$_POST['link_note']));
	}
	else if('delete' == $act && isset( $_POST['submit']) )
	{
		$wpdb->query("DELETE FROM $table_link_list WHERE link_id = '" . $_POST['link_id'] . "'");
	}

$groups = $wpdb->get_results("SELECT * FROM $table_link_groups");
$txt_groups = '';
foreach($groups as $group)
{
	$selected = $group_id == $group->group_id ? 'selected="selected"' : '';
	if('0' == $group_id)
		$group_id = $group->group_id;
	$txt_groups .= "<option value='$group->group_id' $selected>$group->group_name</option>";
}

	$links = $wpdb->get_results("SELECT * FROM $table_link_list WHERE link_group_id = '$group_id'");

	$cnt = 0;
	foreach( $links as $link)
	{
		$cnt++;
		$alternate = ($cnt %2 == 0 ? 'class="alternate"' : '');
		$table_body .= "<tr $alternate>
	<td>" . $link->link_id . "</td>
	<td>" . $link->link_text . "</td>
	<td>" . $link->link_url . "</td>
	<td>" . $link->link_date1 . " to " . ('0000-00-00' == $link->link_date2 ? 'infinity' : $link->link_date2) . "</td>
	<td>[<a href='".$_SERVER["REQUEST_URI"]."&act=edit&link_id=" . $link->link_id . "'>Edit</a>] -
		[<a href='".$_SERVER["REQUEST_URI"]."&act=delete&link_id=" . $link->link_id . "'>Delete</a>]</td>
	</tr>";
	}

?>
<div class="wrap">
<h2>[CR]Paid Link Manager - LINK List Configuration</h2>

<p>This is your links for a particular link group.</p>

<p>Select Link Group <select id="cr_plmanager_group_id" onchange="javascript:top.location.replace('<?php echo $_SERVER['PHP_SELF'] . '?page=cr_paid_link_manager_group_list_html&group_id='; ?>' + document.getElementById('cr_plmanager_group_id').value)">
<?php echo $txt_groups; ?>
</select></p>
<table class="widefat fixed">
<thead>
	<tr>
		<th class="manage-column">Link ID</th>
		<th class="manage-column">Text</th>
		<th class="manage-column">URL</th>
		<th class="manage-column">Display Period</th>
		<th class="manage-column">Options</th>
	</tr>
<thead>
<tbody>
	<?php echo $table_body; ?>
</tbody>
</table>
<a href="<?php echo $_SERVER["PHP_SELF"]."?page=cr_paid_link_manager_group_list_html&group_id=" . $group_id; ?>&act=new">Add new link</a>



<?php

if( in_array($act, array('new', 'edit')) && !isset($_POST['submit']) )
{
	$link_id = isset( $_GET['link_id'] ) ? trim( $_GET['link_id'] ) : 0;
	$sql = "SELECT * FROM $table_link_list WHERE link_id = '".$wpdb->escape($link_id)."'";

	$link = $wpdb->get_row( $sql );
	
	
?>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<br />
<h3><?php echo ('edit' !== $act ? 'Add' : 'Edit')?> Link Info</h3>
<h4>Link Title</h4>
<input type="text" name="link_text" value="<?php echo $link->link_text; ?>" style="width: 80%">
<h4>Link URL</h4>
<p>Don't forget to add <strong>http://</strong> before your URL.</p>
<input type="text" name="link_url" value="<?php echo $link->link_url; ?>" style="width: 80%">
<h4>Link Display Period</h4>
<p>Acceptable format is <strong>YYYY-MM-DD</strong>. So, if you want to put <strong>12 November 2010</strong>, it would be <strong>2010-11-12</strong>. To make this link live forever, put '0000-00-00' on date2.<br />
Eg: You want to set certain link never expired, set it like this: <strong>'<?php echo date('Y-m-d'); ?>'</strong> to <strong>'0000-00-00'</strong></p>
<input type="text" name="link_date1" value="<?php echo ( !$link->link_date1 ? date('Y-m-d') : $link->link_date1 ); ?>" size="12"> to 
<input type="text" name="link_date2" value="<?php echo ( !$link->link_date2 ? '0000-00-00' : $link->link_date2 ); ?>" size="12">
<h4>Link Notes</h4>
<p>Add whatever info you want here. Be it transaction ID, client email, or anything else that need to be tracked for this particular URL.</p>
<textarea name="link_note" style="width: 80%; height: 100px;"><?php echo $link->link_note; ?></textarea>

<div class="submit">
<input type="hidden" name="link_id" value="<?php echo $link->link_id; ?>" /></div>
<input type="submit" name="submit" value="Submit" /></div>
</form>
<?php
}
else if('delete' == $act && !isset($_POST['submit']) )
{
	$link_id = isset( $_GET['link_id'] ) ? trim( $_GET['link_id'] ) : 0;
	$sql = "SELECT * FROM $table_link_list WHERE link_id = '".$wpdb->escape($link_id)."'";
	
	$group = $wpdb->get_row( $sql );
?>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<br />
<h3>Delete Link</h3>
<div class="submit">
<strong>WARNING: Make sure this LINK is already expired. Or else, your client will be mad or even sue you!</strong>
<input type="hidden" name="link_id" value="<?php echo $group->link_id; ?>" /></div>
<input type="submit" name="submit" value="DELETE" /></div>
</form>
<?php
}
?>

</div>
<?php
	cr_paid_link_manager_admin_footer_info();
}

//install database and prepare initial data
register_activation_hook(__FILE__,'cr_paid_link_manager_script_install');
function cr_paid_link_manager_script_install()
{
	global $wpdb;
	
	if( false === wp_get_schedule( 'cr_paid_link_manager_generate_expiring_link_email_action' ) )
	{
		wp_schedule_event(time(), 'daily', 'cr_paid_link_manager_generate_expiring_link_email_action');
	}
	
	$table_link_groups = $wpdb->prefix . "cr_plmanager_link_groups";
	$table_link_list   = $wpdb->prefix . "cr_plmanager_link_list";
	
	$default_group_name = 'frontpage';
	$default_group_description = 'Only hold links for frontpage only';
	
	if( $wpdb->get_var("SHOW TABLES LIKE '$table_link_groups'") != $table_link_groups )
	{
		$sql_create_link_groups = "CREATE TABLE $table_link_groups (
	group_id INT NOT NULL AUTO_INCREMENT,
	group_name VARCHAR(45) NULL,
	group_description VARCHAR(250) NULL,
	PRIMARY KEY  (group_id)
);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $sql_create_link_groups );
		
		$rows_affected = $wpdb->insert( $table_link_groups,
			array( 'group_name' => $default_group_name,
						 'group_description' => $default_group_description));
		add_option('cr_paid_link_manager_database_version', CR_PAID_LINK_MANAGER_DATABASE_VERSION);
	}
	
	if( $wpdb->get_var("SHOW TABLES LIKE '$table_link_list'") != $table_link_list )
	{
		$sql_create_link_list = "CREATE TABLE $table_link_list (
	link_id INT NOT NULL AUTO_INCREMENT ,
	link_group_id VARCHAR(45) NOT NULL ,
	link_text VARCHAR(250) NOT NULL ,
	link_url VARCHAR(250) NOT NULL ,
	link_date1 DATE NOT NULL ,
	link_date2 DATE NOT NULL ,
	link_note TEXT NULL ,
	PRIMARY KEY  (link_id, link_group_id)
);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $sql_create_link_list );
	}
}

add_action( 'admin_init', 'cr_paid_link_manager_admin_init' );
function cr_paid_link_manager_admin_init()
{
	//do installation process
	$installed_db_version = get_option('cr_paid_link_manager_database_version');
	
	if( $installed_db_version != CR_PAID_LINK_MANAGER_DATABASE_VERSION )
	{
		//this update version, I only introduce new cron schedule
		if( false == wp_get_schedule( 'cr_paid_link_manager_generate_expiring_link_email_action' ) )
		{
			wp_schedule_event(time(), 'daily', 'cr_paid_link_manager_generate_expiring_link_email_action');
		}
		update_option( 'cr_paid_link_manager_database_version', CR_PAID_LINK_MANAGER_DATABASE_VERSION );
	}
}

register_deactivation_hook(__FILE__,'cr_paid_link_manager_script_uninstall');
function cr_paid_link_manager_script_uninstall()
{
	//remove any cron
	wp_clear_scheduled_hook('cr_paid_link_manager_generate_expiring_link_email_action');
}

add_action('cr_paid_link_manager_generate_expiring_link_email_action','cr_paid_link_manager_generate_expiring_link_email');
function cr_paid_link_manager_generate_expiring_link_email()
{
	global $wpdb;
	
	$expiring_threshold = get_option( 'cr_paid_link_manager_link_expiring_threshold', '7' );
	$expiring_email_to  = get_option( 'cr_paid_link_manager_expiring_email_to', get_option('admin_email') );
	list($y, $m, $d) = explode('-', date('Y-m-d'));
	$date_threshold = date('Y-m-d', mktime( 0, 0, 0, $m, $d+$expiring_threshold, $y));
	$table_link_groups = $wpdb->prefix . "cr_plmanager_link_groups";
	$table_link_list   = $wpdb->prefix . "cr_plmanager_link_list";
	$sql = "SELECT p.group_name, l.link_text, l.link_url, l.link_note, l.link_date2
FROM $table_link_groups p
JOIN $table_link_list l ON l.link_group_id = p.group_id
WHERE link_date2 <> '0000-00-00'
AND link_date2 < '$date_threshold'";
	
	$links = $wpdb->get_results( $sql );
	$link_mail = '';
	
	if( 0 == count( $links ) )
	{
		return false;//no expiring link(s)
	}

	foreach($links as $link)
	{
		$link_mail .= "Group: " . $link->group_name . "\r\n";
		$link_mail .= "Text: " . $link->link_text . "\r\n";
		$link_mail .= "URL: " . $link->link_url . "\r\n";
		$link_mail .= "Expire Date: " . $link->link_date2 . "\r\n";
		$link_mail .= "Note: " . $link->link_note . "\r\n";
		
	}
	
	$email_subj = "Expiring Link - " . get_option('blogname');
	$email_text = "Below are link(s) that soon to be expired:\r\n";
	$email_text .= $link_mail;
	$email_text .= "\r\n\r\n\r\nEmail generated by [CR]PaidLinkManager\r\n";
	$email_text .= "Like this plugin? Please consider donation: http://bayu.freelancer.web.id/about/";

	
	wp_mail($expiring_email_to, $email_subj, $email_text);
}

function cr_paid_link_manager_admin_footer_info()
{
?>
<div style="margin-top: 25px; text-align: center; font-size: 90%;">Plugin by <a href="http://bayu.freelancer.web.id/">Arief Bayu Purwanto</a>.<br />
For more questions and or feature requests, submit it to <a href="http://bayu.freelancer.web.id/oss/crpaid-link-manager-plugin-to-manage-your-paid-links-life-cycle/">official project page</a>.<br />
Got money from this plugin? please <a href="http://bayu.freelancer.web.id/about/">consider donation</a>.
</div>
<?php
}

// ========================== PUBLIC  API ==========================
// ========================== PUBLIC  API ==========================
// ========================== PUBLIC  API ==========================

/**
 * This function will display (echo) links from certain group
 * @param integer $group_id links will be extracted from this group id
 * @param integer $mode accepted value is 'list' and 'coma'
 * @param integer $separator only work on $mode = 'coma'. Is used to separate each link
 *
 */
function cr_paid_link_manager_show_links($group_id = 0, $mode = 'list', $separator = ', ')
{
	global $wpdb;
	
	$table_link_list = $wpdb->prefix . "cr_plmanager_link_list";
	$sql = "SELECT * FROM $table_link_list WHERE link_group_id = '$group_id' AND ( ( DATE( NOW() ) BETWEEN link_date1 AND link_date2 ) OR ( link_date2 = '0000-00-00' ) )";
	//echo $sql;
	$links = $wpdb->get_results( $sql );
	
	if( 'list' == $mode )
	{
		echo "<ul>";
		foreach($links as $link)
		{
			echo "<li><a href='".$link->link_url."'>".$link->link_text."</a></li>";
			
		}
		echo "</ul>";
	}
	else if('coma' == $mode)
	{
		$arr_link = array();
		foreach($links as $link)
		{
			$arr_link[] = "<a href='".$link->link_url."'>".$link->link_text."</a>";
		}
		
		echo implode( $separator, $arr_link);
	}

}

// ========================== PUBLIC  API ==========================
// ========================== PUBLIC  API ==========================
// ========================== PUBLIC  API ==========================



// ========================== WIDGET CODE ==========================
// ========================== WIDGET CODE ==========================
// ========================== WIDGET CODE ==========================

add_action('widgets_init', create_function('', 'return register_widget("Cr_PlManager_Widget");'));
class Cr_PlManager_Widget extends WP_Widget
{
	function Cr_PlManager_Widget()
	{
		parent::WP_Widget(false, $name = '[CR]PL Manager');
	}
	
	function widget_content( $group_id, $mode )
	{
		global $wpdb;
		
		$table_link_list = $wpdb->prefix . "cr_plmanager_link_list";
		$sql = "SELECT * FROM $table_link_list WHERE link_group_id = '$group_id' AND ( ( DATE( NOW() ) BETWEEN link_date1 AND link_date2 ) OR ( link_date2 = '0000-00-00' ) )";
		//echo $sql;
		$links = $wpdb->get_results( $sql );
		
		if( 'list' == $mode )
		{
			echo "<ul>";
			foreach($links as $link)
			{
				echo "<li><a href='".$link->link_url."'>".$link->link_text."</a></li>";
				
			}
			echo "</ul>";
		}
		else if('coma' == $mode)
		{
			$arr_link = array();
			foreach($links as $link)
			{
				$arr_link[] = "<a href='".$link->link_url."'>".$link->link_text."</a>";
			}
			
			echo '<div class="textwidget">' . implode(', ', $arr_link) . '</div>';
		}
	}
	
	function widget($args, $instance)
	{
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
			
			echo $this->widget_content( $instance['group_id'], $instance['display_mode'] );
			
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['group_id'] = strip_tags($new_instance['group_id']);
		$instance['display_mode'] = strip_tags($new_instance['display_mode']);
		return $instance;
	}
	
	function form( $instance )
	{
		global $wpdb;
		$title = esc_attr($instance['title']);
		$group_id = esc_attr($instance['group_id']);
		$display_mode = esc_attr($instance['display_mode']);
		?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> 
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('group_id'); ?>"><?php _e('Group:'); ?>
					<select id="<?php echo $this->get_field_id('group_id'); ?>" name="<?php echo $this->get_field_name('group_id'); ?>">
						
						<?php
$table_link_groups = $wpdb->prefix . "cr_plmanager_link_groups";
$groups = $wpdb->get_results("SELECT * FROM $table_link_groups");
$txt_groups = '';
foreach($groups as $group)
{
	$selected = $group_id == $group->group_id ? 'selected="selected"' : '';
	$txt_groups .= "<option value='$group->group_id' $selected>$group->group_name</option>";
}
echo $txt_groups;
						?>
					</select>
				</label></p>
				<p><label for="<?php echo $this->get_field_id('display_mode'); ?>"><?php _e('Display Mode:'); ?> 
				<select id="<?php echo $this->get_field_id('display_mode'); ?>" name="<?php echo $this->get_field_name('display_mode'); ?>">
					<option value="list" <?php echo ('list' == $display_mode) ? 'selected="selected"' : ''; ?>>list</option>
					<option value="coma" <?php echo ('coma' == $display_mode) ? 'selected="selected"' : ''; ?>>coma separated</option>
				</select>
		<?php
	}
}

// ========================== WIDGET CODE ==========================
// ========================== WIDGET CODE ==========================
// ========================== WIDGET CODE ==========================







