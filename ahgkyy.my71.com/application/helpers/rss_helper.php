<?php

/**
 * rss操作类 
 * 
 * @author ciogao@gmail.com 
 */
define("TIME_ZONE", "Asia/Shanghai");
/**
 * Version string. 
 * */
define("FEEDCREATOR_VERSION", "Powered by iShang information industry Co.,Ltd.");

/**
 * A FeedItem is a part of a FeedCreator feed. 
 * 
 */
class FeedItem extends HtmlDescribable {

    /**
     * Mandatory attributes of an item. 
     */
    var $title, $description, $link;

    /**
     * Optional attributes of an item. 
     */
    var $author, $authorEmail, $image, $category, $comments, $guid, $source, $creator;

    /**
     * Publishing date of an item. May be in one of the following formats: 
     * 
     * RFC 822: 
     * "Mon, 20 Jan 03 18:05:41 +0400" 
     * "20 Jan 03 18:05:41 +0000" 
     * 
     * ISO 8601: 
     * "2003-01-20T18:05:41+04:00" 
     * 
     * Unix: 
     * 1043082341 
     */
    var $date;

    /**
     * Any additional elements to include as an assiciated array. All $key => $value pairs 
     * will be included unencoded in the feed item in the form 
     *     <$key>$value</$key> 
     * Again: No encoding will be used! This means you can invalidate or enhance the feed 
     * if $value contains markup. This may be abused to embed tags not implemented by
     * the FeedCreator class used. 
     */
    var $additionalElements = Array();

    // on hold 
    // var $source; 
}

/**
 * An FeedImage may be added to a FeedCreator feed. 
 * @author Kai Blankenhorn <kaib@bitfolge.de> 
 * @since 1.3 
 */
class FeedImage extends HtmlDescribable {

    /**
     * Mandatory attributes of an image. 
     */
    var $title, $url, $link;

    /**
     * Optional attributes of an image. 
     */
    var $width, $height, $description;

}

/**
 * An HtmlDescribable is an item within a feed that can have a description that may 
 * include HTML markup. 
 */
class HtmlDescribable {

    /**
     * Indicates whether the description field should be rendered in HTML. 
     */
    var $descriptionHtmlSyndicated;

    /**
     * Indicates whether and to how many characters a description should be truncated. 
     */
    var $descriptionTruncSize;

    /**
     * Returns a formatted description field, depending on descriptionHtmlSyndicated and
     * $descriptionTruncSize properties 
     * @return    string    the formatted description 
     */
    function getDescription() {
        $descriptionField = new FeedHtmlField($this->description);
        $descriptionField->syndicateHtml = $this->descriptionHtmlSyndicated;
        $descriptionField->truncSize = $this->descriptionTruncSize;
        return $descriptionField->output();
    }

}

/**
 * An FeedHtmlField describes and generates 
 * a feed, item or image html field (probably a description). Output is 
 * generated based on $truncSize, $syndicateHtml properties. 
 * @author Pascal Van Hecke <feedcreator.class.php@vanhecke.info> 
 * @version 1.6 
 */
class FeedHtmlField {

    /**
     * Mandatory attributes of a FeedHtmlField. 
     */
    var $rawFieldContent;

    /**
     * Optional attributes of a FeedHtmlField. 
     * 
     */
    var $truncSize, $syndicateHtml;

    /**
     * Creates a new instance of FeedHtmlField. 
     * @param $parFieldContent 
     * @internal param $string : if given, sets the rawFieldContent property 
     */
    function FeedHtmlField($parFieldContent) {
        if ($parFieldContent) {
            $this->rawFieldContent = $parFieldContent;
        }
    }

    /**
     * Creates the right output, depending on $truncSize, $syndicateHtml properties. 
     * @return string    the formatted field 
     */
    function output() {
        // when field available and syndicated in html we assume 
        // - valid html in $rawFieldContent and we enclose in CDATA tags 
        // - no truncation (truncating risks producing invalid html) 
        if (!$this->rawFieldContent) {
            $result = "";
        } elseif ($this->syndicateHtml) {
            $result = "<![CDATA[" . $this->rawFieldContent . "]]>";
        } else {
            if ($this->truncSize and is_int($this->truncSize)) {
                $result = FeedCreator::iTrunc(htmlspecialchars($this->rawFieldContent), $this->truncSize);
            } else {
                $result = htmlspecialchars($this->rawFieldContent);
            }
        }
        return $result;
    }

}

/**
 * UniversalFeedCreator lets you choose during runtime which 
 * format to build. 
 * For general usage of a feed class, see the FeedCreator class 
 * below or the example above. 
 * 
 */
class UniversalFeedCreator extends FeedCreator {

    var $_feed;

    function _setFormat($format) {
        switch (strtoupper($format)) {


            case "2.0":
            // fall through 
            case "RSS2.0":
                $this->_feed = new RSSCreator20();
                break;


            case "0.91":
            // fall through 
            case "RSS0.91":
                $this->_feed = new RSSCreator091();
                break;


            default:
                $this->_feed = new RSSCreator091();
                break;
        }


        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            // prevent overwriting of properties "contentType", "encoding"; do not copy "_feed" itself 
            if (!in_array($key, array("_feed", "contentType", "encoding"))) {
                $this->_feed->{$key} = $this->{$key};
            }
        }
    }

    /**
     * Creates a syndication feed based on the items previously added. 
     * 
     * @see        FeedCreator::addItem() 
     * @param    string    format    format the feed should comply to. Valid values are: 
     *   "PIE0.1", "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM0.3", "HTML", "JS" 
     * @return    string    the contents of the feed. 
     */
    function createFeed($format = "RSS0.91") {
        $this->_setFormat($format);
        return $this->_feed->createFeed();
    }

    /**
     * Saves this feed as a file on the local disk. After the file is saved, an HTTP redirect 
     * header may be sent to redirect the use to the newly created file. 
     * @since 1.4 
     * 
     * @param string $format 
     * @param string $filename 
     * @param bool $displayContents displayContents optional send the content of the file or not. If true, the file will be sent in the body of the response. 
     * @internal param \format $string format the feed should comply to. Valid values are: 
     *   "PIE0.1" (deprecated), "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM", "ATOM0.3", "HTML", "JS" 
     * @internal param \filename $string optional the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see _generateFilename()). 
     */
    function saveFeed($format = "RSS0.91", $filename = "", $displayContents = TRUE) {
        $this->_setFormat($format);
        $this->_feed->saveFeed($filename, $displayContents);
    }

    /**
     * Turns on caching and checks if there is a recent version of this feed in the cache. 
     * If there is, an HTTP redirect header is sent. 
     * To effectively use caching, you should create the FeedCreator object and call this method 
     * before anything else, especially before you do the time consuming task to build the feed 
     * (web fetching, for example). 
     * 
     * @param string $format 
     * @param string $filename 
     * @param int $timeout 
     * @internal param \format $string format the feed should comply to. Valid values are: 
     *       "PIE0.1" (deprecated), "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM0.3". 
     * @internal param string $filename optional the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see _generateFilename()). 
     * @internal param int $timeout optional the timeout in seconds before a cached version is refreshed (defaults to 3600 = 1 hour) 
     */
    function useCached($format = "RSS0.91", $filename = "", $timeout = 3600) {
        $this->_setFormat($format);
        $this->_feed->useCached($filename, $timeout);
    }

}

/**
 * FeedCreator is the abstract base implementation for concrete 
 * implementations that implement a specific format of syndication. 
 * 
 * @abstract 
 * @author Kai Blankenhorn <kaib@bitfolge.de> 
 * @since 1.4 
 */
class FeedCreator extends HtmlDescribable {

    /**
     * Mandatory attributes of a feed. 
     */
    var $title, $description, $link;

    /**
     * Optional attributes of a feed. 
     */
    var $syndicationURL, $image, $language, $copyright, $pubDate, $lastBuildDate, $editor, $editorEmail, $webmaster, $category, $docs, $ttl, $rating, $skipHours, $skipDays;

    /**
     * The url of the external xsl stylesheet used to format the naked rss feed. 
     * Ignored in the output when empty. 
     */
    var $xslStyleSheet = false;
    var $cssStyleSheet = false;

    /**
     * @access private 
     */
    var $items = Array();

    /**
     * This feed's MIME content type. 
     * @since 1.4 
     * @access private 
     */
    var $contentType = "application/xml";

    /**
     * This feed's character encoding. 
     * @since 1.6.1 
     * */
    var $encoding = "utf-8";

    /**
     * Any additional elements to include as an assiciated array. All $key => $value pairs 
     * will be included unencoded in the feed in the form 
     *     <$key>$value</$key> 
     * Again: No encoding will be used! This means you can invalidate or enhance the feed 
     * if $value contains markup. This may be abused to embed tags not implemented by
     * the FeedCreator class used. 
     */
    var $additionalElements = Array();

    /**
     * Adds an FeedItem to the feed. 
     * 
     * @param $item 
     * @internal param \FeedItem $object $item The FeedItem to add to the feed. 
     * @access public 
     */
    function addItem($item) {
        $this->items[] = $item;
    }

    /**
     * 清空当前数组值 
     * 
     * @internal param \FeedItem $object $item The FeedItem to add to the feed. 
     * @access public 
     */
    function clearItem2Null() {
        $this->items = array();
    }

    /**
     * Truncates a string to a certain length at the most sensible point. 
     * First, if there's a '.' character near the end of the string, the string is truncated after this character. 
     * If there is no '.', the string is truncated after the last ' ' character. 
     * If the string is truncated, " ..." is appended. 
     * If the string is already shorter than $length, it is returned unchanged. 
     * 
     * @static 
     * @param string    string A string to be truncated. 
     * @param int        length the maximum length the string should be truncated to 
     * @return string    the truncated string 
     */
    function iTrunc($string, $length) {
        if (strlen($string) <= $length) {
            return $string;
        }


        $pos = strrpos($string, ".");
        if ($pos >= $length - 4) {
            $string = substr($string, 0, $length - 4);
            $pos = strrpos($string, ".");
        }
        if ($pos >= $length * 0.4) {
            return substr($string, 0, $pos + 1) . " ...";
        }


        $pos = strrpos($string, " ");
        if ($pos >= $length - 4) {
            $string = substr($string, 0, $length - 4);
            $pos = strrpos($string, " ");
        }
        if ($pos >= $length * 0.4) {
            return substr($string, 0, $pos) . " ...";
        }


        return substr($string, 0, $length - 4) . " ...";
    }

    /**
     * Creates a comment indicating the generator of this feed. 
     * The format of this comment seems to be recognized by 
     * Syndic8.com. 
     */
    function _createGeneratorComment() {
        return '';
    }

    /**
     * Creates a string containing all additional elements specified in 
     * $additionalElements. 
     * @param array $elements 
     * @param string $indentString 
     * @internal param array $elements an associative array containing key => value pairs 
     * @internal param string $indentString a string that will be inserted before every generated line 
     * @return    string    the XML tags corresponding to $additionalElements 
     */
    function _createAdditionalElements($elements, $indentString = "") {
        $ae = "";
        if (is_array($elements)) {
            foreach ($elements AS $key => $value) {
                $ae .= $indentString . "<$key>$value</$key>\n";
            }
        }
        return $ae;
    }

    function _createStylesheetReferences() {
        $xml = "";
        if ($this->cssStyleSheet)
            $xml .= "<?xml-stylesheet href=\"" . $this->cssStyleSheet . "\" _fcksavedurl=\"" . $this->cssStyleSheet . "\" type=\"text/css\"?>\n";
        if ($this->xslStyleSheet)
            $xml .= "<?xml-stylesheet href=\"" . $this->xslStyleSheet . "\" type=\"text/xsl\"?>\n";
        return $xml;
    }

    /**
     * Builds the feed's text. 
     * @abstract 
     * @return    string    the feed's complete text 
     */
    function createFeed() {
        
    }

    /**
     * Generate a filename for the feed cache file. The result will be $_SERVER["PHP_SELF"] with the extension changed to .xml. 
     * For example: 
     * 
     * echo $_SERVER["PHP_SELF"]."\n"; 
     * echo FeedCreator::_generateFilename(); 
     * 
     * would produce: 
     * 
     * latestnews.xml 
     * 
     * @return string the feed cache filename 
     * @since 1.4 
     * @access private 
     */
    function _generateFilename() {
        $fileInfo = pathinfo($_SERVER["PHP_SELF"]);
        return substr($fileInfo["basename"], 0, -(strlen($fileInfo["extension"]) + 1)) . ".xml";
    }

    /**
     * @since 1.4 
     * @access private 
     */
    function _redirect($filename) {
        // HTTP redirect, some feed readers' simple HTTP implementations don't follow it 
        //Header("Location: ".$filename); 
        Header("Content-Type: " . $this->contentType . "; charset=" . $this->encoding . "; filename=" . basename($filename));
        Header("Content-Disposition: inline; filename=" . basename($filename));
        readfile($filename, "r");
        die();
    }

    /**
     * Turns on caching and checks if there is a recent version of this feed in the cache. 
     * If there is, an HTTP redirect header is sent. 
     * To effectively use caching, you should create the FeedCreator object and call this method 
     * before anything else, especially before you do the time consuming task to build the feed 
     * (web fetching, for example). 
     * @since 1.4 
     * @param filename string optional the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see _generateFilename()). 
     * @param int $timeout 
     * @internal param int $timeout optional the timeout in seconds before a cached version is refreshed (defaults to 3600 = 1 hour) 
     */
    function useCached($filename = "", $timeout = 3600) {
        $this->_timeout = $timeout;
        if ($filename == "") {
            $filename = $this->_generateFilename();
        }
        if (file_exists($filename) AND (time() - filemtime($filename) < $timeout)) {
            $this->_redirect($filename);
        }
    }

    /**
     * Saves this feed as a file on the local disk. After the file is saved, a redirect 
     * header may be sent to redirect the user to the newly created file. 
     * @since 1.4 
     * 
     * @param filename string optional the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see _generateFilename()). 
     * @param bool $displayContents 
     * @internal param bool $redirect optional send an HTTP redirect header or not. If true, the user will be automatically redirected to the created file. 
     */
    function saveFeed($filename = "", $displayContents = TRUE) {
        if ($filename == "") {
            $filename = $this->_generateFilename();
        }
        $feedFile = fopen($filename, "w+");
        if ($feedFile) {
            fputs($feedFile, $this->createFeed());
            fclose($feedFile);
            if ($displayContents) {
                $this->_redirect($filename);
            }
        } else {
            echo "<br /><b>Error creating feed file, please check write permissions.</b><br />";
        }
    }

}

/**
 * FeedDate is an internal class that stores a date for a feed or feed item. 
 * Usually, you won't need to use this. 
 */
class FeedDate {

    var $unix;

    /**
     * Creates a new instance of FeedDate representing a given date. 
     * Accepts RFC 822, ISO 8601 date formats as well as unix time stamps. 
     * @param mixed $dateString optional the date this FeedDate will represent. If not specified, the current date and time is used. 
     */
    function FeedDate($dateString = "") {
        if ($dateString == "")
            $dateString = date("r");


        if (is_integer($dateString)) {
            $this->unix = $dateString;
            return;
        }
        if (preg_match("~(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\\s+)?(\\d{1,2})\\s+([a-zA-Z]{3})\\s+(\\d{4})\\s+(\\d{2}):(\\d{2}):(\\d{2})\\s+(.*)~", $dateString, $matches)) {
            $months = Array("Jan" => 1, "Feb" => 2, "Mar" => 3, "Apr" => 4, "May" => 5, "Jun" => 6, "Jul" => 7, "Aug" => 8, "Sep" => 9, "Oct" => 10, "Nov" => 11, "Dec" => 12);
            $this->unix = mktime($matches[4], $matches[5], $matches[6], $months[$matches[2]], $matches[1], $matches[3]);
            if (substr($matches[7], 0, 1) == '+' or substr($matches[7], 0, 1) == '-') {
                $tzOffset = (substr($matches[7], 0, 3) * 60 + substr($matches[7], -2)) * 60;
            } else {
                if (strlen($matches[7]) == 1) {
                    $oneHour = 3600;
                    $ord = ord($matches[7]);
                    if ($ord < ord("M")) {
                        $tzOffset = (ord("A") - $ord - 1) * $oneHour;
                    } elseif ($ord >= ord("M") AND $matches[7] != "Z") {
                        $tzOffset = ($ord - ord("M")) * $oneHour;
                    } elseif ($matches[7] == "Z") {
                        $tzOffset = 0;
                    }
                }
                switch ($matches[7]) {
                    case "UT":
                    case "GMT":
                        $tzOffset = 0;
                }
            }
            $this->unix += $tzOffset;
            return;
        }
        if (preg_match("~(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(.*)~", $dateString, $matches)) {
            $this->unix = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
            if (substr($matches[7], 0, 1) == '+' or substr($matches[7], 0, 1) == '-') {
                $tzOffset = (substr($matches[7], 0, 3) * 60 + substr($matches[7], -2)) * 60;
            } else {
                if ($matches[7] == "Z") {
                    $tzOffset = 0;
                }
            }
            $this->unix += $tzOffset;
            return;
        }
        $this->unix = 0;
    }

    /**
     * Gets the date stored in this FeedDate as an RFC 822 date. 
     * 
     * @return a date in RFC 822 format 
     */
    function rfc822() {
        //return gmdate("r",$this->unix); 
        $date = gmdate("Y-m-d H:i:s", $this->unix);
        if (TIME_ZONE != "")
            $date .= " " . str_replace(":", "", TIME_ZONE);
        return $date;
    }

    /**
     * Gets the date stored in this FeedDate as an ISO 8601 date. 
     * 
     * @return a date in ISO 8601 format 
     */
    function iso8601() {
        $date = gmdate("Y-m-d H:i:s", $this->unix);
        $date = substr($date, 0, 22) . ':' . substr($date, -2);
        if (TIME_ZONE != "")
            $date = str_replace("+00:00", TIME_ZONE, $date);
        return $date;
    }

    /**
     * Gets the date stored in this FeedDate as unix time stamp. 
     * 
     * @return a date as a unix time stamp 
     */
    function unix() {
        return $this->unix;
    }

}

/**
 * RSSCreator10 is a FeedCreator that implements RDF Site Summary (RSS) 1.0. 
 * 
 * @see http://www.purl.org/rss/1.0/ 
 * @since 1.3 
 */
class RSSCreator10 extends FeedCreator {

    /**
     * Builds the RSS feed's text. The feed will be compliant to RDF Site Summary (RSS) 1.0. 
     * The feed will contain all items previously added in the same order. 
     * @return    string    the feed's complete text 
     */
    function createFeed() {
        $feed = "<?xml version=\"1.0\" encoding=\"" . $this->encoding . "\"?>\n";
        $feed .= $this->_createGeneratorComment();
        if ($this->cssStyleSheet == "") {
            $cssStyleSheet = "http://www.w3.org/2000/08/w3c-synd/style.css";
        }
        $feed .= $this->_createStylesheetReferences();
        $feed .= "<rdf:RDF\n";
        $feed .= "    xmlns=\"http://purl.org/rss/1.0/\"\n";
        $feed .= "    xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n";
        $feed .= "    xmlns:slash=\"http://purl.org/rss/1.0/modules/slash/\"\n";
        $feed .= "    xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n";
        $feed .= "    <channel rdf:about=\"" . $this->syndicationURL . "\">\n";
        $feed .= "        <title>" . htmlspecialchars($this->title) . "</title>\n";
        $feed .= "        <description>" . htmlspecialchars($this->description) . "</description>\n";
        $feed .= "        <link>" . $this->link . "</link>\n";
        if ($this->image != NULL) {
            $feed .= "        <image rdf:resource=\"" . $this->image->url . "\" />\n";
        }
        $now = new FeedDate();
        $feed .= "       <dc:date>" . htmlspecialchars($now->iso8601()) . "</dc:date>\n";
        $feed .= "        <items>\n";
        $feed .= "            <rdf:Seq>\n";
        for ($i = 0; $i < count($this->items); $i++) {
            $feed .= "                <rdf:li rdf:resource=\"" . htmlspecialchars($this->items[$i]->link) . "\"/>\n";
        }
        $feed .= "            </rdf:Seq>\n";
        $feed .= "        </items>\n";
        $feed .= "    </channel>\n";
        if ($this->image != NULL) {
            $feed .= "    <image rdf:about=\"" . $this->image->url . "\">\n";
            $feed .= "        <title>" . $this->image->title . "</title>\n";
            $feed .= "        <link>" . $this->image->link . "</link>\n";
            $feed .= "        <url>" . $this->image->url . "</url>\n";
            $feed .= "    </image>\n";
        }
        $feed .= $this->_createAdditionalElements($this->additionalElements, "    ");


        for ($i = 0; $i < count($this->items); $i++) {
            $feed .= "    <item rdf:about=\"" . htmlspecialchars($this->items[$i]->link) . "\">\n";
            //$feed.= "        <dc:type>Posting</dc:type>\n"; 
            $feed .= "        <dc:format>text/html</dc:format>\n";
            if ($this->items[$i]->date != NULL) {
                $itemDate = new FeedDate($this->items[$i]->date);
                $feed .= "        <dc:date>" . htmlspecialchars($itemDate->iso8601()) . "</dc:date>\n";
            }
            if ($this->items[$i]->source != "") {
                $feed .= "        <dc:source>" . htmlspecialchars($this->items[$i]->source) . "</dc:source>\n";
            }
            if ($this->items[$i]->author != "") {
                $feed .= "        <dc:creator>" . htmlspecialchars($this->items[$i]->author) . "</dc:creator>\n";
            }
            $feed .= "        <title>" . htmlspecialchars(strip_tags(strtr($this->items[$i]->title, "\n\r", "  "))) . "</title>\n";
            $feed .= "        <link>" . htmlspecialchars($this->items[$i]->link) . "</link>\n";
            $feed .= "        <description>" . htmlspecialchars($this->items[$i]->description) . "</description>\n";
            $feed .= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
            $feed .= "    </item>\n";
        }
        $feed .= "</rdf:RDF>\n";
        return $feed;
    }

}

/**
 * RSSCreator091 is a FeedCreator that implements RSS 0.91 Spec, revision 3. 
 * 
 * @see http://my.netscape.com/publish/formats/rss-spec-0.91.html 
 * @since 1.3 
 */
class RSSCreator091 extends FeedCreator {

    /**
     * Stores this RSS feed's version number. 
     * @access private 
     */
    var $RSSVersion;

    function RSSCreator091() {
        $this->_setRSSVersion("0.91");
        $this->contentType = "application/rss+xml";
    }

    /**
     * Sets this RSS feed's version number. 
     * @access private 
     */
    function _setRSSVersion($version) {
        $this->RSSVersion = $version;
    }

    /**
     * Builds the RSS feed's text. The feed will be compliant to RDF Site Summary (RSS) 1.0. 
     * The feed will contain all items previously added in the same order. 
     * @return    string    the feed's complete text 
     */
    function createFeed() {
        $feed = "<?xml version=\"1.0\" encoding=\"" . $this->encoding . "\"?>\n";
        $feed .= $this->_createGeneratorComment();
        $feed .= $this->_createStylesheetReferences();
        $feed .= "<rss version=\"" . $this->RSSVersion . "\">\n";
        $feed .= "    <channel>\n";
        $feed .= "        <title>" . FeedCreator::iTrunc(htmlspecialchars($this->title), 100) . "</title>\n";
        $this->descriptionTruncSize = 500;
        $feed .= "        <description>" . $this->getDescription() . "</description>\n";
        $feed .= "        <link>" . $this->link . "</link>\n";
        $now = new FeedDate();
        $feed .= "        <lastBuildDate>" . htmlspecialchars($now->rfc822()) . "</lastBuildDate>\n";
        $feed .= "        <generator>" . FEEDCREATOR_VERSION . "</generator>\n";
        if ($this->image != NULL) {
            $feed .= "        <image>\n";
            $feed .= "            <url>" . $this->image->url . "</url>\n";
            $feed .= "            <title>" . FeedCreator::iTrunc(htmlspecialchars($this->image->title), 100) . "</title>\n";
            $feed .= "            <link>" . $this->image->link . "</link>\n";
            if ($this->image->width != "") {
                $feed .= "            <width>" . $this->image->width . "</width>\n";
            }
            if ($this->image->height != "") {
                $feed .= "            <height>" . $this->image->height . "</height>\n";
            }
            if ($this->image->description != "") {
                $feed .= "            <description>" . $this->image->getDescription() . "</description>\n";
            }
            $feed .= "        </image>\n";
        }
        if ($this->language != "") {
            $feed .= "        <language>" . $this->language . "</language>\n";
        }
        if ($this->copyright != "") {
            $feed .= "        <copyright>" . FeedCreator::iTrunc(htmlspecialchars($this->copyright), 100) . "</copyright>\n";
        }
        if ($this->editor != "") {
            $feed .= "        <managingEditor>" . FeedCreator::iTrunc(htmlspecialchars($this->editor), 100) . "</managingEditor>\n";
        }
        if ($this->webmaster != "") {
            $feed .= "        <webMaster>" . FeedCreator::iTrunc(htmlspecialchars($this->webmaster), 100) . "</webMaster>\n";
        }
        if ($this->pubDate != "") {
            $pubDate = new FeedDate($this->pubDate);
            $feed .= "        <pubDate>" . htmlspecialchars($pubDate->rfc822()) . "</pubDate>\n";
        }
        if ($this->category != "") {
            $feed .= "        <category>" . htmlspecialchars($this->category) . "</category>\n";
        }
        if ($this->docs != "") {
            $feed .= "        <docs>" . FeedCreator::iTrunc(htmlspecialchars($this->docs), 500) . "</docs>\n";
        }
        if ($this->ttl != "") {
            $feed .= "        <ttl>" . htmlspecialchars($this->ttl) . "</ttl>\n";
        }
        if ($this->rating != "") {
            $feed .= "        <rating>" . FeedCreator::iTrunc(htmlspecialchars($this->rating), 500) . "</rating>\n";
        }
        if ($this->skipHours != "") {
            $feed .= "        <skipHours>" . htmlspecialchars($this->skipHours) . "</skipHours>\n";
        }
        if ($this->skipDays != "") {
            $feed .= "        <skipDays>" . htmlspecialchars($this->skipDays) . "</skipDays>\n";
        }
        $feed .= $this->_createAdditionalElements($this->additionalElements, "    ");
        for ($i = 0; $i < count($this->items); $i++) {
            $feed .= "        <item>\n";
            $feed .= "            <title>" . FeedCreator::iTrunc(htmlspecialchars(strip_tags($this->items[$i]->title)), 100) . "</title>\n";
            $feed .= "            <link>" . htmlspecialchars($this->items[$i]->link) . "</link>\n";
            $feed .= "            <description>" . $this->items[$i]->getDescription() . "</description>\n";


            if ($this->items[$i]->author != "") {
                $feed .= "            <author>" . htmlspecialchars($this->items[$i]->author) . "</author>\n";
            }
            /*
              // on hold
              if ($this->items[$i]->source!="") {
              $feed.= "            <source>".htmlspecialchars($this->items[$i]->source)."</source>\n";
              }
             */
            if ($this->items[$i]->category != "") {
                $feed .= "            <category>" . htmlspecialchars($this->items[$i]->category) . "</category>\n";
            }
            if ($this->items[$i]->comments != "") {
                $feed .= "            <comments>" . htmlspecialchars($this->items[$i]->comments) . "</comments>\n";
            }
            if ($this->items[$i]->date != "") {
                $itemDate = new FeedDate($this->items[$i]->date);
                $feed .= "            <pubDate>" . htmlspecialchars($itemDate->rfc822()) . "</pubDate>\n";
            }
            if ($this->items[$i]->guid != "") {
                $feed .= "            <guid>" . htmlspecialchars($this->items[$i]->guid) . "</guid>\n";
            }
            $feed .= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
            $feed .= "        </item>\n";
        }
        $feed .= "    </channel>\n";
        $feed .= "</rss>\n";
        return $feed;
    }

}

/**
 * Class RSSCreator20 
 */
class RSSCreator20 extends RSSCreator091 {

    function RSSCreator20() {
        parent::_setRSSVersion("2.0");
    }

}

?>