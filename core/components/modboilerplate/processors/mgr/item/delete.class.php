<?
/**
 * Delete an Item
 */

require_once (dirname(__FILE__) .'/update.base.php');

class modBoilerplateItemDeleteProcessor extends modBoilerplateItemUpdateBaseProcessor {
  public $permission = '';
  public $permission_owner = 'modboilerplate_item_delete_owner';
  public $permission_colleague = 'modboilerplate_item_delete_colleague';
  public $permission_another = 'modboilerplate_item_delete_another';
  public $beforeSaveEvent = 'modBoilerplateItemBeforeDelete';
  public $afterSaveEvent = 'modBoilerplateItemAfterDelete';

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

    $this->setProperty('deleted', 1);
    $this->setProperty('deletedon', time());
    $this->setProperty('deletedby', $this->modx->getLoginUserID());

    return !$this->hasErrors();
  }

}

return 'modBoilerplateItemDeleteProcessor';
