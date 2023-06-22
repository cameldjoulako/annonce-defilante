<?php

/**
 * Plugin Name: Annonce Défilante By Homedeve
 * Plugin URI: https://homedeve.com/plugins/annonce-defilante/
 * Description: Extension pour ajouter une bande d'annonces défilante sur le site. La bande d'annonce sera placé au dessus du menu de navigation.
 * Version: 1.0.0
 * Author: Camel Djoulako
 * Author URI: https://cameldjoulako.homedeve.com/
 */

class AnnonceDefilante
{

    public function __construct()
    {
        add_action('init', array($this, 'register_post_type'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('annonce-defilante', array($this, 'shortcode_annonce_defilante'));
        add_action('save_post_annonce', array($this, 'save_annonce'));
    }


    public function inject_annonce_defilante()
    {
        $output = $this->insert_annonce_defilante();

        add_action('wp_footer', function () use ($output) {
            echo $output;
        });
    }



    public function register_post_type()
    {
        $labels = array(
            'name' => 'Annonces',
            'singular_name' => 'Annonce',
            'menu_name' => 'Annonces',
            'add_new' => 'Ajouter une annonce',
            'add_new_item' => 'Ajouter une nouvelle annonce',
            'edit' => 'Modifier',
            'edit_item' => 'Modifier l\'annonce',
            'new_item' => 'Nouvelle annonce',
            'view' => 'Voir',
            'view_item' => 'Voir l\'annonce',
            'search_items' => 'Rechercher des annonces',
            'not_found' => 'Aucune annonce trouvée',
            'not_found_in_trash' => 'Aucune annonce trouvée dans la corbeille',
            'parent' => 'Annonce parente'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => false,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 20,
            'supports' => array('title', 'editor')
        );

        register_post_type('annonce', $args);
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Les annonces',
            'Liste des annonces',
            'manage_options',
            'gestion_annonces',
            array($this, 'display_gestion_annonces_page'),
            'dashicons-megaphone',
            20
        );
    }

    public function display_gestion_annonces_page()
    {
        if (isset($_POST['annonce_save'])) {
            $post_id = $_POST['annonce_id'];
            $this->save_annonce($post_id);
        }

        $annonces = get_posts(array(
            'post_type' => 'annonce',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ));





?>
        <div class="wrap">
            <h1>Les Annonces</h1>
            <table class="wp-list-table widefat fixed">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Titre</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($annonces as $annonce) : ?>
                        <tr>
                            <td><?php echo $annonce->ID; ?></td>
                            <td><?php echo esc_html($annonce->post_title); ?></td>
                            <td>
                                <a href="<?php echo get_edit_post_link($annonce->ID); ?>">Modifier</a> |
                                <a href="<?php echo get_delete_post_link($annonce->ID); ?>" class="delete-annonce">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
            (function() {
                var deleteLinks = document.getElementsByClassName('delete-annonce');

                for (var i = 0; i < deleteLinks.length; i++) {
                    deleteLinks[i].addEventListener('click', function(e) {
                        e.preventDefault();
                        if (confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')) {
                            window.location.href = this.getAttribute('href');
                        }
                    });
                }
            })();
        </script>
<?php
    }

    public function save_annonce($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['annonce_nonce']) || !wp_verify_nonce($_POST['annonce_nonce'], 'annonce_nonce')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['annonce_title'])) {
            $title = sanitize_text_field($_POST['annonce_title']);
            update_post_meta($post_id, 'annonce_title', $title);
        }
    }

    public function enqueue_admin_scripts($hook)
    {
        if ($hook !== 'toplevel_page_gestion-annonces') {
            return;
        }

        wp_enqueue_script('annonce-admin', plugin_dir_url(__FILE__) . 'js/annonce-admin.js', array(), '1.0.0', true);
        /* var_dump("bien charger");
        die(); */
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('annonce-defilante', plugin_dir_url(__FILE__) . 'css/annonce-defilante.css', array(), '1.0.0');
        wp_enqueue_script('annonce-defilante', plugin_dir_url(__FILE__) . 'js/annonce-defilante.js', array('jquery'), '1.0.0', true);

        $output = $this->insert_annonce_defilante();

        add_action('wp_body_open', function () use ($output) {
            echo $output;
        });
    }




    public function insert_annonce_defilante()
    {
        $annonces = get_posts(array(
            'post_type' => 'annonce',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        // Déterminer si la barre d'administration WordPress est visible
        $admin_bar_visible = is_admin_bar_showing();

        // Ajouter une classe conditionnelle en fonction de la visibilité de la barre d'administration
        $container_class = $admin_bar_visible ? 'annonce-defilante-container admin-bar-visible' : 'annonce-defilante-container';




        ob_start();

        echo '<div class="' . $container_class . '">';
        echo '<ul>';

        foreach ($annonces as $annonce) {

            /*$title = esc_html(get_post_meta($annonce->ID, 'annonce_title', true));
    echo '<li class="annonce-defilante-text">' . $title . '</li>';*/

            $content = apply_filters('the_content', $annonce->post_content);
            echo '<li class="annonce-defilante-text">' . $content . '</li>';
        }

        echo '</ul>';
        echo '</div>';

        return ob_get_clean();
    }




    public function shortcode_annonce_defilante()
    {
        $annonces = get_posts(array(
            'post_type' => 'annonce',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        $output = '<div class="annonce-defilante-container">';
        foreach ($annonces as $annonce) {
            $title = esc_html(get_post_meta($annonce->ID, 'annonce_title', true));
            $output .= '<span class="annonce-defilante-text">' . $title . '</span>';
        }
        $output .= '</div>';

        return $output;
    }
}

new AnnonceDefilante();
