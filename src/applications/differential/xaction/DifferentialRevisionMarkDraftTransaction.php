<?php

final class DifferentialRevisionMarkDraftTransaction
  extends DifferentialRevisionActionTransaction {

  const TRANSACTIONTYPE = 'differential.revision.draft';
  const ACTIONKEY = 'mark-draft';

  protected function getRevisionActionLabel(
    DifferentialRevision $revision,
    PhabricatorUser $viewer) {
    return pht('Mark as Draft');
  }

  protected function getRevisionActionDescription(
    DifferentialRevision $revision,
    PhabricatorUser $viewer) {
    return pht(
      'This revision will be returned to the draft state and held there '.
      'until you submit it for review again.');
  }

  public function getIcon() {
    return 'fa-pencil';
  }

  public function getColor() {
    return 'grey';
  }

  protected function getRevisionActionOrder() {
    return 250;
  }

  public function getActionName() {
    return pht('Marked as Draft');
  }

  public function getCommandKeyword() {
    return 'draft';
  }

  public function getCommandSummary() {
    return pht('Return a revision to the draft state.');
  }

  public function generateOldValue($object) {
    return $object->isDraft();
  }

  public function applyInternalEffects($object, $value) {
    // Hold the revision as a draft so builds finishing does not immediately
    // publish it again. The author can use "Request Review" to publish it.
    $object
      ->setModernRevisionStatus(DifferentialRevisionStatus::DRAFT)
      ->setShouldBroadcast(false)
      ->setHoldAsDraft(true);
  }

  protected function validateAction($object, PhabricatorUser $viewer) {
    if ($object->isDraft()) {
      throw new Exception(
        pht(
          'You can not mark this revision as a draft because it is already '.
          'a draft.'));
    }

    if ($object->isClosed()) {
      throw new Exception(
        pht(
          'You can not mark this revision as a draft because it has '.
          'already been closed. Only open revisions can be returned to '.
          'draft.'));
    }

    if (!$this->isViewerRevisionAuthor($object, $viewer)) {
      throw new Exception(
        pht(
          'You can not mark this revision as a draft because you do not '.
          'own it. Only the author of a revision can mark it as a draft.'));
    }
  }

  public function getTitle() {
    return pht(
      '%s marked this revision as a draft.',
      $this->renderAuthor());
  }

  public function getTitleForFeed() {
    return pht(
      '%s marked %s as a draft.',
      $this->renderAuthor(),
      $this->renderObject());
  }

  public function getTransactionTypeForConduit($xaction) {
    return 'mark-draft';
  }

  public function getFieldValuesForConduit($object, $data) {
    return array();
  }

}
