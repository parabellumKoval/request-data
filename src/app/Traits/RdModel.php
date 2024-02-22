<?php

namespace Rd\app\Traits;

trait RdModel {
  
  public $rd_fields = null;

  /**
   * getFields
   *
   * @param  mixed $type
   * @return void
   */
  public static function getFields($type = 'fields') {
    $fields = $this->rd_fields;
    
    if(!$fields)
      throw new \Exception('Please set fields in backpack.store.order config');
    else
      return $fields;
  }

  /** 
   *  Get validation rules from fields array
   * @param Array|String $fields
   * @return Array
  */
  public static function getRules($fields = null, $type = 'fields') {
    $node = $fields? $fields: $this->rd_fields;

    $rules = [];
    
    if(is_string($node)) {
      return $node;
    }

    if(is_array($node)) {
      
      foreach($node as $field => $value) {
        if(in_array($field, ['store_in']))
          continue;
        
        $selfRules = static::getRules($value);

        if(is_array($selfRules))
          foreach($selfRules as $k => $v) {
            if($k === 'rules') {
              $rules[$field] = $v;
            }else {
              $name = implode('.', [$field, $k]);
              $rules[$name] = $v;
            }
          }
        else
          $rules[$field] = $selfRules;
      }

    }

    return $rules;
  }
  
  /**
   * getFieldKeys
   *
   * @param  mixed $type
   * @return void
   */
  public static function getFieldKeys($type = 'fields') {
    $keys = array_keys($this->rd_fields);
    $keys = array_map(function($item) {
      return preg_replace('/[\*\.]/u', '', $item);
    }, $keys);

    return $keys;
  }
}