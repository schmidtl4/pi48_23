<?php
    const siteSafetyKey = TRUE;

    require($_SERVER['DOCUMENT_ROOT'].'/core/boot.php');

    $page = new PageComment();

    echo $page->page;
