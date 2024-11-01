<?php

/*

Plugin Name: Show product reviews and ratings and total average shoprating
Plugin URI: https://pdminternetdiensten.nl

Description: Plugin to show average rating stars of all products. Furthermore you can show all product reviews / stars 

Version: 1.0

Author: PDMI

Author URI: https://pdminternetdiensten.nl

License: GPLv2 or later

Text Domain: pdmi

*/

// Show reviews of all the products
add_shortcode( 'pdmi_show_all_product_reviews', function () {
	ob_start();
	pdmi_show_product_all_ratings();
	return ob_get_clean(); 
} );

// Show average shop ratingstars
add_shortcode( 'pdmi_show_shop_average_rating_stars', function () {
	ob_start();
	echo wp_kses_post(esc_attr(pdmi_show_average_rating_stars_all_products()));
	return ob_get_clean();
} );

// Show average ratings score (1 dec)
add_shortcode( 'pdmi_get_average_rating_score', function () {
	ob_start();
	echo esc_attr(round(pdmi_get_average_rating_all_products(),1));
	return ob_get_clean();
} );

// Show total number of product reviews
add_shortcode( 'pdmi_get_total_number_reviews', function () {
	ob_start();
	echo esc_attr(pdmi_get_total_reviews_count());
	return ob_get_clean();
} );



function pdmi_my_enqueued_assets() {
        wp_enqueue_style('my-css-file', plugin_dir_url(__FILE__) . '/css/show-review-rating.css', '', time());
}
add_action('wp_enqueue_scripts', 'pdmi_my_enqueued_assets');

function pdmi_get_total_reviews_count(){
    return get_comments(array(
        'status'   => 'approve',
        'post_status' => 'publish',
        'post_type'   => 'product',
        'count' => true
    ));
}

function pdmi_show_all_product_reviewss($reviewID) {
	$args = array ('post_id' => $reviewID); 
    $comments = get_comments( $args );
    wp_list_comments( array( 'callback' => 'woocommerce_comments' ), $comments);
}

function pdmi_show_product_all_ratings() {
	$all_ids = get_posts( array(
			'post_type' => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
	 ) );
	

	// Bepaal alle producten en bepaal de gemiddelde rating score
	foreach ( $all_ids as $id ) {
		$product = wc_get_product( $id );
		$product_name = $product->get_name(); // Get product name.
		if ($product->get_average_rating() >0) {		
		   	echo ("<style>.hrclass {	position: relative;top: 20px;		border: none;		height: 6px;	background: black;		margin-bottom: 50px;	}	</style>");	
			$prodLink=$product->get_permalink();
			echo "<h3><a href='" . esc_url($prodLink) . "'>" . esc_attr($product_name) . "</a></h3>";	
			echo "<a href='" . esc_url($prodLink) . "'>" . wp_kses_post($product->get_image()). "</a>";			
			pdmi_show_all_product_reviewss($id);			
			echo wp_kses_post("<hr class='hrclass' >");
		}
	}
}

function pdmi_get_average_rating_all_products() {
	$total_product_score=0;
	$products_with_rating=0;
	$product = wc_get_product( $id );
	$counter =0;
	
	$all_ids = get_posts( array(
			'post_type' => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
	 ) );
	
	// Bepaal alle producten en bepaal de gemiddelde rating score
	foreach ( $all_ids as $id ) {
	   $product = wc_get_product( $id );
		if ($product->get_rating_count() > 0){
			  $total_product_score = $total_product_score + $product->get_average_rating();
			  $products_with_rating = $products_with_rating +1;
		   }   
	   $counter=$counter+1;
   }
	
	$gemiddeldeRating = $total_product_score / $products_with_rating ;
	
	return $gemiddeldeRating;
}

function pdmi_show_average_rating_stars_all_products() {
	
	$pdmi_Avg_Rating = pdmi_get_average_rating_all_products() ;

	
	echo '<div class="star-rating"><span style="width:'.( ( esc_attr($pdmi_Avg_Rating) / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'. esc_attr($pdmi_Avg_Rating) .'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>'; 
}
