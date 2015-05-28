<?
/**
 * Unpublish an Item
 */

require_once (dirname(__FILE__) .'/update.base.php');

class modBoilerplateItemUnpublishProcessor extends modBoilerplateItemUpdateBaseProcessor {
  public $permission = '';
  public $permission_owner = 'modboilerplate_item_unpublish_owner';
  public $permission_colleague = 'modboilerplate_item_unpublish_colleague';
  public $permission_another = 'modboilerplate_item_unpublish_another';
  public $beforeSaveEvent = 'modBoilerplateItemBeforeUnpublish';
  public $afterSaveEvent = 'modBoilerplateItemAfterUnpublish';

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

    $this->setProperty('published', 0);
    $this->setProperty('unpublishedon', time());
    $this->setProperty('unpublishedby', $this->modx->getLoginUserID());

    return !$this->hasErrors();
  }

}

return 'modBoilerplateItemUnpublishProcessor';
