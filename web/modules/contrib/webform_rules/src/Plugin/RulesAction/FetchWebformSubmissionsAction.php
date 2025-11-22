<?php

namespace Drupal\webform_rules\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'FetchWebformSubmissionsAction' action.
 *
 * @RulesAction(
 *  id = "fetch_webform_submissions_action",
 *  label = @Translation("Fetch webform submissions"),
 *  category = @Translation("Webform Rules"),
 * )
 */
class FetchWebformSubmissionsAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    // Insert code here.
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $access = $object->status->access('edit', $account, TRUE)
      ->andIf($object->access('update', $account, TRUE));

    return $return_as_object ? $access : $access->isAllowed();
  }

}
