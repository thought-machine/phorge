<?php

final class DifferentialReviewersSearchEngineAttachment
  extends PhabricatorSearchEngineAttachment {

  public function getAttachmentName() {
    return pht('Differential Reviewers');
  }

  public function getAttachmentDescription() {
    return pht(
      'Get the reviewers for each revision, and which of them you can '.
      'accept as.');
  }

  public function willLoadAttachmentData($query, $spec) {
    $query
      ->needReviewers(true)
      ->needReviewerAuthority(true)
      ->needActiveDiffs(true);
  }

  public function getAttachmentForObject($object, $data, $spec) {
    $reviewers = $object->getReviewers();

    // Find the reviewers the viewer can pass to an "accept" transaction.
    $accept_phids = id(new DifferentialRevisionAcceptTransaction())
      ->getAcceptableReviewerPHIDs($this->getViewer(), $object);
    $accept_phids = array_fuse($accept_phids);

    $status_blocking = DifferentialReviewerStatus::STATUS_BLOCKING;

    $list = array();
    foreach ($reviewers as $reviewer) {
      $status = $reviewer->getReviewerStatus();
      $is_blocking = ($status == $status_blocking);

      $list[] = array(
        'reviewerPHID' => $reviewer->getReviewerPHID(),
        'status' => $status,
        'isBlocking' => $is_blocking,
        'actorPHID' => $reviewer->getLastActorPHID(),
        'canAcceptAs' => isset($accept_phids[$reviewer->getReviewerPHID()]),
      );
    }

    return array(
      'reviewers' => $list,
    );
  }

}
