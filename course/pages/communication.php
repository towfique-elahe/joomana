<?php
/**
 * Template Name: Course | Communication
 */

// Set page title.
global $pageTitle;
$pageTitle = 'Communication';

require_once( get_template_directory() . '/course/templates/header.php' );

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Get the current user.
$user = wp_get_current_user();
$default_user_image = esc_url( get_stylesheet_directory_uri() . '/assets/image/user.png' );

// Get course_id from URL.
if ( ! isset( $_GET['course_id'] ) || empty( $_GET['course_id'] ) ) {
	// Redirect according to the user's role.
	if ( in_array( 'student', (array) $user->roles ) ) {
		wp_redirect( home_url( '/student/course-management/' ) );
		exit;
	} elseif ( in_array( 'teacher', (array) $user->roles ) ) {
		wp_redirect( home_url( '/teacher/course-management/' ) );
		exit;
	} else {
		wp_redirect( home_url() );
		exit;
	}
}
$course_id = intval( $_GET['course_id'] );

/**
 * Determine the user's group number for this course.
 * (Assumes a student has an entry in wp_student_courses and a teacher in wp_teacher_courses.)
 */
$group_number = 0;
if ( in_array( 'student', (array) $user->roles ) ) {
	$student_group = $wpdb->get_var( $wpdb->prepare(
		"SELECT group_number FROM {$wpdb->prefix}student_courses WHERE student_id = %d AND course_id = %d LIMIT 1",
		$user->ID,
		$course_id
	) );
	if ( $student_group ) {
		$group_number = intval( $student_group );
	}
} elseif ( in_array( 'teacher', (array) $user->roles ) ) {
	$teacher_group = $wpdb->get_var( $wpdb->prepare(
		"SELECT group_number FROM {$wpdb->prefix}teacher_courses WHERE teacher_id = %d AND course_id = %d LIMIT 1",
		$user->ID,
		$course_id
	) );
	if ( $teacher_group ) {
		$group_number = intval( $teacher_group );
	}
}

/**
 * Handle form submission for chat messages.
 */
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['chat_message'] ) ) {
	$message = sanitize_text_field( $_POST['chat_message'] );
	if ( ! empty( $message ) ) {
		$wpdb->insert(
			$wpdb->prefix . 'communications',
			[
				'course_id'    => $course_id,
				'user_id'      => $user->ID,
				'group_number' => $group_number,
				'message'      => $message
			]
		);
		// Redirect to avoid resubmission.
		wp_redirect( $_SERVER['REQUEST_URI'] );
		exit;
	}
}

/**
 * Fetch all messages for this course and group.
 * Messages are ordered by timestamp (oldest first).
 */
$messages = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}communications WHERE course_id = %d AND group_number = %d ORDER BY timestamp ASC",
	$course_id,
	$group_number
) );
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once( get_template_directory() . '/course/templates/sidebar.php' ); ?>
    </div>
    <div id="courseCommunication" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Communication</h2>
            <div class="content-breadcrumb">
                <?php if ( current_user_can( 'student' ) ) { ?>
                <a href="<?php echo home_url( '/student/dashboard' ); ?>" class="breadcrumb-link">Tableau de bord</a>
                <?php } elseif ( current_user_can( 'teacher' ) ) { ?>
                <a href="<?php echo home_url( '/teacher/dashboard' ); ?>" class="breadcrumb-link">Tableau de bord</a>
                <?php } ?>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <?php if ( current_user_can( 'student' ) ) { ?>
                <a href="<?php echo home_url( '/student/course-management' ); ?>" class="breadcrumb-link">Gestion de
                    cours</a>
                <?php } elseif ( current_user_can( 'teacher' ) ) { ?>
                <a href="<?php echo home_url( '/teacher/course-management' ); ?>" class="breadcrumb-link">Gestion de
                    cours</a>
                <?php } ?>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Communication</span>
            </div>
        </div>

        <div class="content-section">
            <div class="chat-container">
                <h3>Group Conversation (Group <?php echo esc_html( $group_number ); ?>)</h3>
                <div id="chat-messages">
                    <?php
					if ( ! empty( $messages ) ) {
						foreach ( $messages as $msg ) {
							$msg_user = get_userdata( $msg->user_id );
							echo "<p><strong>" . esc_html( $msg_user->display_name ) . ":</strong> " . esc_html( $msg->message ) . "</p>";
						}
					} else {
						echo "<p>No conversation messages yet.</p>";
					}
					?>
                </div>
                <form method="POST" action="">
                    <textarea name="chat_message" placeholder="Type your message..."></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once( get_template_directory() . '/course/templates/footer.php' ); ?>