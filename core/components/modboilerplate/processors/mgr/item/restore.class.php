<?
/**
 * Restore an Item
 */

require_once (dirname(__FILE__) .'/update.base.php');

class modBoilerplateItemRestoreProcessor extends modBoilerplateItemUpdateBaseProcessor {
  public $permission = '';
  public $permission_owner = 'modboilerplate_item_restore_owner';
  public $permission_colleague = 'modboilerplate_item_restore_colleague';
  public $permission_another = 'modboilerplate_item_restore_another';
  public $beforeSaveEvent = 'modBoilerplateItemBeforeRestore';
  public $afterSaveEvent = 'modBoilerplateItemAfterRestore';

  /**
   * {@inheritDoc}
   */
  public function getConfigForCheckCustomPermissionsOwner () {
    return array();
  }

  /**
   * {@inheritDoc}
   */
  public function getConfigForCheckCustomPermissionsColleague () {
    return array();
  }

  /**
   * {@inheritDoc}
   */
  public function getConfigForCheckCustomPermissionsAnother () {
    return array();
  }

  /**
   * {@inheritDoc}
   */
  public function beforeSet() {
    $this->unsetProperties();

    $this->setProperty('deleted', 0);
    $this->setProperty('restoredon', time());
    $this->setProperty('restoredby', $this->modx->getLoginUserID());

    return !$this->hasErrors();
  }

}

return 'modBoilerplateItemDeleteProcessor';
