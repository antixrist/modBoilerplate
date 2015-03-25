<?
/**
 * Delete an Item
 */

require_once (dirname(__FILE__) .'/update.class.php');

class modBoilerplateItemDeleteProcessor extends modBoilerplateItemUpdateProcessor {

  /**
   * {@inheritDoc}
   */
  public function beforeSet() {
    $primaryKeys = array();
    if (is_array($this->primaryKeyField)) {
      foreach ($this->primaryKeyField as $field) {
        $primaryKeys[] = $field;
      }
    } else if ($this->primaryKeyField) {
      $primaryKeys[] = $this->primaryKeyField;
    }

    foreach($this->getProperties() as $k => $v) {
      if (in_array($k, $this->primaryKeyField)) continue;
      $this->unsetProperty($k);
    }

    $this->setProperty('deleted', 1);
    $this->setProperty('deletedon', time());
    $this->setProperty('deletedby', $this->modx->getLoginUserID());

    return !$this->hasErrors();
  }

}

return 'modBoilerplateItemDeleteProcessor';
