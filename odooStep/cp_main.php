<?php
/*
 * Configuration page: Callback del menu "menuConfigPage"
 */

$G_MAIN_MENU            = 'processmaker';
$G_ID_MENU_SELECTED     = 'ID_ODOOSTEP_MNU_02';
$G_PUBLISH = new Publisher;
$G_PUBLISH->AddContent('view', 'odooStep/cp_iframe');//'view',
G::RenderPage('publish');
?>