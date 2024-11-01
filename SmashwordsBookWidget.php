<?php
/**
 * Plugin Name: Smashwords Book Widget
 * Plugin URI: http://unleashyouradventure.com/smashwords-book-widget-for-wordpress
 * Description: A widget that displayes books from Smashwords by author.
 * Version: 1.18
 * Author: unleashyouradventure
 * Author URI: http://unleashyouradventure.com
 *
 * Licensed under The MIT License
 * It uses the libary PHP Simple HTML Dom: http://sourceforge.net/projects/simplehtmldom/
 */

include_once("SmashwordsBookWidget/SmashwordsFunctions.php");

/**
 * Add function to widgets_init that'll load our widget.
 */
add_action( 'widgets_init', 'smashwords_book_load_widgets' );
add_action('wp_enqueue_scripts', 'add_my_scripts');

/**
 * Register widget.
 */
function smashwords_book_load_widgets() {
   register_widget( 'Smashwords_Book_Widget' );
}

function add_my_scripts() {
	$myStyleUrl = plugins_url('/SmashwordsBookWidget/SmashwordsBookWidget.css', __FILE__);
	wp_register_style('smashwordsBookWidget', $myStyleUrl);
	wp_enqueue_style( 'smashwordsBookWidget');

	// Vertical
	if(true){
	    $myStyleUrl = plugins_url('/SmashwordsBookWidget/SmashwordsBookWidgetVertical.css', __FILE__);
	    wp_register_style('smashwordsBookWidgetVertical', $myStyleUrl);
	    wp_enqueue_style( 'smashwordsBookWidgetVertical');
	}
	wp_enqueue_script(
	    'smashwordsBookWidget_js',
	    plugins_url('SmashwordsBookWidget/SmashwordsBookWidget.js', __FILE__),
	    array('jquery')
	);
}

/**
 * Widget class.
 */
class Smashwords_Book_Widget extends WP_Widget {

    private $swfunctions = null;

    public function __construct() {
		parent::__construct(
	 		'Smashwords_Book_Widget', // Base ID
			'Smashwords_Book_Widget', // Name
			array( 'description' => __( 'Displayes the books published by an author on Smashwords', 'text_domain' ), ) // Args
		);
        $this->swfunctions = new SmashwordsFunctions();
    }

   /**
    * Output on the screen.
    */
    function widget( $args, $instance ) {
        extract( $args );

        $title = apply_filters('widget_title', $instance['title'] );
	echo $before_widget;
        if ( ! empty( $title ) ){
	    echo $before_title . $title . $after_title;
        }
        echo($this->swfunctions->printOutput($instance));
        echo $after_widget;
   }

   /**
    * Update the widget settings.
    */
   function update( $new_instance, $old_instance ) {
      $instance = $old_instance;

      // Strip tags for title and name to remove HTML (important for text inputs)
      $instance['title'] = strip_tags( $new_instance['title'] );
      $instance['author'] = strip_tags( $new_instance['author'] );
      $instance['link_text'] = strip_tags( $new_instance['link_text'] );
      $instance['affiliate'] = strip_tags( $new_instance['affiliate'] );
      $no_books = intval( $new_instance['no_books'] );
      if($no_books<1) $no_books=1;
      $instance['no_books'] = $no_books;
      $instance['slide'] = $new_instance['slide'];
      $instance['vertical'] = $new_instance['vertical'];
      return $instance;
   }

   /**
    * Displays the widget settings controls on the widget panel.
    */
   function form( $instance ) {

      // default settings
      $defaults = array( 'title' => __('Books', 'smashwords_book_widget'),
                         'author' => __('UnleashYourAdventure', 'smashwords_book_widget'),
                         'link_text' => __('More books', 'smashwords_book_widget'),
                         'affiliate' => __('UnleashYourAdventure', 'smashwords_book_widget'),
                         'no_books' => __('1', 'smashwords_book_widget'),
                         'slide' => __('false', 'smashwords_book_widget'),
                         'vertical' => __('false', 'smashwords_book_widget')
                  );
      $instance = wp_parse_args( (array) $instance, $defaults ); ?>

      <!-- Widget Title: Text Input -->
      <p>
         <label for="<?php echo $this->get_field_id( 'title' ); ?>"><b><?php _e('Title:', 'hybrid'); ?></b></label>
         <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
      </p>

      <!-- author: Text Input -->
      <p>
         <label for="<?php echo $this->get_field_id( 'author' ); ?>"><b><?php _e('Author:', 'UnleashYourAdventure'); ?></b></label>
         <input id="<?php echo $this->get_field_id( 'author' ); ?>" name="<?php echo $this->get_field_name( 'author' ); ?>" value="<?php echo $instance['author']; ?>" style="width:100%;" />
<br>
         The authors user name at Smashwords. This can also be a publisher. Required. More then one author can be entered, separated with commas.
         Exaple: author1,author2,author3
      </p>

      <!-- link text: Text Input -->
      <p>
         <label for="<?php echo $this->get_field_id( 'link_text' ); ?>"><b><?php _e('Link Text to Authors page:', 'More books'); ?></b></label>
         <input id="<?php echo $this->get_field_id( 'link_text' ); ?>" name="<?php echo $this->get_field_name( 'link_text' ); ?>" value="<?php echo $instance['link_text']; ?>" style="width:100%;" />
<br>
         Leave empty for no link.
      </p>
      <!-- your affiliate name: Text Input -->
      <p>
         <label for="<?php echo $this->get_field_id( 'affiliate' ); ?>"><b><?php _e('Affiliate user name:', 'UnleashYourAdventure'); ?></b></label>
         <input id="<?php echo $this->get_field_id( 'affiliate' ); ?>" name="<?php echo $this->get_field_name( 'affiliate' ); ?>" value="<?php echo $instance['affiliate']; ?>" style="width:100%;" />
<br>
         Your user name. It will be included in all links to Smashwords as an affiliate partner. See <a href="http://www.smashwords.com/about/affiliate" target="_blank">Smashwords</a> for more information.<br>
Optional. Leave blank for no affiliate link.<br>Please consider to use 'UnleashYourAdventure' to support the development of this widget.
      </p>
      <!-- number of books: Text Input -->
      <p>
      <label for="<?php echo $this->get_field_id( 'no_books' ); ?>"><b><?php _e('Number of books:', '1'); ?></b></label>
               <input id="<?php echo $this->get_field_id( 'no_books' ); ?>" name="<?php echo $this->get_field_name( 'no_books' ); ?>" value="<?php echo $instance['no_books']; ?>" style="width:100%;" />
      <br>
               How many books should be shown? Please enter a number.
      </p>
      <!-- slide show: yes / no input -->
      <p>
      <label for="<?php echo $this->get_field_id( 'slide' ); ?>"><b><?php _e('Enable slide show:', 'false'); ?></b></label>
      <select id="<?php echo $this->get_field_id( 'slide' ); ?>" name="<?php echo $this->get_field_name( 'slide' ); ?>" >
        <option value="false" <?php selected('false', $instance["slide"]); ?>>no</option>
        <option value="true" <?php selected('true', $instance["slide"]); ?>>yes</option>
      </select>
           <br>
               Should the widget appear as a slide show?
            </p>
            <!-- vertical show: yes / no input -->
            <p>
            <label for="<?php echo $this->get_field_id( 'vertical' ); ?>"><b><?php _e('Display books vertically:', 'false'); ?></b></label>
                  <select id="<?php echo $this->get_field_id( 'vertical' ); ?>" name="<?php echo $this->get_field_name( 'vertical' ); ?>" >
                    <option value="false" <?php selected('false', $instance["vertical"]); ?>>no</option>
                    <option value="true" <?php selected('true', $instance["vertical"]); ?>>yes</option>
                  </select>
                       <br>
                           Should the books be displayed above each other?
                        </p>
   <?php
   }
}
?>
