

/* A Ver SI ESTO SE PUEDE SOBREESCRIBIR, es un JS**/
console.log('En algun punto entro a override.js');

$( "span[title='odooStep external step (EXTERNAL)'] + div > a:nth-child(2)" ).css({"color": "red", "border": "2px solid red"});

$( "span[title='odooStep external step (EXTERNAL)'] + div > a:nth-child(2)" ).on( "click", function() {
  console.log( "La hostia" );
});


$( "button-icon " ).on( "click", function() {
  console.log( "Jooderrr" );
});
//$('mafe-button-edit propertiesTask-accordionButton').onClic
/*
stepsTask.prototype.editStepShow = function (step, accordioItem) {
    var inputDocument,
        that = this;
    switch (step.step_type_obj) {
        case 'EXTERNAL':
            console.log("SOBRESCRIBIDO!")
            break;
        case 'OUTPUT_DOCUMENT':
            PMDesigner.output();
            PMDesigner.output.showTiny(step.step_uid_obj);
            break;
        case 'INPUT_DOCUMENT':
            inputDocument = new InputDocument({
                onUpdateInputDocumentHandler: function (data, inputDoc) {
                    var position, title;
                    position = accordioItem.dataItem.step_position;
                    title = position + ". " + data.inp_doc_title;
                    title = title + ' (' + that.stepsType["INPUT_DOCUMENT"] + ')';
                    accordioItem.dataItem.obj_title = data.inp_doc_title;
                    accordioItem.setTitle(title);
                    inputDoc.winMainInputDocument.close();
                }
            });
            inputDocument.build();
            inputDocument.openFormInMainWindow();
            inputDocument.inputDocumentFormGetProxy(step.step_uid_obj);
            break;
    }
};*/
/*
"{"success":true,"respuesta":
{"option":"SAVE","txtUrl":"http:\/\/localhost:8069","txtDb":"","txtUsuario":"","txtPassword":""},

"_SESSION":{"__EE_INSTALLATION__":10,"__EE_SW_PMLICENSEMANAGER__":1,"phpLastFileFound":"\/sysworkflow\/en\/neoclassic\/odooStep\/cp_appAjax","USERNAME_PREVIOUS1":"","USERNAME_PREVIOUS2":"admin","WORKSPACE":"workflow","USER_LOGGED":"00000000000000000000000000000001","USR_USERNAME":"admin","USR_TIME_ZONE":"America\/New_York","USR_FULLNAME":"Administrator admin","currentSkin":"neoclassic","currentSkinVariant":"extJs","user_experience":"NORMAL","CONDITION_DYN_UID":""},"request":[]}<br />
<b>Warning</b>:  Illegal string offset 'VAR_UID' in <b>/opt/plugins/odooStep/odooStep/cp_appAjax.php</b> on line <b>62</b><br /><br />
<b>Warning</b>:  array_key_exists() expects parameter 2 to be array, string given in
 <b>/opt/processmaker/workflow/engine/classes/model/om/BaseProcessVariables.php</b> on line <b>920</b><br /><br /
 ><b>Warning</b>:  array_key_exists() expects parame"


 "{"success":true,"respuesta":{}}<br /
 
 ><b>Notice</b>:  Undefined index: pageSize in <b>/opt/plugins/odooStep/odooStep/cp_appAjax.php</b> on line <b>98</b>
 
 <br />{"success":true,"respuesta":{"option":"LOAD"}}"*/