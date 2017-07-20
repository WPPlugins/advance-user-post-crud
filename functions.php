<?php if ( ! defined( 'ABSPATH' ) ) exit; 
class adv_user_crud {
	function adv_user_crud_scripts() {
	    wp_enqueue_style('adv-user-crud-css', plugins_url( 'css/style.css', __FILE__ ));
	}
	function adv_user_crud_page(){
		echo '<div class="post_count_table">';
		echo '<h2>Advance user post CRUD</h2>';
		$args = array(
			'public'   => true,
		);
		$post_types = get_post_types($args); ?>
		<table class="wp-list-table widefat fixed striped">
			<tr class="post_header">
				<th class="manage-column column-user">Users</th>
				<th class="manage-column column-user">Attachment</th>
				<?php
				foreach ($post_types as $key => $post_type_name)
				{
					if($post_type_name!="attachment"):
					?>
						<th class="manage-column column-author"><?php echo $post_type_name; ?></th>
				<?php
					endif;
				} ?>
				<th>Total</th>
			</tr>
			
				<?php
				$users = new WP_User_Query( array( 'number' => '100' ) );
				$total_count = 0;
				$user_count=0;
				foreach($users->results as $data => $user)
				{ 
					$users_id = $user->data->ID;
					$users_name = $user->data->display_name;
					echo '<tr><td style="text-align: center;"><a href="'.admin_url( "options-general.php?page=adv-user-crud&user_id=") .$users_id . '">'.$users_name.'</a></td>';
					
					global $wpdb;
					if(user_can( $users_id, 'upload_files' )):
						$attachment_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_author='".$users_id."'");
					else:
						$attachment_count = 0;
					endif;
					echo '<td style="text-align: center;">'.$attachment_count.'</td>';
					
					$user_capability = user_can( $users_id, 'publish_posts' );
				 	$total_count1 = 0;
					foreach ($post_types as $key => $post_type_name)
					{
						if($post_type_name!="attachment"):
							$post_count = count_user_posts( $users_id , $post_type_name );
							if($post_count !== '0'){
								echo '<td style="text-align: center;">'.$post_count.'</td>';
							}elseif($post_count==0){
								$post_count=0;
								echo '<td style="text-align: center;">0</td>';
							}
						endif;
						$total_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE  post_author ='".$users_id."' AND (post_status = 'publish' OR post_status = 'inherit') AND post_type = '".$post_type_name."'");
						$total_count1 = $total_count1 + $total_count;
						$count_other_posts = $total_count + $count_other_posts;
					}
						echo '<td style="text-align: center;">'.$total_count1.'</td>'; ?>
					</tr>
				<?php }
		?>
		</table>
	<?php
		echo '</div>';
	}
	function adv_user_crud_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		if(isset($_GET['user_id'])){ 
			$user_id = intval(($_GET['user_id']));
			$user_info = get_userdata($user_id);
			echo '<div class="adv_user_crud_page">';
				echo '<h1>List of posts, pages and media published by '.$user_info->user_login.'</h1>';
				require('class/Example_List_Table.php');
				$args = array(
					'public'   => true,
				);
				$post_types = get_post_types($args);
				$flag = 0;
				$exampleListTable = new Example_List_Table();
				foreach ($post_types as $key => $post_type_name) {
					if( $post_type_name !="attachment" && count_user_posts( $user_id , $post_type_name)){
						$flag = 1; 
						echo '<h2 class="post_type_title">'.$post_type_name.'</h2>'; 
				        $exampleListTable->prepare_items($post_type_name);
				        $exampleListTable->display();
			        }
		    	}
		    	$the_query = new WP_Query( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'author' => $user_id) );
				if ( $the_query->have_posts() ) {
					$flag = 1; 
					echo '<h2 class="post_type_title">Media</h2>'; 
			        $exampleListTable->prepare_items('attachment');
			        $exampleListTable->display();
				}
		    	if(!$flag){
		    		echo '<p>There is no post published by <a href="'.get_edit_user_link($user_id).'">'.$user_info->user_login.'</a>.</p>';
		    	} ?>
	        </div>
		<?php }
	}
	function adv_user_crud_menu() {
		$sub_menu=add_submenu_page(
			null,__( 'Advance user post CRUD Options', 'textdomain' )
			,''
			,'manage_options'
			,'adv-user-crud'
			,array($this,'adv_user_crud_options')
		);
		$post_count_sub_page=add_submenu_page('options-general.php','Advance user post CRUD','Advance user post CRUD','manage_options','adv-user-crud-post-count',array($this,'adv_user_crud_page'));
	}
	function adv_user_crud_frontend_profile_action_link($actions, $user_object) {
		$actions['view profile'] = "<a class='view_frontend_profile' href='" .  admin_url( "options-general.php?page=adv-user-crud&user_id=") .$user_object->ID . "'>" . __( 'View Profile', 'frontend_profile' ) . "</a>";
		return $actions;
	}
	function adv_user_crud_user_content($current_user, $userids){
		 echo '<a href="'.admin_url().'options-general.php?page=adv-user-crud&user_id='.$userids[0].'">Click to see all published posts</a>';
	}

}
$adv_user_crud_class = new adv_user_crud();
// add plugin option in dashboard
add_action( 'admin_menu', array($adv_user_crud_class,'adv_user_crud_menu' ));
add_action('login_enqueue_scripts', array($adv_user_crud_class,'adv_user_crud_scripts'));
add_action( 'admin_enqueue_scripts', array($adv_user_crud_class,'adv_user_crud_scripts' ));
//add link to user page
add_filter('user_row_actions', array($adv_user_crud_class,'adv_user_crud_frontend_profile_action_link'), 11, 2);
//add plugin page link when we click on user delete link
add_action('delete_user_form',array($adv_user_crud_class,'adv_user_crud_user_content'),10,2);
?>