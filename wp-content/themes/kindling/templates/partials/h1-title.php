<?php
/**
 * Created by PhpStorm.
 * User: djacobsmeyer
 * Date: 7/7/17
 * Time: 2:57 PM
 *
 * attr: class: string, title: string
 */

if (!$title) {
    return;
}

$class = isset($class) ? "{$class} module-title" : 'module-title';
?>
<h1 class="<?php esc_attr_e($class); ?>">
    <?php echo $title; ?>
</h1>
