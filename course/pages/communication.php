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
	"SELECT * FROM {$wpdb->prefix}communications WHERE course_id = %d AND group_number = %d ORDER BY timestamp DESC",
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
            <div class="chat-container row">

                <div id="chat-messages" class="chat-messages col">
                    <?php
						if ( ! empty( $messages ) ) {
							foreach ( $messages as $msg ) {
								$msg_wp_user = get_userdata( $msg->user_id );

								if ( in_array( 'student', (array) $msg_wp_user->roles ) ) {
									global $wpdb;
									$student_table = $wpdb->prefix . 'students';
									$msg_user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $msg_wp_user->id));
									$msg_user_role = 'student';
								}
								if ( in_array( 'teacher', (array) $msg_wp_user->roles ) ) {
									global $wpdb;
									$teacher_table = $wpdb->prefix . 'teachers';
									$msg_user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $msg_wp_user->id));
									$msg_user_role = 'teacher';
								}

								// Convert message timestamp to WordPress timezone
								$message_time = strtotime( get_date_from_gmt( $msg->timestamp, 'Y-m-d H:i:s' ) );
								$current_time = current_time( 'timestamp' ); // WordPress local time
								
								// Calculate the time difference
								$time_ago = human_time_diff( $message_time, $current_time ) . ' ago';

								// Format the date and time (e.g., Feb 10, 2025 - 01:57 AM)
								$formatted_time = date( 'M d, Y - h:i A', $message_time );

								// Add 'current-user' class if the message is from the logged-in user
								$chat_class = ($msg->user_id == $user->id) ? 'current-user' : '';
					?>
                    <div class="row chat <?= esc_attr($msg_user_role); ?> <?= esc_attr($chat_class); ?>">
                        <div class="chat-user">
                            <img src="<?php echo !empty($msg_user->image) ? esc_url($msg_user->image) : esc_url($default_user_image); ?>"
                                alt="" class="user-image">
                        </div>
                        <div class="chat-message">
                            <h5 class="user-name">
                                <?= esc_html($msg_user->first_name) . ' ' . esc_html($msg_user->last_name); ?>
                            </h5>
                            <p class="message-text">
                                <?= esc_html($msg->message); ?>
                            </p>
                            <p class="message-time">
                                <?= esc_html( $time_ago . ' | ' . $formatted_time ) ?>
                            </p>
                        </div>

                    </div>
                    <?php
						}
					} else {
						echo "<p class='no-data'>No conversation messages yet.</p>";
					}
					?>
                </div>

                <form method="POST" action="" class="form col">
                    <textarea name="chat_message" placeholder="Laisser les commentaires..." rows="5"></textarea>
                    <button type="submit">Envoyer <i class="fas fa-paper-plane"></i></button>
                </form>

            </div>

        </div>

    </div>
</div>

<?php require_once( get_template_directory() . '/course/templates/footer.php' ); ?>