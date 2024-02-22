<?php

namespace Rd\app\Traits;

use Illuminate\Support\Facades\Validator;

trait RdController {
  
  public $rd_model = null;
  /**
   * validateData
   *
   * @param  array $data - Data from the order request
   * @return void
   */
  public function validateData($request) {
    $data = $request->only($this->rd_model::getFieldKeys());

    // Apply validation rules to data
    $validator = Validator::make($data, $this->rd_model::getRules());

    if ($validator->fails()) {
      $errors = $validator->errors()->toArray();
      $errors_array = [];

      foreach($errors as $key => $error){
        $this->assignArrayByPath($errors_array, $key, $error);
      }

      throw new \Exception('Data Validation Error', 403, null, $errors_array);
    }

    return $data;
  }


  /**
   * setRequestFields
   * 
   * Automatycly setting all fields form request 
   * using structure from the config("backpack.store.order.fields").
   * 
   * 
   * @param  Backpack\Store\app\Models\Order $model - new Order model
   * @param  array $data - Order request data
   * @return Backpack\Store\app\Models\Order $model
   */
  protected function setRequestFields($model, array $data) {

    foreach($data as $field_name => $field_value){
      // Getting fields structure and rules from config
      $config_fields = $model::getFields();
      $field = $config_fields[$field_name] ?? $config_fields[$field_name.'.*'];
      
      // Skipping if filed is hidden
      if(isset($field['hidden']) && $field['hidden'])
        continue;

      // If JSON field 
      if(isset($field['store_in'])) {
        $field_old_value = $model->{$field['store_in']};
        $field_old_value[$field_name] = $field_value;
        $model->{$field['store_in']} = $field_old_value;
      }
      // if regular field
      else {
        $model->{$field_name} = $field_value;
      }
    }

    return $model;
  }

  
  /**
   * assignArrayByPath
   *
   * @param  mixed $arr
   * @param  mixed $path
   * @param  mixed $value
   * @param  mixed $separator
   * @return void
   */
  private function assignArrayByPath(&$arr, $path, $value, $separator='.') {
    $keys = explode($separator, $path);

    foreach ($keys as $key) {
        $arr = &$arr[$key];
    }

    $arr = $value;
  }
}