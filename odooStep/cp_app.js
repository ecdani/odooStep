/**
 * Construye la página de config, usando ExtJS 3.2.1
 */

Ext.namespace("odooStep");

odooStep.cp_app = {
  init:function(){
    
    var txtUrl = new Ext.form.Field({ 
      id: "txtUrl",
      value: CONFIG.resultado.Url, // Valor de inicializacion
      width: 150,
      editable: false,
      fieldLabel: "Url Odoo:"
    });
    
      var txtDb = new Ext.form.Field({ 
      id: "txtDb",
      width: 150,
      editable: false,
      fieldLabel: "Base de datos"
    });
      var txtUsuario = new Ext.form.Field({ 
      id: "txtUsuario",
      width: 150,
      editable: false,
      fieldLabel: "Usuario"
    });
      var txtPassword = new Ext.form.Field({ 
      id: "txtPassword",
      inputType: "password",
      width: 150,
      editable: false,
      fieldLabel: "Contraseña"
    });
    var btnSubmit = new Ext.Button({
      id: "btnSubmit",
                    
      text: "Guardar",
      //anchor: "95%",
                    
      handler: function () {
        //Ext.MessageBox.alert("Alert", "Event handler in Ext.Button");
        var myMask = new Ext.LoadMask(Ext.getBody(), {msg:"Saving..."});
      myMask.show();


      Ext.Ajax.request({
        url: "../odooStep/cp_appAjax", // Llamada al fichero php de Ajax cp_appAjax.php
        method: "POST",
        //form: 'frmHistory',
        params: {
          option: "SAVE",
          txtUrl: frmHistory.getForm().findField('txtUrl').getValue(),
          txtDb: frmHistory.getForm().findField('txtDb').getValue(),
          txtUsuario: frmHistory.getForm().findField('txtUsuario').getValue(),
          txtPassword: frmHistory.getForm().findField('txtPassword').getValue()
        },
                         
        success:function (result, request) {
                  console.log(result);
                  //storeApplication.loadData(Ext.util.JSON.decode(result.responseText));
                  myMask.hide();
                },
        failure:function (result, request) {
                  myMask.hide();
                  Ext.MessageBox.alert("Alert", "Failure application data load");
                }
      });
      }
    });
    
    /**
     * PRECARGA DE LOS CAMPOS
     *//*
    Ext.Ajax.request({
        url: "../odooStep/cp_appAjax", // Llamada al fichero php de Ajax
        method: "POST",
        params: {
          option: "LOAD"
        },
                         
        success:function (result, request) {
                  console.log(result);
                  respuesta = Ext.util.JSON.decode(result.responseText);
                  console.log(CONFIG);//{"success":true,"respuesta":{"Id":1,"Url":"Jane","Db":"Austen","Username":"Austen","Password":"Austen"}}
                  //storeApplication.loadData(Ext.util.JSON.decode(result.responseText));
                  frmHistory.getForm().findField('txtUrl').setValue(respuesta['respuesta']['Url']),
                  frmHistory.getForm().findField('txtDb').setValue(respuesta['respuesta']['Db']),
                  frmHistory.getForm().findField('txtUsuario').setValue(respuesta['respuesta']['Username']),
                  frmHistory.getForm().findField('txtPassword').setValue(respuesta['respuesta']['Password'])
                },
        failure:function (result, request) {
                  Ext.MessageBox.alert("Alert", "Failure application data load");
                }
      });*/

      
    //------------------------------------------------------------------------------------------------------------------
    var tbarMain = new Ext.Toolbar({
      id: "tbarMain",
                   
      items: [{text: "< Back"},
              "-",
              "->", //Right
              "-",
              {text: "Home"}
             ]
    });
                 
    var frmHistory = new Ext.FormPanel({
      id: "frmHistory",
               
      labelWidth: 115, //The width of labels in pixels
      bodyStyle: "padding:0.5em;",
      border: false,
                     
      
      items: [txtUrl,txtDb,txtPassword,txtUsuario],
                     
      buttonAlign: "right",
      buttons: [btnSubmit,
                {text:"Reset",
                 handler: function () {
                   frmHistory.getForm().reset();
                 }
                }
               ]
    });
                
    var pnlCenter = new Ext.Panel({
      id: "pnlCenter",
      border: false,
      region:"center",
      margins: {top:3, right:3, bottom:3, left:0},
      bodyStyle: "padding:25px 25px 25px 25px;",
                    
      //html: "Application2",
      items: [frmHistory]
    }); 
   
    //------------------------------------------------------------------------------------------------------------------
    var pnlMain=new Ext.Panel({
      id: "pnlMain",
      items: [pnlCenter],
      layout: "border",
      defaults: {autoScroll: true},
      border: true,

      //collapsible: true,
      split: true,
      margins: {top:3, right:3, bottom:3, left:3},
                  
      title: "Configuraciones conexion con Odoo",
      tbar: tbarMain,
     
    });

    //LOAD ALL PANELS
    var viewport = new Ext.Viewport({
      layout: "fit",
      items:[pnlMain]
    });

    if (CONFIG.respuesta){
      frmHistory.getForm().findField('txtUrl').setValue(CONFIG.respuesta.Url),
      frmHistory.getForm().findField('txtDb').setValue(respuesta['respuesta']['Db']),
      frmHistory.getForm().findField('txtUsuario').setValue(respuesta['respuesta']['Username']),
      frmHistory.getForm().findField('txtPassword').setValue(respuesta['respuesta']['Password'])
    }
  }
}

Ext.onReady(odooStep.cp_app.init, odooStep.cp_app);

