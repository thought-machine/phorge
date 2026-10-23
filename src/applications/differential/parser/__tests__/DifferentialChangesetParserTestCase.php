<?php

final class DifferentialChangesetParserTestCase extends PhabricatorTestCase {

  public function testDiffChangesets() {
    $hunk = new DifferentialHunk();
    $hunk->setChanges("+a\n b\n-c\n");
    $hunk->setNewOffset(1);
    $hunk->setNewLen(2);
    $left = new DifferentialChangeset();
    $left->attachHunks(array($hunk));

    $tests = array(
      "+a\n b\n-c\n" => array(array(), array()),
      "+a\n x\n-c\n" => array(array(), array()),
      "+aa\n b\n-c\n" => array(array(1), array(11)),
      " b\n-c\n" => array(array(1), array()),
      "+a\n b\n c\n" => array(array(), array(13)),
      "+a\n x\n c\n" => array(array(), array(13)),
    );

    foreach ($tests as $changes => $expected) {
      $hunk = new DifferentialHunk();
      $hunk->setChanges($changes);
      $hunk->setNewOffset(11);
      $hunk->setNewLen(3);
      $right = new DifferentialChangeset();
      $right->attachHunks(array($hunk));

      $parser = new DifferentialChangesetParser();
      $parser->setOriginals($left, $right);
      $this->assertEqual($expected, $parser->diffOriginals(), $changes);
    }
  }

  public function testIsOneSidedChangeset() {
    $type_add = DifferentialChangeType::TYPE_ADD;
    $type_change = DifferentialChangeType::TYPE_CHANGE;
    $type_delete = DifferentialChangeType::TYPE_DELETE;
    $type_move = DifferentialChangeType::TYPE_MOVE_HERE;

    $tests = array(
      'added file' => array($type_add, array("+a\n+b\n"), true),
      'deleted file' => array($type_delete, array("-a\n-b\n"), true),
      'added binary file' => array($type_add, array(), true),
      'deleted binary file' => array($type_delete, array(), true),
      'only adds lines' => array($type_change, array(" a\n+b\n c\n"), true),
      'only removes lines' => array($type_change, array(" a\n-b\n c\n"), true),
      'adds and removes lines' => array(
        $type_change,
        array(" a\n-b\n+c\n"),
        false,
      ),
      'adds and removes lines in different hunks' => array(
        $type_change,
        array(" a\n+b\n", " c\n-d\n"),
        false,
      ),
      'modified binary file' => array($type_change, array(), false),
      'moved file with no changes' => array($type_move, array(), false),
      'moved file which only adds lines' => array(
        $type_move,
        array(" a\n+b\n"),
        true,
      ),
      'moved file which adds and removes lines' => array(
        $type_move,
        array(" a\n-b\n+c\n"),
        false,
      ),
      'only context lines' => array($type_change, array(" a\n b\n"), false),
      'adds a line with no newline at end of file' => array(
        $type_change,
        array(" a\n+b\n\\ No newline at end of file\n"),
        true,
      ),
      'adds a newline at end of file' => array(
        $type_change,
        array("-a\n\\ No newline at end of file\n+a\n"),
        false,
      ),
    );

    foreach ($tests as $name => $test) {
      list($change_type, $changes, $expected) = $test;

      $changeset = $this->newTestChangeset($change_type, $changes);

      $this->assertEqual(
        $expected,
        DifferentialChangesetParser::isOneSidedChangeset($changeset),
        $name);
    }
  }

  public function testNewRendererKey() {
    $always = PhabricatorUnifiedDiffsSetting::VALUE_ALWAYS_UNIFIED;
    $small = PhabricatorUnifiedDiffsSetting::VALUE_ON_SMALL_SCREENS;
    $on =
      PhabricatorOneSidedUnifiedDiffsSetting::VALUE_ONE_SIDED_UNIFIED_ENABLED;
    $off =
      PhabricatorOneSidedUnifiedDiffsSetting::VALUE_ONE_SIDED_UNIFIED_DISABLED;

    $type_change = DifferentialChangeType::TYPE_CHANGE;
    $one_sided = $this->newTestChangeset($type_change, array(" a\n+b\n"));
    $mixed = $this->newTestChangeset($type_change, array(" a\n-b\n+c\n"));

    // Each test is: "Show Unified Diffs" setting, "Unified Diffs for
    // One-Sided Changes" setting, changeset, renderer chosen in the view
    // state, renderer for the device, and the expected renderer.
    $tests = array(
      'disabled, one-sided, large screen' => array(
        $small,
        $off,
        $one_sided,
        null,
        '2up',
        '2up',
      ),
      'disabled, one-sided, small screen' => array(
        $small,
        $off,
        $one_sided,
        null,
        '1up',
        '1up',
      ),
      'enabled, one-sided, large screen' => array(
        $small,
        $on,
        $one_sided,
        null,
        '2up',
        '1up',
      ),
      'enabled, mixed, large screen' => array(
        $small,
        $on,
        $mixed,
        null,
        '2up',
        '2up',
      ),
      'enabled, mixed, small screen' => array(
        $small,
        $on,
        $mixed,
        null,
        '1up',
        '1up',
      ),
      'always unified, mixed, large screen' => array(
        $always,
        $off,
        $mixed,
        null,
        '2up',
        '1up',
      ),
      'always unified and enabled, mixed, large screen' => array(
        $always,
        $on,
        $mixed,
        null,
        '2up',
        '1up',
      ),
      'enabled, one-sided, side-by-side chosen' => array(
        $small,
        $on,
        $one_sided,
        '2up',
        '2up',
        '2up',
      ),
      'always unified, side-by-side chosen' => array(
        $always,
        $off,
        $mixed,
        '2up',
        '2up',
        '2up',
      ),
      'disabled, mixed, unified chosen' => array(
        $small,
        $off,
        $mixed,
        '1up',
        '2up',
        '1up',
      ),
    );

    foreach ($tests as $name => $test) {
      list(
        $unified,
        $one_sided_unified,
        $changeset,
        $renderer_key,
        $device_key,
        $expected) = $test;

      $preferences = array(
        PhabricatorUnifiedDiffsSetting::SETTINGKEY => $unified,
        PhabricatorOneSidedUnifiedDiffsSetting::SETTINGKEY =>
          $one_sided_unified,
      );

      $viewer = id(new PhabricatorUser())
        ->setPHID('PHID-USER-test')
        ->attachRawCacheData(
          array(
            PhabricatorUserPreferencesCacheType::KEY_PREFERENCES =>
              phutil_json_encode($preferences),
          ));

      $viewstate = id(new PhabricatorChangesetViewState())
        ->setRendererKey($renderer_key)
        ->setDefaultDeviceRendererKey($device_key);

      $parser = id(new DifferentialChangesetParser())
        ->setViewer($viewer)
        ->setViewState($viewstate)
        ->setChangeset($changeset);

      $this->assertEqual($expected, $parser->newRendererKey(), $name);
    }
  }

  private function newTestChangeset($change_type, array $changes) {
    $hunks = array();
    foreach ($changes as $change) {
      $hunk = new DifferentialHunk();
      $hunk->setChanges($change);
      $hunk->setOldOffset(1);
      $hunk->setNewOffset(1);
      $hunks[] = $hunk;
    }

    return id(new DifferentialChangeset())
      ->setChangeType($change_type)
      ->attachHunks($hunks);
  }

}
