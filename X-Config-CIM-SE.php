<?php
/*********************************************************************************************************************************
* Plugin Name: X-CONFIG CIM Starter Edition
* Plugin URI: http://www.xioni.ag
* Description: Product configuration as matrix, dynamic, bill of material or set/bundle
* Author: XIONI AG
* Version: 1.0
**********************************************************************************************************************************/

/**********************************************************************
* XWPP is used as a prefix in this plugin
* XWPP stands for XIONI WORDPRESS PLUGIN
* Every function we write should start with the xwpp prefix
***********************************************************************/


/***********************************
*************METHODS****************
************************************/


/*--------------------------------------------------------------------\
| @method:      xwpp_add_xconfig_to_settings
|
| @param:       -
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-05-15
|
| @description: adds 'XConfig CIM SE' to settings
\---------------------------------------------------------------------*/
function xwpp_add_xconfig_to_settings() { 

	add_options_page( 'XConfig', 'XConfig CIM SE', 'manage_options', 'xconfig', 'xwpp_options_page' );

}
add_action( 'admin_menu', 'xwpp_add_xconfig_to_settings' ); 
/*--------------------------------------------------------------------\
| @method:      xwpp_adding_domain_license_to_settings
|
| @param:       -
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-05-15
|
| @description: adds 'License' and 'Domain' fields in settings
\---------------------------------------------------------------------*/
function xwpp_adding_domain_license_to_settings() { 

	register_setting( 'pluginPage', 'xwpp_settings' );

	add_settings_section(
		'xwpp_pluginPage_section', 
		__( 'Options:', 'wordpress' ), 
		'xwpp_settings_section_callback',
		'pluginPage'
	);

	add_settings_field( 
		'xwpp_text_field_0', 
		__( 'License Key:', 'wordpress' ),
		'xwpp_text_field_0_render', 
		'pluginPage', 
		'xwpp_pluginPage_section' 
	);

	add_settings_field( 
		'xwpp_text_field_1', 
		__( 'Domain:', 'wordpress' ),
		'xwpp_text_field_1_render',
		'pluginPage', 
		'xwpp_pluginPage_section' 
	);



}
add_action( 'admin_init', 'xwpp_adding_domain_license_to_settings' );

// required function for xwpp_adding_domain_license_to_settings (rendering)
function xwpp_text_field_0_render(  ) { 

	$options = get_option( 'xwpp_settings' );
	?>
	<input type='text' style="width: 50%" name='xwpp_settings[xwpp_text_field_0]' value='<?php echo $options['xwpp_text_field_0']; ?>'>
	<?php

}
// required function for xwpp_adding_domain_license_to_settings (rendering)
function xwpp_text_field_1_render(  ) { 

	$options = get_option( 'xwpp_settings' );
	?>
	<input type='text' style="width: 50%" name='xwpp_settings[xwpp_text_field_1]' value='<?php echo $options['xwpp_text_field_1']; ?>'>
	<?php

}
// required function for xwpp_adding_domain_license_to_settings
function xwpp_settings_section_callback(  ) { 

	echo __( 'Enter your license and your domain here:', 'wordpress' );

}
// required function for xwpp_adding_domain_license_to_settings
function xwpp_options_page(  ) { 

	?>
	<form action='options.php' method='post'>

		<h2>XConfig CIM SE</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
    
	<?php

}

/*--------------------------------------------------------------------\
| @method:      xwpp_add_xbackend_button()
|
| @param:       -
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-16
|
| @description: creates 'open in x-backend'-button
\---------------------------------------------------------------------*/
function xwpp_add_xbackend_button() {

	global $post;

	$current_ID = get_the_ID();

	global $wpdb;
	$article_ID = $wpdb->get_var("SELECT meta_value FROM wp_postmeta WHERE post_id = $current_ID AND meta_key = '_sku'");
	$article_name = $wpdb->get_var("SELECT post_title FROM wp_posts WHERE ID = $current_ID");

	$options = get_option( 'xwpp_settings' );
	$license = $options['xwpp_text_field_0'];
	$domain = $options['xwpp_text_field_1'];

	echo "</form>";
	echo "<form method='post' target='_blank' action='https://" . $domain . "/xioniBackend/backend_index.php' style='display: inline;'>";
		echo "<input type='hidden' name='user_id' id='user_id' value='1'></input>";
		echo "<input type='hidden' name='user-backend' id='user-backend' value='1'></input>";
		echo "<input type='hidden' name='ordernumber' id='ordernumber' value='" . $article_ID . "'></input>";
		echo "<input type='hidden' name='xlicense' id='xlicense' value='" . $license . "'></input>";
		echo "<input type='hidden' name='xdomain' id='xdomain' value='" . $domain . "'></input>"; 
		echo "<input type='hidden' name='articlename' id='articlename' value='" . $article_name . "'></input>";
		echo "<input type='hidden' name='realdomain' id='realdomain' value=''></input>";
		echo "<button id='open-x-backend' type='submit'>Open in X-Backend</button>";
	echo "</form>"; 
}
add_action( 'media_buttons', 'xwpp_add_xbackend_button' );

/*--------------------------------------------------------------------\
| @method:      xwpp_add_xconfig_option_in_products( $product_type_options )
|
| @param:       $product_type_options 
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-06-12
|
| @description: adds 'xconfig' checkbox to products
\---------------------------------------------------------------------*/
function xwpp_add_xconfig_option_in_products( $product_type_options ) {
	$product_type_options['xconfig'] = array(
		'id'            => '_xconfig',
		'wrapper_class' => 'show_if_simple show_if_variable show_if_grouped show_if_external',
		'label'         => __( 'XConfig', 'woocommerce' ),
		'description'   => __( 'Is this a xconfig product?', 'woocommerce' ),
		'default'       => 'no'
	);

	return $product_type_options;
}
add_filter( 'product_type_options', 'xwpp_add_xconfig_option_in_products' );
/*--------------------------------------------------------------------\
| @method:      xwpp_save_xconfig_option( $post_id )
|
| @param:       $post_id
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-06-12
|
| @description: saves xconfig checkbox in products
\---------------------------------------------------------------------*/
function xwpp_save_xconfig_option( $post_id ) {

	$is_xconfig = isset( $_POST['_xconfig'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_xconfig', $is_xconfig );

}
add_action( 'woocommerce_process_product_meta_simple', 'xwpp_save_xconfig_option'  );
add_action( 'woocommerce_process_product_meta_variable', 'xwpp_save_xconfig_option'  );
////
/*--------------------------------------------------------------------\
| @method:      xwpp_expand_db_order_items()
|
| @param:       -
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-06-19
|
| @description: expands 'wp_woocommerce_order_items' table in DB
\---------------------------------------------------------------------*/
function xwpp_expand_db_order_items() {

	global $wpdb;

	$sql = "ALTER TABLE wp_woocommerce_order_items ADD COLUMN ( 
		xconfigdesc varchar(2048) NULL, 
		xconfigflag int(2) NULL,
		xconfigurl varchar(2048) NULL,
		xconfigprice varchar(2048) NULL,
		xconfigattr1 varchar(2048) NULL, 
		xconfigattr2 varchar(2048) NULL, 
		xconfigattr3 varchar(2048) NULL
	);";


	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$wpdb->query($sql);

}
register_activation_hook( __FILE__, 'xwpp_expand_db_order_items' );

/*--------------------------------------------------------------------\
| @method:     xwpp_get_product_data()
|
| @param:       -
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-16
|
| @description: get the product data -> load xconfig
\---------------------------------------------------------------------*/
function xwpp_get_product_data() {
	global $product;
	global $wpdb;

	//data for domain and license
	$options = get_option( 'xwpp_settings' );
	$license = $options['xwpp_text_field_0'];
	$domain = $options['xwpp_text_field_1'];

	$id = $product->id;
	$name = $product->name;
	$price = $product->sale_price;
	$sku = $product->sku;
	$xcon = $wpdb->get_var("SELECT meta_value FROM wp_postmeta WHERE post_id = $id AND meta_key = '_xconfig'"); 
	$add_to_cart_url = $product->add_to_cart_url();

	if( $xcon == "yes" && $sku != null ) {
		//article is xconfig article

		//load xconfig
		xwpp_load_xconfig($id, $name, $price, $sku, $domain, $add_to_cart_url);

	}

}
add_action( 'woocommerce_before_add_to_cart_form', 'xwpp_get_product_data' );

/*--------------------------------------------------------------------\
| @method:     xwpp_load_xconfig($id, $name, $price, $sku, $domain)
|
| @param:       $id, $name, $price, $sku, $domain
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-16
|
| @description: loads XConfig and Add-To-Cart-Button
\---------------------------------------------------------------------*/
function xwpp_load_xconfig($id, $name, $price, $sku, $domain, $add_to_cart_url) {
	WC()->session->set('sale_price', $price);
	?>

	<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>    
	<link type="text/css" media="screen, projection" rel="stylesheet" href="https://<?php echo $domain; ?>/xioniBackend/resources/shopfrontend/css/shop-frontend.css"/> 
	<link type="text/css" media="screen, projection" rel="stylesheet" href="https://<?php echo $domain; ?>/xioniBackend/resources/frontend/css/custom.css"/>

	<script src="https://code.jquery.com/jquery-latest.js"></script>
	<script src="https://<?php echo $domain; ?>/xioniBackend/resources/common/js/jquery-ui.js"></script>
	<script src="https://<?php echo $domain; ?>/xioniBackend/resources/shopfrontend/js/xActionFunctionsWC_SE.php"></script>
	
	<script>
		var articleId 	= '1';
		var ordernumber = '<?php echo $sku ?>';
		var userId		= '1';
		var price		= '<?php echo $price ?>';
		var currency	= "<?php echo xwpp_get_currency(); ?>";
		var groupIds 	= new Array();
		var groupValues = new Array();
		var num 		= 0;
		var string;
            

		$(document).ready(function() {
			visualtype = getVisualType(ordernumber, userId, articleId);

			isGroupComplete(visualtype, groupIds, groupValues);
			getGroupString(groupIds, groupValues);
			getConfiguration(articleId, ordernumber, userId, price, groupIds, groupValues, currency);
			getConfigurationFooter(ordernumber, function() {
				getDescriptionList(ordernumber, groupIds, groupValues); 
			});

			if(visualtype == 1){
				getVisual(articleId, ordernumber, userId, groupIds, groupValues);
			}

			//hide del price 
			$(".price").find("del").contents().hide();
			 
			//disable old add-to-cart button and quantity-input-field
			$(".single_add_to_cart_button").hide();
			$(".input-text").hide();

			//disable second "effect" add-to-cart button 
			$(".storefront-sticky-add-to-cart__content-button").hide();
		});

		function submitXData() {
			$("#x_configurator_body :input[name^='group']").each(function(e){
				if(this.type == "hidden" || this.type == "text") {
					string = this.name;
					string = string.substring(string.lastIndexOf("[")+1,string.lastIndexOf("]"));
					groupIds[num] = string;
					groupValues[num] = this.value;
					num++;
				}
			});

			$("#groupIds").val(groupIds);
			$("#groupValues").val(groupValues);

			getConfiguration(articleId, ordernumber, userId, price, groupIds, groupValues, currency);
			getPrice(articleId, ordernumber, userId, price, groupIds, groupValues, currency);
			getDescription(groupIds, groupValues);
			getDescriptionList(ordernumber, groupIds, groupValues);
			getGroupString(groupIds, groupValues);
			isGroupComplete(visualtype, groupIds, groupValues);

			groupIds = [];
			groupValues = [];
			num = 0;

			return false;
		}
            
		function submitRestart() {
			var groupIds 	= new Array();
			var groupValues = new Array();
			getConfiguration(articleId, ordernumber, userId, price, groupIds, groupValues);
			getDescription(ordernumber, groupIds, groupValues);
			getDescriptionList(ordernumber, groupIds, groupValues);
			getGroupString(groupIds, groupValues);
			isGroupComplete(groupIds, groupValues);
                
			$('.woocommerce-Price-amount').html(currency + price); 
		}
        
	</script>

	<div id = "x_configurator_body"></div>
	<div id = "x_configurator_footer"></div>

	<!-- ADD TO CART BUTTON INKLUSIVE QUANTITY FELD -->
	<div id = "x_config_add_to_cart_button">
		<form class="cart" name="sAddToBasket" id="sAddToBasket" method="post" action="<?php echo $add_to_cart_url; ?>"> 
			<input type="hidden" name="sActionIdentifier" value=""></input>
			<input type="hidden" name="name" value="<?php echo $name; ?>"></input>
			<input type="hidden" name="sAdd" value="<?php echo $sku; ?>"></input>
			<input type="hidden" name="userId" value="1"></input>
			<input type="hidden" name="articleId" value="<?php echo $id; ?>"></input>
			<input type="hidden" name="groupIds" id="groupIds" value=""></input>
			<input type="hidden" name="groupValues" id="groupValues" value=""></input>
			<input type="hidden" name="xconfig" id="xconfig" value="1"></input>
			<input type="hidden" name="xconfigdesc" id="xconfigdesc" value=""></input>
			<input type="hidden" name="xconfiggroup" id="xconfiggroup" value=""></input>
			<input type="hidden" name="xconfigimage" id="xconfigimage" value=""></input>
			<input type="hidden" name="xconfigobject" id="xconfigobject" value=""></input>
			<input type="number" name="quantity" min="1" max="99" step="1" value="1" ></input>

			<input type="submit" class="x-submit" value="Add to cart" disabled></input>
		</form>
	</div>

	<?php 
           
}
/*--------------------------------------------------------------------\
| @method:     xwpp_set_session( $name, $value )
|
| @param:       $name, $value
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-15
|
| @description: set session variable
\---------------------------------------------------------------------*/
function xwpp_set_session( $name, $value ) {
	WC()->session->set( $name, $value );
}
/*--------------------------------------------------------------------\
| @method:     xwpp_set_session( $name, $value )
|
| @param:       $name, $value
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-15
|
| @description: get session variable
\---------------------------------------------------------------------*/
function xwpp_get_session( $name ) {
	return WC()->session->get( $name );
}
/*--------------------------------------------------------------------\
| @method:     xwpp_add_cart_item_data( $cart_item_meta, $product_id)
|
| @param:       $cart_item_meta, $product_id
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-16
|
| @description: add to cart
\---------------------------------------------------------------------*/
 //Store the custom field
add_filter( 'woocommerce_add_cart_item_data', 'xwpp_add_cart_item_data', 10, 2 );
function xwpp_add_cart_item_data( $cart_item_meta, $product_id ) {
	global $woocommerce;

	$cart_item_meta[ 'sAdd' ] = $_POST[ 'sAdd' ];
	$cart_item_meta[ 'name' ] = $_POST[ 'name' ];
	$cart_item_meta[ 'x_config_desc' ] = $_POST[ 'xconfigdesc' ];
	$cart_item_meta[ 'groupIds' ] = $_POST[ 'groupIds' ];
	$cart_item_meta[ 'groupValues' ] = $_POST[ 'groupValues' ];
	$cart_item_meta[ 'xconfig' ] = $_POST[ 'xconfig' ];
	$cart_item_meta[ 'xconfiggroup' ] = $_POST[ 'xconfiggroup' ];
	$cart_item_meta[ 'articleId' ] = $_POST[ 'articleId' ];
 
	$cart_item_meta[ 'domain' ] = xwpp_get_domain();
	$cart_item_meta[ 'sale_price' ] = WC()->session->get('sale_price');

	$ids_exp = explode(",", $cart_item_meta[ 'groupIds' ] );
	$values_exp = explode(",", $cart_item_meta[ 'groupValues' ] );

	$cart_item_meta[ 'xprice' ] = xwpp_getXPrice( $cart_item_meta[ 'sAdd' ], '1', $cart_item_meta[ 'sale_price' ], $cart_item_meta[ 'articleId' ], $ids_exp, $values_exp, $cart_item_meta[ 'domain' ] );
	$cart_item_meta[ 'xattr1' ] = xwpp_getXConfigAttributes( $cart_item_meta[ 'sAdd' ], '1', $cart_item_meta[ 'articleId' ], $ids_exp, $values_exp, 1, $cart_item_meta[ 'domain' ] );
	$cart_item_meta[ 'xattr2' ] = xwpp_getXConfigAttributes( $cart_item_meta[ 'sAdd' ], '1', $cart_item_meta[ 'articleId' ], $ids_exp, $values_exp, 2, $cart_item_meta[ 'domain' ] );
	$cart_item_meta[ 'xattr3' ] = xwpp_getXConfigAttributes( $cart_item_meta[ 'sAdd' ], '1', $cart_item_meta[ 'articleId' ], $ids_exp, $values_exp, 3, $cart_item_meta[ 'domain' ] );

	return $cart_item_meta;
  
}

/*--------------------------------------------------------------------\
| @method:     xwpp_get_cart_items_from_session( $item, $values, $key )
|
| @param:       $item, $values, $key 
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-16
|
| @description: get cart items from session
\---------------------------------------------------------------------*/
//Get it from the session and add it to the cart variable
function xwpp_get_cart_items_from_session( $item, $values, $key ) {
	if ( array_key_exists( 'xconfigdesc', $values ) )
	$item[ 'x_config_desc' ] = $values[ 'xconfigdesc' ];

	if ( array_key_exists( 'groupIds', $values ) )
	$item[ 'groupIds' ] = $values[ 'groupIds' ];

	if ( array_key_exists( 'groupValues', $values ) )
	$item[ 'groupValues' ] = $values[ 'groupValues' ];

	if ( array_key_exists( 'xconfig', $values ) )
	$item[ 'xconfig' ] = $values[ 'xconfig' ];

	if ( array_key_exists( 'xconfiggroup', $values ) )
	$item[ 'xconfiggroup' ] = $values[ 'xconfiggroup' ];

	if ( array_key_exists( 'articleId', $values ) )
	$item[ 'articleId' ] = $values[ 'articleId' ];

	if ( array_key_exists( 'xattr1', $values ) )
	$item[ 'xattr1' ] = $values[ 'xattr1' ];

	if ( array_key_exists( 'xattr2', $values ) )
	$item[ 'xattr2' ] = $values[ 'xattr2' ];

	if ( array_key_exists( 'xattr3', $values ) )
	$item[ 'xattr3' ] = $values[ 'xattr3' ];

	return $item;
}
add_filter( 'woocommerce_get_cart_item_from_session', 'xwpp_get_cart_items_from_session', 1, 3 );

/*--------------------------------------------------------------------\
| @method:     xwpp_after_order_change_db_data( $order_id )
|
| @param:       $order_id
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-16
|
| @description: changes item data in DB
\---------------------------------------------------------------------*/

function xwpp_after_order_change_db_data( $order_id ) {

	$order = new WC_Order( $order_id );
	$items = $order->get_items();

	xwpp_set_session( 'order_id', $order_id );

	$complete_data = xwpp_get_session( 'cart_data' );

	foreach( $complete_data AS $single_data ) {
		xwpp_update_order_items_db( $single_data['name'], 'xconfigdesc', $single_data['xconfigdesc'], $order_id );
		xwpp_update_order_items_db( $single_data['name'], 'xconfigflag', '1', $order_id );
		xwpp_update_order_items_db( $single_data['name'], 'xconfigprice', $single_data['xprice'], $order_id );
		xwpp_update_order_items_db( $single_data['name'], 'xconfigurl', $single_data['xconfigurl'], $order_id );
		xwpp_update_order_items_db( $single_data['name'], 'xconfigattr1', $single_data['xattr1'], $order_id );
		xwpp_update_order_items_db( $single_data['name'], 'xconfigattr2', $single_data['xattr2'], $order_id );
		xwpp_update_order_items_db( $single_data['name'], 'xconfigattr3', $single_data['xattr3'], $order_id );
	}            

}
add_action('woocommerce_thankyou', 'xwpp_after_order_change_db_data', 10, 1);
/*--------------------------------------------------------------------\
| @method:     xwpp_replace_addtocard_on_front( $button, $product  )
|
| @param:       $button, $product 
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-15
|
| @description: switches add-to-cart buttons to 'view product' buttons on front page
\---------------------------------------------------------------------*/
// Shop and archives pages: we replace the button add to cart by a link to the product

function xwpp_replace_addtocard_on_front( $button, $product  ) {
	$button_text = __("View product", "woocommerce");
	return '<a class="button" href="' . $product->get_permalink() . '">' . $button_text . '</a>';
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'xwpp_replace_addtocard_on_front', 10, 2 );

/*--------------------------------------------------------------------\
| @method:     xwpp_add_xconfigdesc_to_items_in_cart( $item_name, $cart_item, $cart_item_key )
|
| @param:       $item_name, $cart_item, $cart_item_key
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-15
|
| @description: -
\---------------------------------------------------------------------*/
function xwpp_add_xconfigdesc_to_items_in_cart( $item_name, $cart_item, $cart_item_key ) {
	//Auslesen der Session daten
	$cart = WC()->cart->get_cart();

	echo "$item_name";
	echo "<br />";

	if( $cart_item['x_config_desc'] == " " || $cart_item['x_config_desc'] == null || $cart_item['x_config_desc'] == "" ) {
		echo "-";
	} else {
		echo $cart_item[ 'x_config_desc' ];
	}

}
add_filter( 'woocommerce_cart_item_name', 'xwpp_add_xconfigdesc_to_items_in_cart', 10, 3 );

/*--------------------------------------------------------------------\
| @method:     xwpp_get_all_item_data_and_set_price( $cart_object )
|
| @param:       $cart_object 
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-15
|
| @description: -
\---------------------------------------------------------------------*/
function xwpp_get_all_item_data_and_set_price( $cart_object ) {
	//needed for woocommerce 3+
	if ( is_admin() && ! defined( 'DOING_AJAX' ) )
		return;

	$counter = 0;
	foreach ( $cart_object->get_cart() as $cart_item ) {

		// get the product id (or the variation id)
		$id = $cart_item['data']->get_id();

		if( $cart_item['xconfig'] == 1 ) {

			$data_array[$counter] = array (
				'name'      => $cart_item['name'],
				'xconfig'   => '1',
				'xconfigdesc' => $cart_item['x_config_desc'],
				'xconfigurl'    => $cart_item['xconfiggroup'],
				'xprice'        => $cart_item['xprice'],
				'xattr1'        => $cart_item['xattr1'],
				'xattr2'        => $cart_item['xattr2'],
				'xattr3'        => $cart_item['xattr3'],
			);
 
			xwpp_set_session( 'cart_data', $data_array );
			
			//$new_price = $cart_item['xconfigprice'];
			$new_price = $cart_item['xprice'];

			// Updated cart item price
			$cart_item['data']->set_price( $new_price ); 
            
		}
        
		$counter++;
    }
}
add_filter( 'woocommerce_before_calculate_totals', 'xwpp_get_all_item_data_and_set_price', 10, 1 );

/*--------------------------------------------------------------------\
| @method:      xwpp_update_order_items_db( $attr, $value, $id )
|
| @param:       $attr, $value, $id 
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-07-18
|
| @description: update item data in DB
\---------------------------------------------------------------------*/
function xwpp_update_order_items_db( $name, $attr, $value, $id ) {

	global $wpdb;

	$sql = "UPDATE wp_woocommerce_order_items 
		SET $attr = '$value'
		WHERE order_id = $id
		AND order_item_name = '$name'";


	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$wpdb->query($sql);


}

/*--------------------------------------------------------------------\
| @method:      xwpp_get_order_items_from_db( $name, $id )
|
| @param:       $name, $id 
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-07-18
|
| @description: -
\---------------------------------------------------------------------*/
function xwpp_get_order_items_from_db( $name, $id ) {
    
	global $wpdb;

	$current = $wpdb->get_var("SELECT xconfigdesc FROM wp_woocommerce_order_items WHERE order_id = $id AND order_item_name = '$name'");
	return $current;

}

/*--------------------------------------------------------------------\
| @method:      xwpp_add_item_data_to_email_item_table( $item_id, $item, $order, $plain_text )
|
| @param:       $item_id, $item, $order, $plain_text
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-07-18
|
| @description: -
\---------------------------------------------------------------------*/
function xwpp_add_item_data_to_email_item_table( $item_id, $item, $order, $plain_text ){
	global $wpdb;

	$order_id = xwpp_get_session( 'order_id' );

	$xconfigdesc_from_db = xwpp_get_order_items_from_db( $item['name'], $order_id  );

	echo "<br />";
	echo $xconfigdesc_from_db;

}
add_action( 'woocommerce_order_item_meta_end', 'xwpp_add_item_data_to_email_item_table', 10, 4 );

/*--------------------------------------------------------------------\
| @method:      xwpp_add_item_data_to_orders_in_backend( $item_id, $item, $product )
|
| @param:        $item_id, $item, $product 
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-07-18
|
| @description: -
\---------------------------------------------------------------------*/
function xwpp_add_item_data_to_orders_in_backend( $item_id, $item, $product ) {
	global $wpdb;
	// Only for "line item" order items
	if( ! $item->is_type('line_item') ) return;

	$data = $wpdb->get_var("SELECT xconfigdesc FROM wp_woocommerce_order_items WHERE order_item_id = '$item_id'");
	echo $data;
}
add_action( 'woocommerce_after_order_itemmeta', 'xwpp_add_item_data_to_orders_in_backend', 20, 3 );

/*--------------------------------------------------------------------\
| @method:      xwpp_get_domain()
|
| @param:       -
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-07-18
|
| @description: -
\---------------------------------------------------------------------*/
function xwpp_get_domain() {
	global $wpdb;

	//data for domain and license
	$options = get_option( 'xwpp_settings' );
	$license = $options['xwpp_text_field_0'];
	$domain = $options['xwpp_text_field_1']; 

	return $domain;
}

/*--------------------------------------------------------------------\
| @method:      xwpp_get_xconfigdesc_in_email_item_table()
|
| @param:       -
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-16
|
| @description: -
\---------------------------------------------------------------------*/

function xwpp_get_xconfigdesc_in_email_item_table( $item_id, $item, $order, $plain_text ){
	global $wpdb;
	
	$product = $order->get_product_from_item($item);
	$id = $product->id;
	$data = $wpdb->get_var("SELECT xconfigdesc FROM wp_woocommerce_order_items WHERE order_item_id = $id");
	echo '<br />' . $data;
}
add_action( 'woocommerce_order_item_meta_start', 'xwpp_get_xconfigdesc_in_email_item_table', 10, 4 );

/*--------------------------------------------------------------------\
| @method:      xwpp_get_currency()
|
| @param:       -
|
| @return:      string
|
| @author:      Marcus Meixner
|
| @changedate:  2018-08-16
|
| @description: get the currency symbol from woocommerce backend
\---------------------------------------------------------------------*/
function xwpp_get_currency() {
	global $woocommerce;
	return get_woocommerce_currency_symbol();
}

/*--------------------------------------------------------------------\
| @method:      xwpp_getXPrice($ordernumber, $user_id, $price, $article_id, $group_ids, $group_values, $domain)
|
| @param:       $ordernumber, $user_id, $price, $article_id, $group_ids, $group_values, $domain
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-07-18
|
| @description: -
\---------------------------------------------------------------------*/
function xwpp_getXPrice($ordernumber, $user_id, $price, $article_id, $group_ids, $group_values, $domain) {
	$url        = 'https://'.$domain.'/xioniBackend/engine/frontend/Actions/xPrice.php';
	$data       = array('ordernumber' => $ordernumber, 'userID' => $user_id, 'price' => $price, 'articleID' => $article_id, 'groupIds' => $group_ids, 'groupValues' => $group_values);
	$options    = array(
		'http'      => array(
			'header'    => "Content-type: application/x-www-form-urlencoded",
			'method'    => 'POST',
			'content'   => http_build_query($data)
		)
	);
	
	$context                = stream_context_create($options);
	$x_price                = file_get_contents($url, false, $context);

	return $x_price;
}

/*--------------------------------------------------------------------\
| @method:      xwpp_getXConfigAttributes($ordernumber, $user_id, $article_id, $group_ids, $group_values, $x_attr, $domain)
|
| @param:       $ordernumber, $user_id, $article_id, $group_ids, $group_values, $x_attr, $domain
|
| @return:      -
|
| @author:      Marcus Meixner
|
| @changedate:  2018-07-18
|
| @description: -
\---------------------------------------------------------------------*/
function xwpp_getXConfigAttributes($ordernumber, $user_id, $article_id, $group_ids, $group_values, $x_attr, $domain) {
	$url        = 'https://'.$domain.'/xioniBackend/engine/frontend/Actions/xConfigAttributes.php';
	$data       = array('ordernumber' => $ordernumber, 'userID' => $user_id, 'articleID' => $article_id, 'groupIds' => $group_ids, 
					 'groupValues' => $group_values, 'xattr' => $x_attr);
	$options    = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context        = stream_context_create($options);
	$x_attributes   = file_get_contents($url, false, $context);
	
	return $x_attributes;
}
    
?>