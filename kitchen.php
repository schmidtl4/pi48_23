<?php
    const siteSafetyKey = TRUE;

    require($_SERVER['DOCUMENT_ROOT'].'/core/boot.php');

    $page = new PageKitchen();

    echo $page->page;
