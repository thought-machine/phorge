<?php

final class PhabricatorInlineCommentContentStateTestCase
  extends PhutilTestCase {

  /**
   * Remarkup metadata (such as "attachedFilePHIDs", which records files
   * uploaded into the editor) must survive the full trip from the inline
   * editor request, into storage, and back out of storage.
   *
   * If it is dropped, files uploaded into inline comments are only
   * referenced (never attached) when the comment is published, so users who
   * can see the revision can not see the file.
   */
  public function testContentMetadataRoundTrip() {
    $cases = array(
      'attached files' => array(
        'request' => array(
          'text' => '{F123}',
          'metadata' => '{"attachedFilePHIDs":["PHID-FILE-aaaa"]}',
        ),
        'expect' => array(
          'attachedFilePHIDs' => array('PHID-FILE-aaaa'),
        ),
      ),
      'empty object' => array(
        'request' => array(
          'text' => 'x',
          'metadata' => '{}',
        ),
        'expect' => array(),
      ),
      'missing' => array(
        'request' => array(
          'text' => 'x',
        ),
        'expect' => array(),
      ),
      'malformed' => array(
        'request' => array(
          'text' => 'x',
          'metadata' => '{not json',
        ),
        'expect' => array(),
      ),
      'not an object' => array(
        'request' => array(
          'text' => 'x',
          'metadata' => '"string"',
        ),
        'expect' => array(),
      ),
    );

    foreach ($cases as $name => $tc) {
      $request = new AphrontRequest('example.com', '/');
      $request->setRequestData($tc['request']);

      $state = id(new PhabricatorDiffInlineCommentContentState())
        ->readFromRequest($request);

      $this->assertEqual(
        $tc['expect'],
        $state->getContentMetadata(),
        pht('Metadata read from request: %s', $name));

      $stored = id(new PhabricatorDiffInlineCommentContentState())
        ->readStorageMap($state->newStorageMap());

      $this->assertEqual(
        $tc['expect'],
        $stored->getContentMetadata(),
        pht('Metadata after storage round trip: %s', $name));
    }
  }

}
