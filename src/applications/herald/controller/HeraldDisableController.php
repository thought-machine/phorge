<?php

final class HeraldDisableController extends HeraldController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('id');
    $action = $request->getURIData('action');

    $rule = id(new HeraldRuleQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$rule) {
      return new Aphront404Response();
    }

    if ($rule->isGlobalRule()) {
      $this->requireApplicationCapability(
        HeraldManageGlobalRulesCapability::CAPABILITY);
    }

    $view_uri = '/'.$rule->getMonogram();

    $is_disable = ($action === 'disable');

    $e_reason = null;
    $errors  = array();

    if ($request->isFormPost()) {
      $reason = trim($request->getStr('reason', ''));
      if (phutil_nonempty_string($reason)) {
        $transactions = array();

        $transactions[] = id(new HeraldRuleTransaction())
        ->setTransactionType(HeraldRuleDisableTransaction::TRANSACTIONTYPE)
        ->setNewValue($is_disable);

        $transactions[] = id(new HeraldRuleTransaction())
        ->setTransactionType(HeraldRuleDisableTransaction::TRANSACTIONTYPE)
        ->setNewValue($reason);

        id(new HeraldRuleEditor())
        ->setActor($viewer)
        ->setContinueOnNoEffect(true)
        ->setContentSourceFromRequest($request)
        ->applyTransactions($rule, $transactions);

        return id(new AphrontRedirectResponse())->setURI($view_uri);
      }
      $e_reason = pht('Required');
      $errors[] = pht('A reason is required.');
    }

    if ($is_disable) {
      $title = pht('Really disable this rule?');
      $body = pht('This rule will no longer activate.');
      $button = pht('Disable Rule');
      $verb = pht("disabling");
    } else {
      $title = pht('Really enable this rule?');
      $body = pht('This rule will become active again.');
      $button = pht('Enable Rule');
      $verb = pht("enabling");
    }

    if ($errors) {
      $errors = id(new PHUIInfoView())
      ->setErrors($errors);
    }

    $form = id(new PHUIFormLayoutView())
      ->setUser($viewer)
      ->setFullWidth(true)
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Reason for %s this rule', $verb))
          ->setName('reason')
          ->setDisableAutocomplete(true)
          ->setAutofocus(true)
          ->setError($e_reason));

    $dialog = id(new AphrontDialogView())
      ->setUser($viewer)
      ->setTitle($title)
      ->appendChild($body)
      ->appendChild($errors)
      ->appendChild($form)
      ->addSubmitButton($button)
      ->addCancelButton($view_uri);

    return id(new AphrontDialogResponse())->setDialog($dialog);
  }
}
