<?php

final class ConduitBoolOrPHIDListParameterTypeTestCase
  extends PhabricatorTestCase {

  public function testBoolValues() {
    $this->assertEqual(true, $this->readValue(true));
    $this->assertEqual(false, $this->readValue(false));

    // Non-strict reads accept boolean strings.
    $this->assertEqual(true, $this->readValue('true', false));
    $this->assertEqual(false, $this->readValue('0', false));
  }

  public function testPHIDListValues() {
    $phids = array('PHID-USER-1111', 'PHID-PROJ-2222');
    $this->assertEqual($phids, $this->readValue($phids));

    // An empty list is accepted here; rejecting it is up to the caller.
    $this->assertEqual(array(), $this->readValue(array()));
  }

  public function testInvalidValues() {
    $invalid = array(
      'string' => 'PHID-USER-1111',
      'integer' => 1,
      'non-string list item' => array('PHID-USER-1111', 2),
      'nested list' => array(array('PHID-USER-1111')),
      'object' => array('a' => 'PHID-USER-1111'),
      'sparse list' => array(1 => 'PHID-USER-1111'),
    );

    foreach ($invalid as $name => $value) {
      $caught = null;
      try {
        $this->readValue($value);
      } catch (Exception $ex) {
        $caught = $ex;
      }

      $this->assertTrue(
        ($caught instanceof Exception),
        pht('Expected value "%s" to be rejected.', $name));
    }
  }

  private function readValue($value, $strict = true) {
    $type = new ConduitBoolOrPHIDListParameterType();
    return $type->getValue(array('key' => $value), 'key', $strict);
  }

}
