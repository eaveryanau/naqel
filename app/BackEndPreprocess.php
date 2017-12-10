<?php
/**
 * Created by PhpStorm.
 * User: eaveryanau
 * Date: 9/27/17
 * Time: 12:44 PM
 */

// Add plugin admin page.
$_page = 'naqel-settings.php';

/*
 * Function that adds a page to the Settings menu item
 */
function naqel_options() {
	global $_page;
	add_menu_page( 'Naqel express', 'Naqel express', 'manage_options', $_page, 'naqel_option_page' );
    add_submenu_page( $_page, 'Waybills list', 'Waybills list', 'manage_options', 'naqel_waybills_page', 'naqel_waybills_list_page' );
	add_submenu_page( null, 'Sticker', 'Sticker', 'manage_options', 'naqel_waybills_sticker_print', 'naqel_waybills_sticker_print' );
	add_submenu_page( null, 'Hold', 'Hold', 'manage_options', 'naqel_waybill_hold', 'naqel_waybill_hold' );
	add_submenu_page( null, 'Rto', 'Rto', 'manage_options', 'naqel_waybill_rto', 'naqel_waybill_rto' );
}

add_action( 'admin_menu', 'naqel_options' );

/**
 * Callbacks
 */
function naqel_option_page() {
	global $_page;
	?>
    <div class="wrap">
    <h2>Naqel express settings</h2>
    <form method="post" enctype="multipart/form-data" action="options.php">
		<?php
		settings_fields( 'naqel_options' );
		do_settings_sections( $_page );
		?>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>"/>
        </p>
    </form>
    </div><?php
}

function naqel_waybills_list_page(){

    Naqel::printPageWaybillsList();

    die();
}

function naqel_waybills_sticker_print(){


    if(isset($_GET['order_id'])){
	    Naqel::printSticker($_GET['order_id']);
    }
    die();

}

function naqel_waybill_hold(){
    if(isset($_GET['order_id'])) {
		Naqel::holdWaybill( $_GET['order_id'] );
	}
	die();
}

function naqel_waybill_rto(){
    if(isset($_GET['order_id'])){
        Naqel::rtoWaybill($_GET['order_id']);
    }
    die();
}

/*
 * Register settings
 */
function option_settings() {
	global $_page;
	// Add validate
	register_setting( 'naqel_options', 'naqel_options', 'validate_settings' ); // true_options

	// Add setion
	add_settings_section( 'naqel_section_1', 'Secure section', '', $_page );

	// Add field for client ID
	$naqel_client_id_field_params = array(
		'type'      => 'text',
		'id'        => 'client_id',
		'desc'      => 'Naqel client ID.',
		'label_for' => 'client_id'
	);
	add_settings_field( 'client_id_field', 'Client ID', 'option_display_settings', $_page, 'naqel_section_1', $naqel_client_id_field_params );

    // Add field for password of client naqel
    $naqel_password_field_params = array(
        'type'      => 'password',
        'id'        => 'passwd',
        'desc'      => 'Password of client Naqel.',
        'label_for' => 'passwd'
    );
    add_settings_field( 'password_field', 'Password', 'option_display_settings', $_page, 'naqel_section_1', $naqel_password_field_params );

}

add_action( 'admin_init', 'option_settings' );

/*
 * Output part
 */
function option_display_settings( $args ) {
	extract( $args );

	$option_name = 'naqel_options';

	$o = get_option( $option_name );

	switch ( $type ) {
		case 'text':
			$o[ $id ] = esc_attr( stripslashes( $o[ $id ] ) );
			echo "<input class='regular-text' type='text' id='$id' name='" . $option_name . "[$id]' value='$o[$id]' />";
			echo ( $desc != '' ) ? "<br /><span class='description'>$desc</span>" : "";
			break;
		case 'password':
			$o[ $id ] = esc_attr( stripslashes( $o[ $id ] ) );
			echo "<input class='regular-text' type='password' id='$id' name='" . $option_name . "[$id]' value='$o[$id]' />";
			echo ( $desc != '' ) ? "<br /><span class='description'>$desc</span>" : "";
			break;
		case 'textarea':
			$o[ $id ] = esc_attr( stripslashes( $o[ $id ] ) );
			echo "<textarea class='code large-text' cols='50' rows='10' type='text' id='$id' name='" . $option_name . "[$id]'>$o[$id]</textarea>";
			echo ( $desc != '' ) ? "<br /><span class='description'>$desc</span>" : "";
			break;
		case 'checkbox':
			$checked = ( $o[ $id ] == 'on' ) ? " checked='checked'" : '';
			echo "<label><input type='checkbox' id='$id' name='" . $option_name . "[$id]' $checked /> ";
			echo ( $desc != '' ) ? $desc : "";
			echo "</label>";
			break;
		case 'select':
			echo "<select id='$id' name='" . $option_name . "[$id]'>";
			foreach ( $vals as $v => $l ) {
				$selected = ( $o[ $id ] == $v ) ? "selected='selected'" : '';
				echo "<option value='$v' $selected>$l</option>";
			}
			echo ( $desc != '' ) ? $desc : "";
			echo "</select>";
			break;
		case 'radio':
			echo "<fieldset>";
			foreach ( $vals as $v => $l ) {
				$checked = ( $o[ $id ] == $v ) ? "checked='checked'" : '';
				echo "<label><input type='radio' name='" . $option_name . "[$id]' value='$v' $checked />$l</label><br />";
			}
			echo "</fieldset>";
			break;
	}
}

/*
 * Validate function
 */
function validate_settings( $input ) {
	foreach ( $input as $k => $v ) {
		$valid_input[ $k ] = trim( $v );
	}

	return $valid_input;
}

