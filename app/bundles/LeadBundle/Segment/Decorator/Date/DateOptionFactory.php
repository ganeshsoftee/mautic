<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Segment\Decorator\Date;

use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\LeadBundle\Segment\Decorator\Date\Day\DateDayToday;
use Mautic\LeadBundle\Segment\Decorator\Date\Day\DateDayTomorrow;
use Mautic\LeadBundle\Segment\Decorator\Date\Day\DateDayYesterday;
use Mautic\LeadBundle\Segment\Decorator\Date\Month\DateMonthLast;
use Mautic\LeadBundle\Segment\Decorator\Date\Month\DateMonthNext;
use Mautic\LeadBundle\Segment\Decorator\Date\Month\DateMonthThis;
use Mautic\LeadBundle\Segment\Decorator\Date\Other\DateAnniversary;
use Mautic\LeadBundle\Segment\Decorator\Date\Other\DateDefault;
use Mautic\LeadBundle\Segment\Decorator\Date\Week\DateWeekLast;
use Mautic\LeadBundle\Segment\Decorator\Date\Week\DateWeekNext;
use Mautic\LeadBundle\Segment\Decorator\Date\Week\DateWeekThis;
use Mautic\LeadBundle\Segment\Decorator\Date\Year\DateYearLast;
use Mautic\LeadBundle\Segment\Decorator\Date\Year\DateYearNext;
use Mautic\LeadBundle\Segment\Decorator\Date\Year\DateYearThis;
use Mautic\LeadBundle\Segment\Decorator\DateDecorator;
use Mautic\LeadBundle\Segment\Decorator\FilterDecoratorInterface;
use Mautic\LeadBundle\Segment\LeadSegmentFilterCrate;
use Mautic\LeadBundle\Segment\RelativeDate;

class DateOptionFactory
{
    /**
     * @var DateDecorator
     */
    private $dateDecorator;

    /**
     * @var RelativeDate
     */
    private $relativeDate;

    public function __construct(
        DateDecorator $dateDecorator,
        RelativeDate $relativeDate
    ) {
        $this->dateDecorator = $dateDecorator;
        $this->relativeDate  = $relativeDate;
    }

    /**
     * @param LeadSegmentFilterCrate $leadSegmentFilterCrate
     *
     * @return FilterDecoratorInterface
     */
    public function getDateOption(LeadSegmentFilterCrate $leadSegmentFilterCrate)
    {
        $originalValue   = $leadSegmentFilterCrate->getFilter();
        $isTimestamp     = $this->isTimestamp($leadSegmentFilterCrate);
        $timeframe       = $this->getTimeFrame($leadSegmentFilterCrate);
        $requiresBetween = $this->requiresBetween($leadSegmentFilterCrate);
        $includeMidnigh  = $this->shouldIncludeMidnight($leadSegmentFilterCrate);

        $dtHelper = new DateTimeHelper('midnight today', null, 'local');

        switch ($timeframe) {
            case 'birthday':
            case 'anniversary':
                return new DateAnniversary($this->dateDecorator);
            case 'today':
                return new DateDayToday($this->dateDecorator, $dtHelper, $requiresBetween, $includeMidnigh, $isTimestamp);
            case 'tomorrow':
                return new DateDayTomorrow($this->dateDecorator, $dtHelper, $requiresBetween, $includeMidnigh, $isTimestamp);
            case 'yesterday':
                return new DateDayYesterday($this->dateDecorator, $dtHelper, $requiresBetween, $includeMidnigh, $isTimestamp);
            case 'week_last':
                return new DateWeekLast($this->dateDecorator, $dtHelper, $requiresBetween, $includeMidnigh, $isTimestamp);
            case 'week_next':
                return new DateWeekNext($this->dateDecorator, $dtHelper, $requiresBetween, $includeMidnigh, $isTimestamp);
            case 'week_this':
                return new DateWeekThis($this->dateDecorator, $dtHelper, $requiresBetween, $includeMidnigh, $isTimestamp);
            case 'month_last':
                return new DateMonthLast($this->dateDecorator, $dtHelper, $requiresBetween, $includeMidnigh, $isTimestamp);
            case 'month_next':
                return new DateMonthNext($this->dateDecorator, $dtHelper, $requiresBetween, $includeMidnigh, $isTimestamp);
            case 'month_this':
                return new DateMonthThis($this->dateDecorator, $dtHelper, $requiresBetween, $includeMidnigh, $isTimestamp);
            case 'year_last':
                return new DateYearLast();
            case 'year_next':
                return new DateYearNext();
            case 'year_this':
                return new DateYearThis();
            default:
                return new DateDefault($this->dateDecorator, $originalValue);
        }
    }

    private function requiresBetween(LeadSegmentFilterCrate $leadSegmentFilterCrate)
    {
        return in_array($leadSegmentFilterCrate->getOperator(), ['=', '!='], true);
    }

    private function shouldIncludeMidnight(LeadSegmentFilterCrate $leadSegmentFilterCrate)
    {
        return in_array($leadSegmentFilterCrate->getOperator(), ['gt', 'lte'], true);
    }

    private function isTimestamp(LeadSegmentFilterCrate $leadSegmentFilterCrate)
    {
        return $leadSegmentFilterCrate->getType() === 'datetime';
    }

    /**
     * @param LeadSegmentFilterCrate $leadSegmentFilterCrate
     *
     * @return string
     */
    private function getTimeFrame(LeadSegmentFilterCrate $leadSegmentFilterCrate)
    {
        $relativeDateStrings = $this->relativeDate->getRelativeDateStrings();
        $key                 = array_search($leadSegmentFilterCrate->getFilter(), $relativeDateStrings, true);

        return str_replace('mautic.lead.list.', '', $key);
    }
}