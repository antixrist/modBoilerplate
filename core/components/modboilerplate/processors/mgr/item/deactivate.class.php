<?
/**
 * Deactivate an Item
 */

require_once (dirname(__FILE__) .'/update.base.php');

class modBoilerplateItemDeactivateProcessor extends modBoilerplateItemUpdateBaseProcessor {
  public $permission = '';
  public $permission_owner = 'modboilerplate_item_deactivate_owner';
  public $permission_colleague = 'modboilerplate_item_deactivate_colleague';
  public $permission_another = 'modboilerplate_item_deactivate_another';
  public $beforeSaveEvent = 'modBoilerplateItemBeforeDeactivate';
  public $afterSaveEvent = 'modBoilerplateItemAfterDeactivate';

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

    $this->setProperty('active', 0);
    $this->setProperty('deactivatedon', time());
    $this->setProperty('deactivatedby', $this->modx->getLoginUserID());

    return !$this->hasErrors();
  }

}

return 'modBoilerplateItemDeactivateProcessor';
