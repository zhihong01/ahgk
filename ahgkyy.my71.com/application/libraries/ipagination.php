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

        $density = $this->findDensity(1);

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

        if ($density["last_page"]) {
            if ($this->Rewrites == 1) {//伪静态判断
                $PageLInks.="<span>....</span> <a href='" . $this->Url . "page-" . $density["last_page"] . "/'><span class='page'>尾页</span></a>";
            } else {
                $PageLInks.="<span>....</span> <a href='" . $this->Url . "&page=" . $density["last_page"] . "'><span class='page'>尾页</span></a>";
            }
        }

        if ($this->Rewrites == 1) {//伪静态判断
            if ($this->CurrentPage == 1) {
                $PageLInks = '<a href="' . $this->Url . "page-" . ($this->CurrentPage + 1) . '/"><span class="page">下一页</span></a>' . $PageLInks;
            } else if ($this->TotalPages == $this->CurrentPage) {
                $PageLInks = '<a href="' . $this->Url . "page-" . ($this->CurrentPage - 1) . '/"><span class="page">上一页</span></a>' . $PageLInks;
            } else {
                $PageLInks = '<a href="' . $this->Url . "page-" . ($this->CurrentPage - 1) . '/"><span class="page">上一页</span></a><a href="' . $this->Url . "page-" . ($this->CurrentPage + 1) . '/"><span class="page">下一页</span></a>' . $PageLInks;
            }
            $PageLInks.='<span class="currentpage direct">直达页面<input type="text" name="" id="redirect_page" value="" /></span><a class="determine" href="javascript:void(0);" onClick="redirectPageRewrite(document.URL,$(\'#redirect_page\').val(),' . $this->TotalPages . ');return false;"><span class="page">跳转</span></a>';
        } else {
            if ($this->CurrentPage == 1) {
                $PageLInks = '<a href="' . $this->Url . "&page=" . ($this->CurrentPage + 1) . '"><span class="page">下一页</span></a>' . $PageLInks;
            } else if ($this->TotalPages == $this->CurrentPage) {
                $PageLInks = '<a href="' . $this->Url . "&page=" . ($this->CurrentPage - 1) . '"><span class="page">上一页</span></a>' . $PageLInks;
            } else {
                $PageLInks = '<a href="' . $this->Url . "&page=" . ($this->CurrentPage - 1) . '"><span class="page">上一页</span></a><a href="' . $this->Url . "&page=" . ($this->CurrentPage + 1) . '"><span class="page">下一页</span></a>' . $PageLInks;
            }
            $PageLInks.='<span class="currentpage direct">直达页面<input type="text" name="" id="redirect_page" value="" /></span><a class="determine" href="javascript:void(0);" onClick="redirectPage(document.URL,$(\'#redirect_page\').val(),' . $this->TotalPages . ');return false;"><span class="page">跳转</span></a>';
        }
        $PageLInks.='<script type="text/javascript">//直达页（分页用 伪静态）
                function redirectPageRewrite(url, page, Total) {
                    var new_url;
                    if (page === undefined || page === "" || isNaN(parseInt(page)) || parseInt(page) < 1) {
                        page = 1;
                    }
                    if (parseInt(page) > parseInt(Total)) {
                        page = Total;
                    }
                    if (url.match(/([a-z0-9]+)\-[\d]+\.html/) !== null) {
                        new_url = url + "?page=" + parseInt(page);
                    } else {
                        if (url.match(/page-[\d]+/) !== null) {
                            new_url = url.replace(/page-[\d]+/, "page-" + parseInt(page));
                        } else if (/\//) {
                            new_url = url + "page-" + parseInt(page) + "/";
                        } else {
                            new_url = url + "/page-" + parseInt(page) + "/";
                        }
                    }
                    parent.window.location.href = new_url;
                }

                //非伪静态
                function redirectPage(url, page, Total) {
                    var new_url;
                    if (page === undefined || page === "" || isNaN(parseInt(page)) || parseInt(page) < 1) {
                        page = 1;
                    }
                    if (parseInt(page) > parseInt(Total)) {
                        page = Total;
                    }
                    if (url.match(/([a-z0-9]+)\-[\d]+\.html/) !== null) {
                        new_url = url + "?page=" + parseInt(page);
                    } else {
                        if (url.match(/page=[\d]+/) !== null) {
                            new_url = url.replace(/page=[\d]+/, "page=" + parseInt(page));
                        } else {
                            new_url = url + "&page=" + parseInt(page);
                        }
                    }
                    window.location.href = new_url;
                }</script>';
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