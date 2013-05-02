<?php
// $Id: template.block-editing.inc,v 1.1 2008/09/11 11:26:27 johnalbin Exp $

/**
 * @file
 * Contains functions only needed if the user has block editing permissions.
 */

/**
 * Add block editing variables into the block templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */
function Bluebird_preprocess_block_editing(&$vars, $hook) {
  $block = $vars['block'];
  // Display 'edit block' for custom blocks.
  if ($block->module == 'block') {
    $edit_links[] = l('<span>' . t('[Edit Block]') . '</span>', 'admin/build/block/configure/' . $block->module . '/' . $block->delta,
      array(
        'attributes' => array(
          'title' => t('Edit the content of this block.'),
          'class' => 'block-edit',
        ),
        'query' => drupal_get_destination(),
        'html' => TRUE,
      )
    );
  }
  // Display 'configure' for other blocks.
  else {
    $edit_links[] = l('<span>' . t('[Configure]') . '</span>', 'admin/build/block/configure/' . $block->module . '/' . $block->delta,
      array(
        'attributes' => array(
          'title' => t('Configure this block.'),
          'class' => 'block-config',
        ),
        'query' => drupal_get_destination(),
        'html' => TRUE,
      )
    );
  }

  // Display 'edit menu' for Menu blocks.
  if (($block->module == 'menu' || ($block->module == 'user' && $block->delta == 1)) && user_access('administer menu')) {
    $menu_name = ($block->module == 'user') ? 'navigation' : $block->delta;
    $edit_links[] = l('<span>' . t('[Edit menu]') . '</span>', 'admin/build/menu-customize/' . $menu_name,
      array(
        'attributes' => array(
          'title' => t('Edit the menu that defines this block.'),
          'class' => 'block-edit-menu',
        ),
        'query' => drupal_get_destination(),
        'html' => TRUE,
      )
    );
  }
  // Display 'edit menu' for Menu block blocks.
  elseif ($block->module == 'menu_block' && user_access('administer menu')) {
    $menu_name = variable_get('menu_block_' . $block->delta . '_menu_name', 'navigation');
    $edit_links[] = l('<span>' . t('[Edit menu]') . '</span>', 'admin/build/menu-customize/' . $menu_name,
      array(
        'attributes' => array(
          'title' => t('Edit the menu that defines this block.'),
          'class' => 'block-edit-menu',
        ),
        'query' => drupal_get_destination(),
        'html' => TRUE,
      )
    );
  }

  $vars['edit_links_array'] = $edit_links;
  $vars['edit_links'] = '<div class="edit">' . implode(' ', $edit_links) . '</div>';
}
