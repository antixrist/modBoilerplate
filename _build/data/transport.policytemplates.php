<?php

$templates = array();

$tmp = array(
  'modBoilerplatePolicyTemplate' => array(
    'description' => 'A policy for modBoilerplate.',
    'template_group' => 1,
    'permissions' => array(
      'modboilerplate_item_new' => array(),
      'modboilerplate_item_view' => array(),
      'modboilerplate_item_view_another' => array(),
      'modboilerplate_item_view_activated' => array(),
      'modboilerplate_item_view_activated_another' => array(),
      'modboilerplate_item_view_deactivated' => array(),
      'modboilerplate_item_view_deactivated_another' => array(),
      'modboilerplate_item_view_published' => array(),
      'modboilerplate_item_view_published_another' => array(),
      'modboilerplate_item_view_unpublished' => array(),
      'modboilerplate_item_view_unpublished_another' => array(),
      'modboilerplate_item_view_deleted' => array(),
      'modboilerplate_item_view_deleted_another' => array(),
      'modboilerplate_item_list' => array(),
      'modboilerplate_item_list_another' => array(),
      'modboilerplate_item_remove' => array(),
      'modboilerplate_item_remove_another' => array(),
      'modboilerplate_item_remove_activated' => array(),
      'modboilerplate_item_remove_activated_another' => array(),
      'modboilerplate_item_remove_deactivated' => array(),
      'modboilerplate_item_remove_deactivated_another' => array(),
      'modboilerplate_item_remove_published' => array(),
      'modboilerplate_item_remove_published_another' => array(),
      'modboilerplate_item_remove_unpublished' => array(),
      'modboilerplate_item_remove_unpublished_another' => array(),
      'modboilerplate_item_remove_deleted' => array(),
      'modboilerplate_item_remove_deleted_another' => array(),
      'modboilerplate_item_edit' => array(),
      'modboilerplate_item_edit_another' => array(),
      'modboilerplate_item_activate' => array(),
      'modboilerplate_item_activate_another' => array(),
      'modboilerplate_item_deactivate' => array(),
      'modboilerplate_item_deactivate_another' => array(),
      'modboilerplate_item_publish' => array(),
      'modboilerplate_item_publish_another' => array(),
      'modboilerplate_item_unpublish' => array(),
      'modboilerplate_item_unpublish_another' => array(),
      'modboilerplate_item_delete' => array(),
      'modboilerplate_item_delete_another' => array(),
      'modboilerplate_item_restore' => array(),
      'modboilerplate_item_restore_another' => array(),
    )
  ),
);

foreach ($tmp as $k => $v) {
  $permissions = array();

  if (isset($v['permissions']) && is_array($v['permissions'])) {
    foreach ($v['permissions'] as $k2 => $v2) {
      /* @var modAccessPermission $event */
      $permission = $modx->newObject('modAccessPermission');
      $permission->fromArray(array_merge(array(
          'name' => $k2,
          'description' => $k2,
          'value' => true,
        ), $v2)
        ,'', true, true);
      $permissions[] = $permission;
    }
  }

  /* @var $template modAccessPolicyTemplate */
  $template = $modx->newObject('modAccessPolicyTemplate');
  $template->fromArray(array_merge(array(
      'name' => $k,
      'lexicon' => PKG_NAME_LOWER.':permissions',
    ),$v)
    ,'', true, true);

  if (!empty($permissions)) {
    $template->addMany($permissions);
  }

  $templates[] = $template;
}

return $templates;