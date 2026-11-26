<?php

final class PhabricatorOneSidedUnifiedDiffsSetting
  extends PhabricatorSelectSetting {

  const SETTINGKEY = 'diff-unified-one-sided';

  const VALUE_ONE_SIDED_UNIFIED_DISABLED = 'default';
  const VALUE_ONE_SIDED_UNIFIED_ENABLED = 'enabled';

  public function getSettingName() {
    return pht('Unified Diffs for One-Sided Changes');
  }

  protected function getSettingOrder() {
    return 150;
  }

  public function getSettingPanelKey() {
    return PhabricatorDiffPreferencesSettingsPanel::PANELKEY;
  }

  protected function getControlInstructions() {
    return pht(
      'Files which only add lines or only remove lines are shown as unified '.
      'diffs, even on large screens, since one side of a side-by-side diff '.
      'would be empty. Other files use the layout picked by "Show Unified '.
      'Diffs".');
  }

  public function getSettingDefaultValue() {
    return self::VALUE_ONE_SIDED_UNIFIED_DISABLED;
  }

  protected function getSelectOptions() {
    return array(
      self::VALUE_ONE_SIDED_UNIFIED_DISABLED => pht('Disabled'),
      self::VALUE_ONE_SIDED_UNIFIED_ENABLED => pht('Enabled'),
    );
  }


}
