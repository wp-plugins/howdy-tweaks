<?php
/*
Plugin Name: Howdy Tweaks
Plugin URI: http://trepmal.com/plugins/howdy-tweaks/
Description: Tweaks to the Howdy greeting and Favorites menu
Author: Kailey Lampert
Version: 1.1
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

$howdy_tweaks = new howdy_tweaks();

class howdy_tweaks {

	function howdy_tweaks() {
		add_action( 'admin_init', array( &$this, 'ht_register' ) );
		add_action( 'admin_menu', array( &$this, 'menu' ) );
		add_filter( 'admin_user_info_links', array( &$this, 'the_info_tweaks' ) );
		add_filter( 'favorite_actions', array( &$this, 'the_favs_tweaks'), 100 );
	}

	function ht_register() {
		register_setting( 'howdy_tweaks_options', 'ht_options', array ( &$this, 'ht_validate' ) );
		register_setting( 'howdy_tweaks_options', 'ht_greeting', 'esc_attr' );
	}

	function ht_validate( $input ) {
		$original = get_option('ht_options', array() );

		if ( isset( $input['newrow'] ) && isset( $input['0'] ) ) {
				$original['0']['label'] = esc_attr( $input['0']['label'] );
				$original['0']['favs'] = isset( $input['0']['favs'] ) ? ( $input['0']['favs'] == 1 ? 1: 0 ) : 0;
				$original['0']['info'] = isset( $input['0']['info'] ) ? ( $input['0']['info'] == 1 ? 1: 0 ) : 0;
				$original['0']['url'] = esc_url( $input['0']['url'] );
				if ( empty( $original['0']['label'] ) )
					unset( $original['0'] );

			foreach( $input as $k => $opts ) {
				if ( $k == 'newrow' ) continue;
				$original[ $k ]['label'] = esc_attr( $opts['label'] );
				$original[ $k ]['favs'] = isset( $opts['favs'] ) ? ( $opts['favs'] == 1 ? 1: 0 ) : 0;
				$original[ $k ]['info'] = isset( $opts['info'] ) ? ( $opts['info'] == 1 ? 1: 0 ) : 0;
				$original[ $k ]['url'] = $opts['url'];
				if ( empty( $original[ $k ]['label'] ) )
					unset( $original[ $k ] );
			}
		}

		/* some wierd looping thing is happening, so this check is required */
		if ( !isset( $input['newrow'] ) ) {
			if ( !isset( $input['0'] ) ) {
				return $original;
			}
			else {
				$new = $input['0'];
			}
		}
		else {
			$new = $input['newrow'];
		}

		$new_row['label'] = !empty( $new['label'] ) ? esc_attr( $new['label'] ) : false;
		$new_row['favs'] = isset( $new['favs'] ) ? 1 : 0;
		$new_row['info'] = isset( $new['info'] ) ? 1 : 0;
		$new_row['url'] = isset( $new['url'] ) ? esc_url( $new['url'] ) : '';


		if ( $new_row['label'] )
			$input = array_merge( array( $new_row ), $original );
		else
			$input = array_merge( array(), $original );

		return $input;

	}

	function menu() {
		$page = add_options_page( __( 'Howdy Tweaks', 'howdy_tweaks' ),  __( 'Howdy Tweaks', 'howdy_tweaks' ), 'administrator', __FILE__, array( &$this, 'page' ) );
	}

	function page() {

		echo '<div class="wrap">';
		echo '<h2>' . __('Howdy Tweaks', 'howdy_tweaks' ) . '</h2>';

		echo '<form method="post" action="options.php">';
		settings_fields( 'howdy_tweaks_options' );

		$values = get_option( 'ht_options', array() );
		$greeting = get_option( 'ht_greeting', 'Howdy,');

		echo '<p><label for="greeting">' . __( 'Greeting', 'howdy_tweaks' ) . ': <input type="text" name="ht_greeting" id="greeting" value="' . $greeting. '" size="50" /></label></p>';
		?>
		<table class="widefat">
		<thead>
			<tr>
				<th><?php _e( 'Label', 'howdy_tweaks' ); ?></th>
				<th>
					<?php
					$label = __( 'Favorites', 'howdy_tweaks' );
					if ( ht_is32() )
						echo '<del>'. $label .'</del>';
					else
						echo $label;
					?>
				</th>
				<th><?php _e( 'Info Links', 'howdy_tweaks' ); ?></th>
				<th><?php _e( 'Link', 'howdy_tweaks' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e( 'Label', 'howdy_tweaks' ); ?></th>
				<th>
					<?php
					$label = __( 'Favorites', 'howdy_tweaks' );
					if ( ht_is32() )
						echo '<del>'. $label .'</del>';
					else
						echo $label;
					?>
				</th>
				<th><?php _e( 'Info Links', 'howdy_tweaks' ); ?></th>
				<th><?php _e( 'Link', 'howdy_tweaks' ); ?></th>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<td><input type="text" name="ht_options[newrow][label]" value="" size="40" /></td>
				<td><input type="checkbox" name="ht_options[newrow][favs]" value="" <?php if ( ht_is32() ) { echo 'disabled'; } ?> /></td>
				<td><input type="checkbox" name="ht_options[newrow][info]" value="" /></td>
				<td><input type="text" name="ht_options[newrow][url]" value="" size="40" /></td>
			</tr>
			<?php foreach($values as $id => $opt) { ?>
			<tr>
				<td><input type="text" name="ht_options[<?php echo $id; ?>][label]" value="<?php echo $opt['label']; ?>" size="40" /></td>
				<td><input type="checkbox" name="ht_options[<?php echo $id; ?>][favs]" value="1" <?php checked($opt['favs']); ?> <?php if ( ht_is32() ) { echo 'disabled'; } ?> /></td>
				<td><input type="checkbox" name="ht_options[<?php echo $id; ?>][info]" value="1" <?php checked($opt['info']); ?> /></td>
				<td><input type="text" name="ht_options[<?php echo $id; ?>][url]" value="<?php echo $opt['url']; ?>" size="40" /></td>
			</tr>
			<?php } ?>
		</tbody>
		</table>
		<?php

		echo '<p>'. __( 'Multisite: Use %ID% as a site ID variable', 'howdy_tweaks' ) . '</p>';
		echo '<p>'. __( 'Erase a label and save to remove an entry.', 'howdy_tweaks' ) . '</p>';
		if ( ht_is32() ) {
			echo '<p>'. __( '"Favorites" menu no longer exists in 3.2.', 'howdy_tweaks' ) . '</p>';
		}
		echo '<p><input type="submit" class="button-primary" value="' . __( 'Save', 'howdy_tweaks' ) . '" /></p>';
		echo '</form>';

		echo '</div>';

	}// end function

	function the_info_tweaks( $links ) {

		global $blog_id;
		$opts = get_option( 'ht_options', array() );

		foreach( $opts as $k => $vals ) {
			if ( $vals['info'] != 0 ) {
				$lnk = ' | <a href="' . $vals['url'] . '" title="' . $vals['url'] . '">' . $vals['label'] . '</a>';
				$lnk = str_replace( '%ID%', $blog_id, $lnk );
				if ( empty( $vals['url'] ) )
					$lnk = strip_tags( $lnk );
				$links[] = $lnk;
			}
		}
		/* change "Howdy" */
		if ( $greeting = get_option( 'ht_greeting' ) )
			$links['5'] = str_replace( 'Howdy,', $greeting, $links['5'] );

		return $links;
	}

	function the_favs_tweaks( $actions ) {
		$opts = get_option( 'ht_options', array() );

		foreach( $opts as $k => $vals ) {
			if ( $vals['favs'] != 0 ) {
				$actions[ $vals['url'] ] = array( $vals['label'], 'publish_pages' );
			}
		}

		return $actions;
	}

}//end class howdy_tweaks

/*
 * Check if this is v 3.2
**/
function ht_is32() {
	if ( function_exists( 'is_multi_author' ) )
		return true;
	else
		return false;
}
