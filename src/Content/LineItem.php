<?php
declare(strict_types=1);

namespace ceLTIc\LTI\Content;

use ceLTIc\LTI\SubmissionReview;

/**
 * Class to represent a line-item object
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class LineItem
{

    /**
     * Label of line-item.
     *
     * @var string|null $label
     */
    private ?string $label = null;

    /**
     * Maximum score of line-item.
     *
     * @var int|null $scoreMaximum
     */
    private ?int $scoreMaximum = null;

    /**
     * Resource ID associated with line-item.
     *
     * @var string|null $resourceId
     */
    private ?string $resourceId = null;

    /**
     * Tag of line-item.
     *
     * @var string|null $tag
     */
    private ?string $tag = null;

    /**
     * Submission review.
     *
     * @var SubmissionReview|null $submissionReview
     */
    private ?SubmissionReview $submissionReview = null;

    /**
     * Class constructor.
     *
     * @param string                $label             Label
     * @param int|float             $scoreMaximum      Maximum score
     * @param string|null           $resourceId        Resource ID (optional)
     * @param string|null           $tag               Tag (optional)
     * @param SubmissionReview|null $submissionReview  Submission Review (optional)
     */
    function __construct(string $label, int|float $scoreMaximum, ?string $resourceId = null, ?string $tag = null,
        ?SubmissionReview $submissionReview = null)
    {
        $this->label = $label;
        $this->scoreMaximum = $scoreMaximum;
        $this->resourceId = $resourceId;
        $this->tag = $tag;
        $this->submissionReview = $submissionReview;
    }

    /**
     * Generate the JSON-LD object representation of the line-item.
     *
     * @return object  JSON object
     */
    public function toJsonldObject(): object
    {
        $lineItem = new \stdClass();

        $lineItem->{'@type'} = 'LineItem';
        $lineItem->label = $this->label;
        $lineItem->reportingMethod = 'http://purl.imsglobal.org/ctx/lis/v2p1/Result#normalScore';
        if (!empty($this->resourceId)) {
            $lineItem->assignedActivity = (object) ['activityId' => $this->resourceId];
        }
        $lineItem->scoreConstraints = (object) [
                '@type' => 'NumericLimits',
                'normalMaximum' => $this->scoreMaximum
        ];
        if (!empty($this->submissionReview)) {
            $lineItem->submissionReview = $this->submissionReview->toJsonObject();
        }

        return $lineItem;
    }

    /**
     * Generate the JSON object representation of the line-item.
     *
     * @return object  JSON object
     */
    public function toJsonObject(): object
    {
        $lineItem = new \stdClass();

        $lineItem->label = $this->label;
        $lineItem->scoreMaximum = $this->scoreMaximum;
        if (!empty($this->resourceId)) {
            $lineItem->resourceId = $this->resourceId;
        }
        if (!empty($this->tag)) {
            $lineItem->tag = $this->tag;
        }
        if (!empty($this->submissionReview)) {
            $lineItem->submissionReview = $this->submissionReview->toJsonObject();
        }

        return $lineItem;
    }

    /**
     * Generate a LineItem object from its JSON or JSON-LD representation.
     *
     * @param object $item  A JSON or JSON-LD object representing a content-item
     *
     * @return LineItem|null  The LineItem object
     */
    public static function fromJsonObject(object $item): ?LineItem
    {
        $obj = null;
        $label = null;
        $reportingMethod = null;
        $scoreMaximum = null;
        $activityId = null;
        $tag = null;
        $submissionReview = null;
        foreach (get_object_vars($item) as $name => $value) {
            switch ($name) {
                case 'label':
                    $label = $item->label;
                    break;
                case 'reportingMethod':
                    $reportingMethod = $item->reportingMethod;
                    break;
                case 'scoreConstraints':
                    $scoreConstraints = $item->scoreConstraints;
                    break;
                case 'scoreMaximum':
                    $scoreMaximum = $item->scoreMaximum;
                    break;
                case 'assignedActivity':
                    if (isset($item->assignedActivity->activityId)) {
                        $activityId = $item->assignedActivity->activityId;
                    }
                    break;
                case 'resourceId':
                    $activityId = $item->resourceId;
                    break;
                case 'tag':
                    $tag = $item->tag;
                    break;
                case 'submissionReview':
                    $submissionReview = SubmissionReview::fromJsonObject($item->submissionReview);
                    break;
            }
        }
        if (is_null($scoreMaximum) && $label && $reportingMethod && $scoreConstraints) {
            foreach (get_object_vars($scoreConstraints) as $name => $value) {
                $method = str_replace('Maximum', 'Score', $name);
                if (str_ends_with($reportingMethod, $method)) {
                    $scoreMaximum = $value;
                    break;
                }
            }
        }
        if (!is_null($scoreMaximum)) {
            $obj = new LineItem($label, $scoreMaximum, $activityId, $tag, $submissionReview);
        }

        return $obj;
    }

}
