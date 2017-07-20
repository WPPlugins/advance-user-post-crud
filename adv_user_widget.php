<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
/**widget of post count***/
add_action('wp_dashboard_setup', 'adv_user_crud_dashboard_widgets');
function adv_user_crud_dashboard_widgets() {
	global $wp_meta_boxes;
	wp_add_dashboard_widget('adv_user_crud', 'Advance User Post CRUD Widget', 'adv_user_crud_widget_content');
}
function adv_user_crud_widget_content() {
	$args = array(
		'public'   => true,
	);
	$post_types = get_post_types($args);?>
	<table>
	<tr>
		<th>Users</th>
		<th>Media</th>
		<th>Post</th>
		<th>Page</th>
		<th>Others</th>
		<th>Total</th>
	</tr>
	<tr>
		<?php
		$users = new WP_User_Query( array( 'role__not_in' => 'Subscriber' ) );
		$total_count = 0;
		$user_count=0;
		foreach($users->results as $data => $user)
		{
			$users_id = $user->data->ID;
			$user_capability = user_can( $users_id, 'publish_posts' );
			if ($user_capability):
			 	if($user_count < 5):
					global $wpdb;
					$total_count1 = 0;

					$users_name = $user->data->display_name;
					
					echo '<td style="text-align: center;"><a href="'.admin_url( "options-general.php?page=adv-user-crud&user_id=") .$users_id . '">'.$users_name.'</a></td>';
					$attachment_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_author='".$users_id."'");
					
					echo '<td style="text-align: center;">'.$attachment_count.'</td>';
					$k=1;
					foreach ($post_types as $key => $post_type_name)
					{
						if ($k<3):
							if($post_type_name!="attachment"):
								$post_count = count_user_posts( $users_id , $post_type_name );
								echo '<td style="text-align: center;">'.$post_count.'</td>';
							endif;
						endif;
						$total_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE  post_author ='".$users_id."' AND (post_status = 'publish' OR post_status = 'inherit') AND post_type = '".$post_type_name."'");
						$total_count1 = $total_count1 + $total_count;
						$count_other_posts = $total_count + $count_other_posts;
						if ($k==3)
						{
							$count_other_posts = 0;
						}
						$k++;
					}
					echo '<td style="text-align: center;">'.$count_other_posts.'</td>';
					echo '<td style="text-align: center;">'.$total_count1.'</td>';
					echo '</tr>';
				endif;
			$user_count++;
			endif;
		}
	echo '<tr><td><a href="'.admin_url("options-general.php?page=adv-user-crud-post-count").'" class="button button-primary">See all</a></td></tr>';
?>
	</table>
<?php
}
