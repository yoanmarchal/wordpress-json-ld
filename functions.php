<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 */
if (!function_exists('build_jsonld')) :
/*
* build json-ld for SEO
*/
function build_jsonld($return = false)
{
    require_once get_template_directory().'/jsonld.php';
    $output = jsonLD();
    if ($return) {
        return json_encode($output);
    } else {
        echo '<script type="application/ld+json">'.json_encode($output).'</script>';
    }
}
endif;
add_action('wp_head', 'build_jsonld');

if (!function_exists('build_jsonldBreadcrumb')) :

/*
build json-ld for SEO items list
https://amged.me/
*/
function build_jsonldBreadcrumb()
{
    $jsonLD['@context'] = 'http://schema.org/';
    $jsonLD['@type'] = 'BreadcrumbList';
    //---------------------------------
    // pre-define some variables
    //---------------------------------
    $home_title = get_bloginfo('name');
    $home_url = esc_url(home_url('/'));
    $blog_url = get_permalink(get_option('page_for_posts'));
    $blog_title = get_the_title(get_option('page_for_posts', true));
    $url = $url ? $url : get_permalink();
    $title = $title ? $title : get_the_title();
    $itemList = [];

    //----------------------------------
    // Output
    //-----------------------------------
     $itemList[] = [
                          '@type'    => 'ListItem',
                          'position' => 1,
                          'item'     => ['@id' => $home_url, 'name' => $home_title],
                      ];
    //----------------------------------------------
    // figure out the next number
    // <meta property="position" content="{NUM}">
    //----------------------------------------------
    $next = 2;
    if (is_single() or is_home()) {
        //-------------------------------------
        // we have a blog "post"
        // so we added the blog url (not home)
        // so next is 3 not 2!
        //--------------------------------------
        $itemList[] = [
                          '@type'    => 'ListItem',
                          'position' => 2,
                          'item'     => ['@id' => $blog_url, 'name' => $blog_title],
                      ];
        $next = 3;
    }
    if (is_single() or is_page() or is_tag() or is_category()) {

    //----------------------------------
    // is it a tag?
    //----------------------------------
    if (is_tag()) {
        $title = single_tag_title('', false);
        $tag_id = get_term_by('name', $title, 'post_tag');
        $url = get_tag_link($tag_id->term_id);
    }

        if (is_category()) {
            $title = single_cat_title('', false);
            $category_id = get_cat_ID($title);
            $url = get_category_link($category_id);
        }

        $itemList[] = [
                          '@type'    => 'ListItem',
                          'position' => $next,
                          'item'     => ['@id' => $url, 'name' => $title],
                      ];
    }
    $jsonLD['itemListElement'] = $itemList;
    /*echo '<pre>';
    print_r(json_encode($jsonLD));
    echo '</pre>';*/
    if ((is_page() or is_single() or is_home() or is_tag() or is_category()) and (!is_front_page())) {
        echo '<script type="application/ld+json">';
        echo json_encode($jsonLD);
        echo '</script>';
    }
}
endif;
add_action('wp_head', 'build_jsonldBreadcrumb');
