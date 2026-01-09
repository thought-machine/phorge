<?php

final class DrydockOperationSearchConduitAPIMethod
  extends PhabricatorSearchEngineAPIMethod {

  public function getAPIMethodName() {
    return 'drydock.operation.search';
  }

  public function newSearchEngine() {
    return new DrydockRepositoryOperationSearchEngine();
  }

  public function getMethodSummary() {
    return pht('Retrieve information about Drydock operations.');
  }

}
