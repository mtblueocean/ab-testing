<?php

//button shortcode
function zva_button_shortcode( $attr , $content = null ) {
    
    if($attr['url']) {
        $url = $attr['url'];
    }
    else if($attr['link']) {
        $url = $attr['link'];
    }
    
    if(!$content) {
        $content = $attr['title'];
    }
        
    return '<a href="'.esc_url($url).'">' . esc_attr($content) . '</a>';
}
add_shortcode( 'button', 'zva_button_shortcode' );