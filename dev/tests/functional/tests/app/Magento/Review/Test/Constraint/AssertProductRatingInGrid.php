<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Fixture\Rating;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductRatingInGrid
 */
class AssertProductRatingInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Assert product Rating availability in product Rating grid
     *
     * @param RatingIndex $ratingIndex
     * @param Rating $productRating
     * @return void
     */
    public function processAssert(RatingIndex $ratingIndex, Rating $productRating)
    {
        $filter = ['rating_code' => $productRating->getRatingCode()];

        $ratingIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $ratingIndex->getRatingGrid()->isRowVisible($filter),
            "Product Rating " . $productRating->getRatingCode() . " is absent on product Rating grid."
        );
    }

    /**
     * Text success exist product Rating in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Rating is present in grid.';
    }
}
