<?
/**
 * Activate an Item
 */

require_once (dirname(__FILE__) .'/update.base.php');

class modBoilerplateItemActivateProcessor extends modBoilerplateItemUpdateBaseProcessor {
  public $permission = '';
  public $permission_owner = 'modboilerplate_item_activate_owner';
  public $permission_colleague = 'modboilerplate_item_activate_colleague';
  public $permission_another = 'modboilerplate_item_activate_another';
  public $beforeSaveEvent = 'modBoilerplateItemBeforeActivate';
  public $afterSaveEvent = 'modBoilerplateItemAfterActivate';

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

    $this->setProperty('active', 1);
    $this->setProperty('activatedon', time());
    $this->setProperty('activatedby', $this->modx->getLoginUserID());

    return !$this->hasErrors();
  }

}

return 'modBoilerplateItemActivateProcessor';
