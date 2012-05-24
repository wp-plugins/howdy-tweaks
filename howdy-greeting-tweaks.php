<?php
/*
Plugin Name: Howdy Tweaks
Plugin URI: http://trepmal.com/plugins/howdy-tweaks/
Description: Tweaks to the Howdy greeting and Favorites menu
Author: Kailey Lampert
Version: 2.1
Author URI: http://kaileylampert.com/

Copyright (C) 2011  Kailey Lampert

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

load_plugin_textdomain( 'howdy-tweaks', false, dirname( plugin_basename( __FILE__ ) ) .  '/lang' );

new howdy_tweaks();

class howdy_tweaks {

	function __construct() {

		add_action( 'admin_init', array( &$this, 'register' ) );
		add_action( 'admin_menu', array( &$this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'scripts' ) );
		add_action( 'admin_bar_menu', array( &$this, 'the_info_tweaks' ) );
		add_action( 'admin_bar_menu', array( &$this, 'the_favs_tweaks'), 100 );
		add_action( 'admin_bar_menu', array( &$this, 'change_howdy' ) );
	}

	function register() {
		register_setting( 'howdy-tweaks_options', 'ht_options', array ( &$this, 'sanitize' ) );
		register_setting( 'howdy-tweaks_options', 'ht_greeting', 'esc_attr' );
	}

	function sanitize( $input ) {

		foreach( $input as $k => $opts ) {
			$input[ $k ]['label'] = esc_attr( $opts['label'] );
			$input[ $k ]['favs'] = isset( $opts['favs'] ) ? 1 : 0;
			$input[ $k ]['info'] = isset( $opts['info'] ) ? 1 : 0;
			$input[ $k ]['url'] = empty( $opts['url'] ) ? '' : esc_url( $opts['url'] );
			if ( empty( $input[ $k ]['label'] ) )
				unset( $input[ $k ] );
		}

		return $input;
	}

	function menu() {
		global $howdy_tweaks_page;
		$howdy_tweaks_page = add_options_page( __( 'Howdy Tweaks', 'howdy-tweaks' ),  __( 'Howdy Tweaks', 'howdy-tweaks' ), 'administrator', 'howdy-tweaks', array( &$this, 'page' ) );
		add_action("load-$howdy_tweaks_page", array( &$this, 'help_tab' ) );
	}

	function scripts( $hook ) {
		if ( $hook != 'settings_page_howdy-tweaks' ) return;
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'howdy-tweaks', plugins_url( 'howdy.js', __FILE__ ), array( 'jquery-ui-sortable' ) );
	}

	function page() {

		echo '<div class="wrap">';
		echo '<h2>' . __('Howdy Tweaks', 'howdy-tweaks' ) . '</h2>';

		echo '<form method="post" action="options.php">';
		settings_fields( 'howdy-tweaks_options' );

		$values = get_option( 'ht_options', array() );
		$greeting = get_option( 'ht_greeting', 'Howdy,');

		echo '<p><label for="greeting">' . __( 'Greeting', 'howdy-tweaks' ) . ': <input type="text" name="ht_greeting" id="greeting" value="' . $greeting. '" size="50" /></label></p>';
		echo '<p>'. sprintf( __( 'Available placeholders: %1$s', 'howdy-tweaks' ) , '<code>%name%</code>' ) .'<br />'.
		__( 'If not specified, %name% will be added to the end. ', 'howdy-tweaks' ) .'</p>';

		$garbage = uniqid();
		echo "<input type='hidden' id='ht_garbage' value='{$garbage}' />";
		?>
		<table class="widefat" id="ht_table">
		<thead>
			<tr>
				<th><?php _e( 'Label', 'howdy-tweaks' ); ?></th>
				<th><?php _e( 'Favorites', 'howdy-tweaks' ); ?></th>
				<th><?php _e( 'Info Links', 'howdy-tweaks' ); ?></th>
				<th><?php _e( 'Link', 'howdy-tweaks' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e( 'Label', 'howdy-tweaks' ); ?></th>
				<th><?php _e( 'Favorites', 'howdy-tweaks' ); ?></th>
				<th><?php _e( 'Info Links', 'howdy-tweaks' ); ?></th>
				<th><?php _e( 'Link', 'howdy-tweaks' ); ?></th>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach($values as $id => $opt) { ?>
			<tr>
				<td><input type="text" name="ht_options[<?php echo $id; ?>][label]" value="<?php echo $opt['label']; ?>" size="40" /></td>
				<td><input type="checkbox" name="ht_options[<?php echo $id; ?>][favs]" value="1" <?php checked($opt['favs']); ?> /></td>
				<td><input type="checkbox" name="ht_options[<?php echo $id; ?>][info]" value="1" <?php checked($opt['info']); ?> /></td>
				<td><input type="text" name="ht_options[<?php echo $id; ?>][url]" value="<?php echo $opt['url']; ?>" size="40" /></td>
			</tr>
			<?php } ?>
			<tr id="ht_new_row">
				<td><input type="text" name="ht_options[<?php echo $garbage; ?>][label]" value="" size="40" /></td>
				<td><input type="checkbox" name="ht_options[<?php echo $garbage; ?>][favs]" value="" /></td>
				<td><input type="checkbox" name="ht_options[<?php echo $garbage; ?>][info]" value="" /></td>
				<td><input type="text" name="ht_options[<?php echo $garbage; ?>][url]" value="" size="40" /></td>
			</tr>
		</tbody>
		</table>
		<?php

		echo '<p><input type="submit" class="button-primary" value="' . __( 'Save', 'howdy-tweaks' ) . '" /> <a href="#" class="ht_add_new">' . __( 'Add another row', 'howdy-tweaks' ) . '</a></p>';
		echo '</form>';

		echo '</div>';

	}// end page()
	
	function help_tab() {
		global $howdy_tweaks_page;
	    $screen = get_current_screen();
	    if ( $screen->id != $howdy_tweaks_page )
    	    return;

	    $screen->add_help_tab( array(
        	'id'	=> 'howdy-tweaks',
    	    'title'	=> __( 'Howdy Tweaks', 'howdy-tweaks' ),
   	 		'content' => $this->help(),
	    ) );
	}

	function help() {
		$help = '';
		$help .= '<p>' . __( 'Items checked "Favorites" will appear in a new Favorites menu on the right side of the Toolbar.', 'howdy-tweaks' ) . ' ';
		$help .= __( 'If there are no "favorites," the menu will not be created.', 'howdy-tweaks' ) . '</p>';
		$help .= '<p>' . __( "You can drag-n-drop each row so the the items will appear in the order you'd prefer", 'howdy-tweaks' ) . '</p>';
		$help .= '<p>' . __( 'Click "Add another row" to quickly add more items.', 'howdy-tweaks' ) . '</p>';
		$help .= '<p>' . __( 'To remove an item, simply delete its label.', 'howdy-tweaks' ) . '</p>';
		$help .= '<p>' . sprintf( __( "If you are using WordPress Multisite, you can use %s as a placeholder for the current site's ID.", 'howdy-tweaks' ), '<code>%ID%</code>' ) . '</p>';
		return $help;
	}
	function the_info_tweaks( $wp_admin_bar ) {

		global $blog_id;
		$opts = get_option( 'ht_options', array() );

		foreach( $opts as $k => $vals ) {
			if ( $vals['info'] ) {

				$label = str_replace( '%ID%', $blog_id, $vals['label'] );
				$node = array (
					'parent' => 'my-account',
					'id' => 'ht-info-'.sanitize_title( $label ),
					'title' => $label,
					'href' => $vals['url']
				);

				$wp_admin_bar->add_menu( $node );

			}
		}

	}

	function the_favs_tweaks( $wp_admin_bar ) {
		global $blog_id;
		$opts = get_option( 'ht_options', array() );

		$wp_admin_bar->add_menu( array(
			'id' => 'favorites',
			'parent'    => 'top-secondary',
			'title' => 'Favorites',
			'meta' => array (
				'class' => 'opposite'
			)
		) );
		$i = 0;
		foreach( $opts as $k => $vals ) {
			if ( $vals['favs'] != 0 ) {
				$label = str_replace( '%ID%', $blog_id, $vals['label'] );
				$node = array (
					'parent' => 'favorites',
					'id' => 'ht-fave-'.sanitize_title( $label ),
					'title' => $label,
					'href' => $vals['url']
				);

				$wp_admin_bar->add_menu( $node );
				++$i;
			}
		}
		//if there are no 'favorites' items, remove the menu
		if ($i < 1) $wp_admin_bar->remove_menu( 'favorites');

	}

	function change_howdy( $wp_admin_bar ) {

		$greeting = get_option( 'ht_greeting', 'Howdy,' );

		//get the node that contains "howdy"
		$my_account = $wp_admin_bar->get_node('my-account');
		//change the "howdy"
		
		$user_id = get_current_user_id();
		$current_user = wp_get_current_user();
		$avatar = get_avatar( $user_id, 16 );

		if (strpos( $greeting, '%name%' ) !== false ) {
			$howdy = str_replace( '%name%', $current_user->display_name, $greeting );
		} else {
			$howdy = $greeting . ' ' . $current_user->display_name;
		}

		$my_account->title = $howdy . $avatar;
		//remove the original node
		$wp_admin_bar->remove_node('my-account');
		//add back our modified version
		$wp_admin_bar->add_node($my_account);

	}

}//end class howdy-tweaks
