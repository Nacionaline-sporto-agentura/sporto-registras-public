<?php
/**
 * The template for displaying 404 pages (Not Found)
**/

get_header(); ?>
		<div id="content" class="site-content-404" role="main">
			<h1 class="title"><?php _e('404: puslapis nerastas', 'sr'); ?></h1>
            <div class="content">
                <p>
                    <?php _e('Gali būti, kad puslapis neegzistuoja, buvo ištrintas arba perkeltas į kitą vietą. Siūlome pasinaudoti paieška:', 'sr'); ?>
                </p>
                <form role="search" method="get" class="search-form" action="<?php echo home_url('/'); ?>">
                    <label>
                        <span class="screen-reader-text"><?php echo _x('Ieškoti:', 'label', 'sr') ?></span>
                        <input type="search" class="search-field"
                            placeholder="<?php echo esc_attr_x('Ieškoti …', 'placeholder', 'sr') ?>"
                            value="<?php echo get_search_query() ?>" name="s"
                            title="<?php echo esc_attr_x('Ieškoti:', 'label', 'sr') ?>" />
                    </label>
                    <input type="submit" class="search-submit" value="<?php echo esc_attr_x('Ieškoti', 'submit button', 'sr') ?>" />
                </form>
			</div><!-- .page-content -->
		</div><!-- #content -->
<?php get_footer(); ?>