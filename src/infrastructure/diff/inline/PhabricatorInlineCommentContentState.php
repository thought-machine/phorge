<?php

abstract class PhabricatorInlineCommentContentState
  extends Phobject {

  private $contentText = '';
  private $contentMetadata = array();

  public function setContentText($content_text) {
    $this->contentText = $content_text;
    return $this;
  }

  public function getContentText() {
    return $this->contentText;
  }

  public function setContentMetadata(array $content_metadata) {
    $this->contentMetadata = $content_metadata;
    return $this;
  }

  public function getContentMetadata() {
    return $this->contentMetadata;
  }

  public function isEmptyContentState() {
    return !strlen($this->getContentText());
  }

  public function writeStorageMap() {
    return array(
      'text' => $this->getContentText(),
      'metadata' => $this->getContentMetadata(),
    );
  }

  public function readStorageMap(array $map) {
    $text = (string)idx($map, 'text');
    $this->setContentText($text);

    $metadata = idx($map, 'metadata');
    $this->setContentMetadata(is_array($metadata) ? $metadata : array());

    return $this;
  }

  final public function readFromRequest(AphrontRequest $request) {
    $map = $this->newStorageMapFromRequest($request);
    return $this->readStorageMap($map);
  }

  protected function newStorageMapFromRequest(AphrontRequest $request) {
    $map = array();

    $map['text'] = (string)$request->getStr('text');

    // The remarkup control metadata is sent as a JSON string. It records
    // things like which uploaded files should be attached to the object.
    $metadata = array();
    $metadata_json = $request->getStr('metadata');
    if (phutil_nonempty_string($metadata_json)) {
      try {
        $decoded = phutil_json_decode($metadata_json);
        if (is_array($decoded)) {
          $metadata = $decoded;
        }
      } catch (PhutilJSONParserException $ex) {
        // Ignore malformed metadata.
      }
    }
    $map['metadata'] = $metadata;

    return $map;
  }

}
