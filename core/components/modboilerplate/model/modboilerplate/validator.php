<?php

class modBoilerplateValidator {

  function prepareValue ($type, $value) {
    $old_value = $value;

    switch ($type) {
      case 'timestamp':
        if (!empty($value)) {
          $value = strtotime($value);
          if (false === $value) { $value = null; }
        }
        break;
      case 'not_zero':
        $value = self::prepareValue ('float', $value);
        if ($value === 0) { $value = null; }
        break;
      case 'float':
        $value = preg_replace('/[,]+/', '.', $value);
        $value = preg_replace('/[^-0-9\.]+/', '', $value);
        $value = floatval($value);
        break;
      case 'number':
        $value = preg_replace('/[^-0-9]+/', '', $value);
        break;
      case 'unsigned':
        $value = self::prepareValue ('float', $value);
        if (is_numeric($value) && $value < 0) {
          $value = $value * -1;
        }
        if (empty($value)) {
          $value = null;
        }
        break;
      case 'required':
        //        $value = trim($value);
        if (empty($value) || trim($value) == '') {$value = null;}
        break;
      case 'email':
        $value = preg_match('/^[^@а-яА-ЯёЁ]+@[^@а-яА-ЯёЁ]+(?<!\.)\.[^\.а-яА-ЯёЁ]{2,}$/m', $value)
          ? trim($value)
          : null;
        break;
      case 'name':
        // Transforms string from "nikolaj - coster--Waldau jr." to "Nikolaj Coster-Waldau Jr."
        $tmp = preg_replace(array('/[^-a-zа-яёЁ\s\.]/iu', '/\s+/', '/\-+/', '/\.+/'), array('', ' ', '-', '.'), $value);
        $tmp = preg_split('/\s/', $tmp, -1, PREG_SPLIT_NO_EMPTY);
        $tmp = array_map(array($tmp, 'ucfirst'), $tmp);
        $value = preg_replace('/\s+/', ' ', implode(' ', $tmp));
        if (empty($value)) {$value = null;}
        break;
      case 'phone':
        //        $value = substr(preg_replace('/[^-+0-9]/iu','',$value),0,15);
        $value = preg_replace('/\D+/', '', $value);
        $len = strlen($value);
        switch ($len) {
          case 10:
            $value = '8'. $value;
            break;
          case 11:
            $first = ($value{0} == '7') ? 8 : $value{0};
            $value = $first . substr($value, 1);
            break;
        }
        break;
      case 'domain':
        $value = preg_replace('/^https?:\/\//i', '', $value);
        $value = preg_replace('/^www\./i', '', $value);
        $value = preg_replace('/\/.*/i', '', $value);
        $value = trim($value);
        if (!strpos($value, '.')) {$value = null;}
        break;
      case 'url':
        $value = trim($value);
        if (!preg_match(
          '%^(?:(?:https?)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu',
          $value
        )) {
          $value = null;
        }
        break;
      default:
        return $value;
    }

    return $value;
  }

}