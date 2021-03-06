<?php
add_theme_support('title-tag');
add_theme_support('post-thumbnails');
add_theme_support('automatic-feed-links');
add_theme_support('custom-header');
add_theme_support("custom-background");

//Gutenberg用スタイルの読み込み
add_theme_support( 'wp-block-styles' );
 
//「幅広」と「全幅」に対応
add_theme_support( 'align-wide' );

//Gutenbergで埋め込んだ動画がレスポンシブに対応する機能を有効にする
add_theme_support( 'responsive-embeds' );

//HTML5のタグで出力
add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );

add_theme_support( 'custom-logo' ); 


//タイトル出力
function hamburger_title($title)
{
    if (is_front_page() && is_home()) { //トップページなら
        $title = get_bloginfo('name', 'display');
    } elseif (is_singular()) { //シングルページなら
        $title = single_post_title('', false);
    }
    return $title;
}
add_filter('pre_get_document_title', 'hamburger_title');

//ファイルの読み込みに関する記述をまとめた関数
function hamburger_script()
{

    //wp_enqueue_styleやwp_enqueue_scriptはこの中で使う


    wp_enqueue_style('robot', '///fonts.gstatic.com', array());
    wp_enqueue_style('M+PLUS+1p', '//fonts.googleapis.com/css2?family=M+PLUS+1p:wght@400;700&family=Roboto:wght@500&display=swap', array());

    wp_enqueue_style('font-awesome', '//use.fontawesome.com/releases/v5.15.4/css/all.css', array(), '5.15.4');
    wp_enqueue_style('normalize', get_template_directory_uri() . '/css/ress.css', array(), '1.0.0');
    wp_enqueue_style('hamburger', get_template_directory_uri() . '/css/style.css', array(), '1.0.0');
    wp_enqueue_style('style', get_template_directory_uri() . 'style.css', array(), '1.0.0');
    wp_enqueue_script('jQuery', get_template_directory_uri() . '/js/jquery-3.6.0.min.js', array(), '3.6.0', true);
    wp_enqueue_script('js', get_template_directory_uri() . '/js/script.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'hamburger_script'); //上記のファイルの読み込みに関する記述をまとめた関数を、「wp_enqueue_scripts」アクションにフックさせる。


//カテゴリー説明文でHTMLタグを使えるようにする
remove_filter('pre_term_description', 'wp_filter_kses');
//pタグが付与されるのでpタグが邪魔な場合取り除く
remove_filter('term_description', 'wp_kses_data');

//カテゴリー説明文の代わりに表示するビジュアルエディターのHTML
add_filter('edit_category_form_fields', 'cat_description');
if (!function_exists('cat_description')) :
    function cat_description($tag)
    {
?>
        <table class="form-table">
            <tr class="form-field">
                <th scope="row" valign="top"><label for="description"><?php _e('Description'); ?></label></th>
                <td>
                    <?php
                    $settings = array('wpautop' => true, 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => '15', 'textarea_name' => 'description');
                    wp_editor(wp_kses_post($tag->description, ENT_QUOTES, 'UTF-8'), 'cat_description', $settings);
                    ?>
                    <br />
                    <span class="description"><?php _e('The description is not prominent by default; however, some themes may show it.'); ?></span>
                </td>
            </tr>
        </table>
        <?php
    }
endif;

//カテゴリー編集ページから「カテゴリー説明文」を取り除く
add_action('admin_head', 'remove_default_category_description');
if (!function_exists('remove_default_category_description')) :
    function remove_default_category_description()
    {
        global $current_screen;
        if ($current_screen->id == 'edit-category') {
        ?>
            <script type="text/javascript">
                jQuery(function($) {
                    $('textarea#description').closest('tr.form-field').remove();
                });
            </script>
<?php
        }
    }
endif;
//このカスタマイズは「カテゴリー編集」画面のみで適用される。「カテゴリー一覧」画面では適用されないので注意。
//参考ページ:https://nelog.jp/visual-category-description-editor

//ページネーション処理
function pagination($pages = '', $range = 10) //$pages：総ページ数, $range：ページネーションを表示する数を設定する数値
{
    $showitems = ($range * 1) + 1; //$showitems：ページネーションを表示する数を設定する数値
    global $paged;
    if (empty($paged)) $paged = 1; //$paged：現在のページ番号
    if ($pages == '') {
        global $wp_query;
        $pages = $wp_query->max_num_pages;
        if (!$pages) {
            $pages = 1;
        }
    }
    if (1 != $pages) {
        // 画像を使う時用に、テーマのパスを取得
        $img_pass = get_template_directory_uri();
        echo "<div class=\"p-pagination\">";
        echo "<div class=\"p-pagination__wrapper\">";
        // 「1/2」表示 現在のページ数 / 総ページ数
        echo "<div class=\"p-pagination__pages\">" . "Page " . $paged . "/" . $pages . "</div>";
        // 「前へ」を表示
        if ($paged > 1) echo "<a class=\"p-pagination__prev\" href='" . get_pagenum_link($paged - 1) . "'><span>前へ</span></a>";
        // ページ番号を出力
        echo "<ol class=\"p-pagination__body\">\n";
        for ($i = 1; $i <= $pages; $i++) {
            if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems)) {
                echo ($paged == $i) ? "<li class=\"-current\">" . $i . "</li>" : // 現在のページの数字はリンク無し
                    "<li><a href='" . get_pagenum_link($i) . "'>" . $i . "</a></li>";
            }
        }
        // [...] 表示
        // if(($paged + 4 ) < $pages){
        //     echo "<li class=\"notNumbering\">...</li>";
        //     echo "<li><a href='".get_pagenum_link($pages)."'>".$pages."</a></li>";
        // }
        echo "</ol>\n";
        // 「次へ」を表示
        if ($paged < $pages) echo "<a class=\"p-pagination__next\" href='" . get_pagenum_link($paged + 1) . "'><span>次へ</span></a>";
        echo "</div>\n";
        echo "</div>\n";
    }
}
//参考ページ:https://since-inc.jp/blog/8506
//ここまでページネーション処理

//メニュー設定
register_nav_menus(
    array(
        'sidebar_menu' => 'サイドバーメニュー',
        'footer_menu' => 'フッターメニュー',
    )
);

//サイドバーのウィジェット実装
function hamburger_widgets_init()
{
    register_sidebar(
        array(
            'name'          => 'メニューウィジェット',
            'id'            => 'menu_widget',
            'description'   => 'メニュー用ウィジェットです',
            'before_widget' => '<div id="%1$s" class="sidebar_widget %2$s">',
            'after_widget'  => '</div>',
        )
    );
}
add_action('widgets_init', 'hamburger_widgets_init');

// editor.styleの読み込み（Gutenberg用）
function hamburger_theme_support_setup()
{
    add_theme_support('editor-styles');
    add_editor_style('editor-style.css');
}
add_action('after_setup_theme', 'hamburger_theme_support_setup');
//参考ページ：https://techmemo.biz/wordpress/add-gutenberg-editor-style/

if (!isset($content_width)) {
    $content_width = 1920;
}

register_block_style(
    'core/heading',
    array(
        'name'         => 'design01',
        'label'        => 'デザイン01',
        'inline_style' => '.is-style-design01 { 
            border-left: solid 8px orange; 
            padding-left: 12px;
        }',
    )
);

//ブロックスタイルの設定
register_block_style(
    'core/heading',
    array(
        'name'         => 'design02',
        'label'        => 'デザイン02',
        'inline_style' => '.is-style-design02 { 
            padding: 20px 10px 15px;
            border-radius: 10px;
            background: #f5d742;
        }
        .is-style-design02::before {
            content: "●";
            color: #ffffff;
            margin-right: 10px;
        }',
    )
);

//ブロックパターンの登録
register_block_pattern(
    'wpdocs-my-plugin/my-awesome-pattern',
    array(
        'title'       => __('Two buttons', 'wpdocs-my-plugin'),
        'description' => _x('Two horizontal buttons, the left button is filled in, and the right button is outlined.', 'Block pattern description', 'wpdocs-my-plugin'),
        'content'     => "<!-- wp:buttons {\"align\":\"center\"} -->\n<div class=\"wp-block-buttons aligncenter\"><!-- wp:button {\"backgroundColor\":\"very-dark-gray\",\"borderRadius\":0} -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link has-background has-very-dark-gray-background-color no-border-radius\">" . esc_html__('Button One', 'wpdocs-my-plugin') . "</a></div>\n<!-- /wp:button -->\n\n<!-- wp:button {\"textColor\":\"very-dark-gray\",\"borderRadius\":0,\"className\":\"is-style-outline\"} -->\n<div class=\"wp-block-button is-style-outline\"><a class=\"wp-block-button__link has-text-color has-very-dark-gray-color no-border-radius\">" . esc_html__('Button Two', 'wpdocs-my-plugin') . "</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons -->",
    )
);
