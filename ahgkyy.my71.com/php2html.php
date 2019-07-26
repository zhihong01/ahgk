<?php

function createStaticPage($sourcePage, $objectPage) {
    $data = file_get_contents($sourcePage);
    file_put_contents($objectPage, $data);
    return true;
}

if (createStaticPage("http://ssxfw.i.my71.com/index.php?c=header", "./header.html")) {
    echo "header.html OK!<br />";
} else {
    echo "header.html Error!<br />";
}

if (createStaticPage("http://ssxfw.i.my71.com/index.php?c=footer", "./footer.html")) {
    echo "footer.html OK!<br />";
} else {
    echo "footer.html Error!<br />";
}

