<?php

namespace Dewdrop\Fields\Template;

use DateInterval;
use DateTimeImmutable;
use Dewdrop\Fields\FieldInterface;

class FilterToDateIntervalByDefault
{
    /**
     * @var DateInterval
     */
    private $dateInterval;

    /**
     * @var DateTimeImmutable
     */
    private $startDate;

    /**
     * FilterToDateIntervalByDefault constructor.
     * @param string $intervalString
     * @param DateTimeImmutable|null $startDate
     */
    public function __construct($intervalString, DateTimeImmutable $startDate = null)
    {
        $this->dateInterval = DateInterval::createFromDateString($intervalString);
        $this->startDate    = ($startDate ?: new DateTimeImmutable());
    }

    public function __invoke(FieldInterface $field)
    {
        $field->assignHelperCallback(
            'SelectFilter.DefaultVars',
            function () {
                $end = $this->startDate->add($this->dateInterval);

                // Swap dates if end if before start
                if ($end < $this->startDate) {
                    return [
                        'comp'  => 'on-or-between',
                        'end'   => $this->startDate->format('Y-m-d'),
                        'start' => $end->format('Y-m-d')
                    ];
                } else {
                    return [
                        'comp'  => 'on-or-between',
                        'start' => $this->startDate->format('Y-m-d'),
                        'end'   => $end->format('Y-m-d')
                    ];
                }
            }
        );
    }
}
