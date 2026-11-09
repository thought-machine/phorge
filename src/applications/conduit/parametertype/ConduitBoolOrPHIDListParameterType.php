<?php

final class ConduitBoolOrPHIDListParameterType
  extends ConduitParameterType {

  protected function getParameterValue(array $request, $key, $strict) {
    $value = parent::getParameterValue($request, $key, $strict);

    if (!is_array($value)) {
      return $this->parseBoolValue($request, $key, $value, $strict);
    }

    if ($value && (array_keys($value) !== range(0, count($value) - 1))) {
      $this->raiseValidationException(
        $request,
        $key,
        pht('Expected a list, but value is an object.'));
    }

    foreach ($value as $idx => $item) {
      $value[$idx] = $this->parseStringValue(
        $request,
        $key.'['.$idx.']',
        $item,
        $strict);
    }

    return $value;
  }

  protected function getParameterTypeName() {
    return 'bool|list<phid>';
  }

  protected function getParameterFormatDescriptions() {
    return array(
      pht('A boolean.'),
      pht('List of PHIDs.'),
    );
  }

  protected function getParameterExamples() {
    return array(
      'true',
      '["PHID-WXYZ-1111", "PHID-WXYZ-2222"]',
    );
  }

}
