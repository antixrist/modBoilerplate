<?php
include_once 'setting.inc.php';
$files = scandir(dirname(__FILE__));
foreach ($files as $file) {
  if (is_file($file) && preg_match('/\.inc\.php$/i', $file)) {
    @include_once($file);
  }
}

$_lang['modboilerplate'] = 'modBoilerplate';
$_lang['modboilerplate_menu_desc'] = 'Пример расширения для разработки.';
$_lang['modboilerplate_intro_msg'] = 'Вы можете выделять сразу несколько предметов при помощи Shift или Ctrl.';

$_lang['modboilerplate_items'] = 'Предметы';
$_lang['modboilerplate_item_id'] = 'Id';
$_lang['modboilerplate_item_name'] = 'Название';
$_lang['modboilerplate_item_description'] = 'Описание';
$_lang['modboilerplate_item_active'] = 'Активно';

$_lang['modboilerplate_item_create'] = 'Создать предмет';
$_lang['modboilerplate_item_update'] = 'Изменить Предмет';
$_lang['modboilerplate_item_enable'] = 'Включить Предмет';
$_lang['modboilerplate_items_enable'] = 'Включить Предметы';
$_lang['modboilerplate_item_disable'] = 'Отключить Предмет';
$_lang['modboilerplate_items_disable'] = 'Отключить Предметы';
$_lang['modboilerplate_item_remove'] = 'Удалить Предмет';
$_lang['modboilerplate_items_remove'] = 'Удалить Предметы';
$_lang['modboilerplate_item_remove_confirm'] = 'Вы уверены, что хотите удалить этот Предмет?';
$_lang['modboilerplate_items_remove_confirm'] = 'Вы уверены, что хотите удалить эти Предметы?';
$_lang['modboilerplate_item_active'] = 'Включено';

$_lang['modboilerplate_item_err_name'] = 'Вы должны указать имя Предмета.';
$_lang['modboilerplate_item_err_ae'] = 'Предмет с таким именем уже существует.';
$_lang['modboilerplate_item_err_nf'] = 'Предмет не найден.';
$_lang['modboilerplate_item_err_ns'] = 'Предмет не указан.';
$_lang['modboilerplate_item_err_remove'] = 'Ошибка при удалении Предмета.';
$_lang['modboilerplate_item_err_save'] = 'Ошибка при сохранении Предмета.';

$_lang['modboilerplate_grid_search'] = 'Поиск';
$_lang['modboilerplate_grid_actions'] = 'Действия';