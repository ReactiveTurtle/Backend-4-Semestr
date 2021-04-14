<?php


class Pagination
{
    private $allElementsCount;
    private $pageElementsCount;
    private $visiblePagesCount;
    public function __construct($allElementsCount, $pageElementsCount, $visiblePagesCount)
    {
        $this->allElementsCount = $allElementsCount;
        $this->pageElementsCount = $pageElementsCount;
        $this->visiblePagesCount = $visiblePagesCount;
    }

    public function getPagesCount()
    {
        return ceil($this->allElementsCount / $this->pageElementsCount);
    }

    public function getVisiblePageIndices($pageIndex)
    {
        $pagesCount = $this->getPagesCount();
        $visiblePagesCount = min($pagesCount, $this->visiblePagesCount);
        $indices = array($visiblePagesCount);

        $centerIndex = $pageIndex - 1;
        $ifStartIndexBias = min($centerIndex, ceil(($visiblePagesCount - 1) / 2));
        $ifEndIndexBias = min($pagesCount - 1 - $centerIndex, floor(($visiblePagesCount - 1) / 2));

        $leftPagesCount = $ifStartIndexBias + floor(($visiblePagesCount - 1) / 2) - $ifEndIndexBias;
        $rightPagesCount = $visiblePagesCount - 1 - $leftPagesCount;
        for ($i = 0; $i < $leftPagesCount; $i++) {
            $indices[$i] = $centerIndex - $leftPagesCount + $i;
        }
        $indices[$leftPagesCount] = $centerIndex;
        for ($i = 0; $i < $rightPagesCount; $i++) {
            $indices[$leftPagesCount + $i + 1] = $centerIndex + $i + 1;
            $indices[$i] = $centerIndex - $leftPagesCount + $i;
        }
        return $indices;
    }
}