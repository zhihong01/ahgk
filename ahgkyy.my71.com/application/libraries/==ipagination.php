<?php

/*
  author:vimal peramangalath
  email:vimalpt@gmail.com
 */

class ipagination {

    protected $TotalPages;
    protected $TotalRows;
    protected $RowPerPage;
    protected $CurrentPage;
    protected $Url;
    protected $Rewrites; //伪静态
    protected $Lang = array();

    function initialize($url = "#", $total_rows = 0, $rowsPerPage = 50, $rewrites = 1) {
        if ($rewrites) {
            $this->Url = preg_replace('#/page-\d+/#i', '/', $url);
        } else {
            $this->Url = preg_replace('/(&page=\d+)/i', '', $url);
        }
        $this->TotalRows = $total_rows;
        $this->RowPerPage = $rowsPerPage;
        $this->Rewrites = $rewrites;
        $this->getTotalPages();
    }

    function displayPageLinks() {

        $density = $this->findDensity(2);

        $PageLInks = "<span class='currentpage'>" . $this->CurrentPage . " / " . $this->TotalPages . "</span>";

        if ($density["first_page"])
            if ($this->Rewrites == 1) {//伪静态判断
                $PageLInks.="<a href='" . $this->Url . "page-" . $density["first_page"] . "/' ><span class='page'>首页</span></a> <span>....</span>";
            } else {
                $PageLInks.="<a href='" . $this->Url . "&page=" . $density["first_page"] . "' ><span class='page'>首页</span></a> <span>....</span>";
            }

        for ($i = (int) $density["start_page"]; $i <= (int) $density["end_page"]; $i++) {
            if ($this->CurrentPage == $i)
                $PageLInks.="<span class='currentpage'>" . $i . "</span>";
            else

            if ($this->Rewrites == 1) {//伪静态判断
                $PageLInks.="<a href='" . $this->Url . "page-" . $i . "/'><span class='page'>" . $i . "</span></a>";
            } else {
                $PageLInks.="<a href='" . $this->Url . "&page=" . $i . "'><span class='page'>" . $i . "</span></a>";
            }
        }

        if ($density["last_page"])
            if ($this->Rewrites == 1) {//伪静态判断
                $PageLInks.="<span>....</span> <a href='" . $this->Url . "page-" . $density["last_page"] . "/'><span class='page'>尾页</span></a>";
            } else {
                $PageLInks.="<span>....</span> <a href='" . $this->Url . "&page=" . $density["last_page"] . "'><span class='page'>尾页</span></a>";
            }

        return $PageLInks;
    }

    function getCurrentStartRecordNo($pageNo = 1) {

        if ((int) $pageNo < 1
        )
            $pageNo = 1;
        if ((int) $pageNo > $this->TotalPages
        )
            $pageNo = $this->TotalPages;
        $this->CurrentPage = $pageNo; //assigning for display links.
        return (int) ($this->CurrentPage * $this->RowPerPage) - (int) ( $this->RowPerPage);
    }

    function getTotalPages() {
        $this->TotalPages = (int) ceil($this->TotalRows / $this->RowPerPage);
    }

    function findDensity($densityFactor = 5) {
        $start = 1;
        $first = $start;
        $last = $this->TotalPages;
        $cur = $this->CurrentPage;
        $end = $last;

        ((int) ($cur + intval($densityFactor)) >= $end) ? $end = $last : $end = (int) $cur + $densityFactor;
        ((int) ($cur - intval($densityFactor)) <= (int) $start) ? $start = $first : $start = (int) $cur - $densityFactor;

        ($first == $start) ? $first = (boolean) false : 0;
        ($end == $last) ? $last = (boolean) false : 0;

        return array("first_page" => $first, "last_page" => $last, "start_page" => $start, "end_page" => $end);
    }

}

?>