<?
/**
 * Publish an Item
 */

require_once (dirname(__FILE__) .'/update.base.php');

class modBoilerplateItemPublishProcessor extends modBoilerplateItemUpdateBaseProcessor {
  public $permission = '';
  public $permission_owner = 'modboilerplate_item_publish_owner';
  public $permission_colleague = 'modboilerplate_item_publish_colleague';
  public $permission_another = 'modboilerplate_item_publish_another';
  public $beforeSaveEvent = 'modBoilerplateItemBeforePublish';
  public $afterSaveEvent = 'modBoilerplateItemAfterPublish';

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

    $this->setProperty('published', 1);
    $this->setProperty('publishedon', time());
    $this->setProperty('publishedby', $this->modx->getLoginUserID());

    return !$this->hasErrors();
  }

}

return 'modBoilerplateItemPublishProcessor';
