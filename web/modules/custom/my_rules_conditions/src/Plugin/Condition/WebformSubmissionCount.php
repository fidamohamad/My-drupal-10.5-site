<?php

namespace Drupal\my_rules_conditions\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a 'Webform Submission Count' condition.
 *
 * @Condition(
 * id = "rules_webform_submission_count",
 * label = @Translation("Webform submission count"),
 * category = @Translation("Webform"),
 * context_definitions = {
 * "webform_submission" = @ContextDefinition(
 * "entity:webform_submission",
 * label = @Translation("Webform Submission"),
 * description = @Translation("The webform submission to check.")
 * ),
 * "email_field_name" = @ContextDefinition(
 * "string",
 * label = @Translation("Email field machine name"),
 * description = @Translation("The machine name of the email field on the webform."),
 * assignment_restriction = "input"
 * ),
 * "count" = @ContextDefinition(
 * "integer",
 * label = @Translation("Submission count"),
 * description = @Translation("The condition is TRUE if the number of submissions is greater than this number."),
 * assignment_restriction = "input",
 * default_value = 1
 * ),
 * }
 * )
 */
class WebformSubmissionCount extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $webform_submission = $this->getContextValue('webform_submission');
    $email_field = $this->getContextValue('email');
    $count_threshold = $this->getContextValue('count');

    // Get the email address the user typed into the form.
    $submitted_email = $webform_submission->getElementData($email_field);

    if (empty($submitted_email)) {
      return FALSE;
    }

    // Search the database for all submissions on this webform with the same email.
    $query = \Drupal::entityQuery('webform_submission')
      ->condition('webform_id', $webform_submission->getWebform()->id())
      ->condition('data.' . $email_field, $submitted_email)
      ->accessCheck(FALSE);

    $submission_count = $query->count()->execute();

    // Check if the actual count is greater than the specified threshold.
    return $submission_count > $count_threshold;
  }

}