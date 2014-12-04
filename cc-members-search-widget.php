<?php

/**
 * BuddyPress Members Widgets - Search Aware
 *
 * @package BuddyPress
 * @subpackage MembersWidgets
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* Register widgets for groups component */
function cc_members_search_register_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("CC_Members_Search_Widget");') );
}
add_action( 'bp_register_widgets', 'cc_members_search_register_widgets' );

/**
 * Members Widget, Search aware.
 */
class CC_Members_Search_Widget extends WP_Widget {

	/**
	 * Constructor method.
	 */
	function __construct() {
		$widget_ops = array(
			'description' => __( 'A search-term-aware list of members', 'buddypress' ),
			'classname' => 'widget_cc_members_search_widget buddypress widget',
		);
		parent::__construct( false, $name = _x( '(BuddyPress) Members Search Results', 'widget name', 'buddypress' ), $widget_ops );

		// if ( is_active_widget( false, false, $this->id_base ) && !is_admin() && !is_network_admin() ) {
		// 	wp_enqueue_script( 'bp-widget-members' );
		// }
	}

	/**
	 * Display the Members widget.
	 *
	 * @see WP_Widget::widget() for description of parameters.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Widget settings, as saved by the user.
	 */
	function widget( $args, $instance ) {

		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

		// Get search terms if set, then remove leading '@' if necessary.
		$search = ( ! empty( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;

		if ( $search ) {
			// Remove leading '@' if necessary. BP won't find @cassie for cassie
			$search = preg_replace('/^@?/', '', $search);
		}

		echo $before_widget;

		$title = $instance['link_title'] ? '<a href="' . trailingslashit( bp_get_root_domain() . '/' . bp_get_members_root_slug() ) . '">' . $title . '</a>' : $title;

		echo $before_title
		   . $title
		   . $after_title;

		$members_args = array(
			'user_id'         => 0,
			'type'            => 'active',
			'per_page'        => $instance['max_members'],
			'max'             => $instance['max_members'],
			'populate_extras' => true,
			'search_terms'	  => $search,
		);

		?>

		<?php if ( bp_has_members( $members_args ) ) : ?>
			<ul id="members-list" class="item-list">
				<?php while ( bp_members() ) : bp_the_member(); ?>
					<li class="vcard">
						<div class="item-avatar">
							<a href="<?php bp_member_permalink() ?>" title="<?php bp_member_name() ?>"><?php bp_member_avatar() ?></a>
						</div>

						<div class="item">
							<div class="item-title fn"><a href="<?php bp_member_permalink() ?>" title="<?php bp_member_name() ?>"><?php bp_member_name() ?></a></div>
							<div class="item-meta">
								<span class="activity">
								<?php bp_member_last_active(); ?>
								</span>
							</div>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>

		<?php else: ?>

			<div class="widget-error">
				<?php _e('No matching members found.', 'buddypress') ?>
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
	<?php
	}

	/**
	 * Update the Members widget options.
	 *
	 * @param array $new_instance The new instance options.
	 * @param array $old_instance The old instance options.
	 * @return array $instance The parsed options to be saved.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] 	    = strip_tags( $new_instance['title'] );
		$instance['max_members']    = strip_tags( $new_instance['max_members'] );
		$instance['link_title']	    = (bool)$new_instance['link_title'];

		return $instance;
	}

	/**
	 * Output the Members widget options form.
	 *
	 * @param $instance Settings for this widget.
	 */
	function form( $instance ) {
		$defaults = array(
			'title' 	 => __( 'Members', 'buddypress' ),
			'max_members' 	 => 5,
			'member_default' => 'active',
			'link_title' 	 => false
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		$title 		= strip_tags( $instance['title'] );
		$max_members 	= strip_tags( $instance['max_members'] );
		$link_title	= (bool)$instance['link_title'];
		?>

		<p><label for="bp-core-widget-title"><?php _e('Title:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>

		<p><label for="<?php echo $this->get_field_name('link_title') ?>"><input type="checkbox" name="<?php echo $this->get_field_name('link_title') ?>" value="1" <?php checked( $link_title ) ?> /> <?php _e( 'Link widget title to Members directory', 'buddypress' ) ?></label></p>

		<p><label for="bp-core-widget-members-max"><?php _e('Max members to show:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_members' ); ?>" name="<?php echo $this->get_field_name( 'max_members' ); ?>" type="text" value="<?php echo esc_attr( $max_members ); ?>" style="width: 30%" /></label></p>

	<?php
	}
}
