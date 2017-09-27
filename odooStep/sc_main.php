<?php

// Callback del menu "menuodooStep"

$G_MAIN_MENU            = 'processmaker';
$G_ID_MENU_SELECTED     = 'ID_ODOOSTEP_MNU_01';
$G_PUBLISH = new Publisher;
$G_PUBLISH->AddContent('view', 'odooStep/sc_iframe');//'view',
G::RenderPage('publish');
?>