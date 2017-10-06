Ext.namespace("fullplugin");

fullplugin.application = {
  init:function(){
    storeStepProcess = function (n, r, i) {
      var myMask = new Ext.LoadMask(Ext.getBody(), {msg:"Load Odoo steps..."});
      myMask.show();

      Ext.Ajax.request({
        url: "sc_appAjax",
        method: "POST",
        params: {"option": "LST", "pageSize": n, "limit": r, "start": i},
                         
        success:function (result, request) {
                  storeStep.loadData(Ext.util.JSON.decode(result.responseText));
                  myMask.hide();
                },
        failure:function (result, request) {
                  myMask.hide();
                  Ext.MessageBox.alert("Alert", "Failure Odoo steps load");
                }
      });
    };
    
    onMnuContext = function(grid, rowIndex,e) {
      e.stopEvent();
      var coords = e.getXY();
      mnuContext.showAt([coords[0], coords[1]]);
    };
    
    //Variables declared in html file
    var pageSize = parseInt(CONFIG.pageSize);
    var message = CONFIG.message;
    
    //stores
    var storeStep = new Ext.data.Store({
      proxy:new Ext.data.HttpProxy({
        url:    "sc_appAjax",
        method: "POST"
      }),
      
      //baseParams: {"option": "LST", "pageSize": pageSize},
            
      reader:new Ext.data.JsonReader({
        root: "resultRoot",
        totalProperty: "resultTotal",
        fields: [{name: "ID"},
                 {name: "NAME"},
                 {name: "METODO"},
                 {name: "MODELO"}
                ]
      }),
      
      //autoLoad: true, //First call
      
      listeners:{
        beforeload:function (store) {
          this.baseParams = {"option": "LST", "pageSize": pageSize,"textfilter": txtSearch.getValue()};
        }
      }
    });
    
    var storePageSize = new Ext.data.SimpleStore({
      fields: ["size"],
      data: [["15"], ["25"], ["35"], ["50"], ["100"]],
      autoLoad: true
    });
    comboMetodos = new Ext.data.SimpleStore({
      fields: ['metodo'],
      data: [["search"], ["search_count"], ["read"], ["fields_get"], ["search_read"], ["create"], ["write"], ["unlink"]],
      autoLoad: true
    });
    /*
    comboDepManager = new Ext.data.GroupingStore( {
    proxy : new Ext.data.HttpProxy({
      url: 'departments_Ajax?action=usersByDepartment'
    }),
    reader : new Ext.data.JsonReader( {
      root: 'users',
      fields : [
                {name : 'USR_UID'},
                {name : 'USR_VALUE'}
                ]
    })
  });
    
     */
    //
    var btnNew = new Ext.Action({
      id: "btnNew",
      
      text: "New",
      iconCls: "button_menu_ext ss_sprite ss_add",
      
      handler: function() {
        newForm = new Ext.FormPanel({
        url: '../odooStep/sc_appAjax',
        frame: true,
        items: [
              {xtype: 'textfield', fieldLabel: 'Nombre', name: 'nombre', width: 250, maxLength :100, allowBlank: false},
              // query a la tabla process
              {xtype: 'textfield', fieldLabel: 'Proceso', name: 'proceso', width: 250, maxLength :100, allowBlank: false},
              //{xtype: 'textfield', fieldLabel: 'Método', name: 'metodo', width: 250, maxLength :100, allowBlank: false},
              {
                xtype: 'combo',
                fieldLabel: 'Método',
                name: 'metodo',
                typeAhead: true,
                mode: 'local',
                store: comboMetodos,
                displayField: 'metodo',
                valueField:'metodo',
                allowBlank: false,
                editable: false,
                triggerAction: 'all',
                emptyText: '',
                selectOnFocus:true
              },
              {xtype: 'textfield', fieldLabel: 'Modelo', name: 'modelo', width: 250, maxLength :100, allowBlank: false},
              {xtype: 'textarea',
              /*emptyText:"Parametros",*/ fieldLabel: 'Parámetros', name: 'parametros', width: 250, maxLength :100, allowBlank: false},
              {xtype: 'textarea', fieldLabel: 'Parámetros KW', name: 'parametros KW', width: 250, maxLength :100, allowBlank: false},
              {xtype: 'textfield', fieldLabel: 'Salida', name: 'salida', width: 250, maxLength :100, allowBlank: false},
        ],
        buttons: [
              {text: _('ID_SAVE'), handler: function(){


                  //catName = catName.trim();
                  //if (catName == '') { // VALIDADOR
                    //Ext.Msg.alert(_('ID_WARNING'), _("ID_FIELD_REQUIRED", 'Nombre'));
                    //return;
                  //}
                  //viewport.getEl().mask(_('ID_PROCESSING'));
                  Ext.Ajax.request({
                    url: '../odooStep/sc_appAjax',
                    params: {
                        option: "NEWSTEP",// LA ACCION
                          newNombre: newForm.getForm().findField('nombre').getValue(),
                          newProceso: newForm.getForm().findField('proceso').getValue(),
                          newMetodo: newForm.getForm().findField('metodo').getValue(),
                          newModelo: newForm.getForm().findField('modelo').getValue(),
                          newParametros: newForm.getForm().findField('parametros').getValue(),
                          newParametrosKW: newForm.getForm().findField('parametros KW').getValue(),
                          newSalida: newForm.getForm().findField('salida').getValue()
                      },
                    success: function(r,o){
                      //viewport.getEl().unmask();
                      PMExt.notify("Success","Odoo Step created" ); // Crea mininotificaciones en el esquinazo.
                      CloseWindow();
                      /*resp = eval(r.responseText);
                      if (resp){
                        CloseWindow(); //Hide popup widow
                            newForm.getForm().reset(); //Set empty form to next use
                            txtSearch.reset();
                            infoGrid.store.load();

                        //viewport.getEl().mask(_('ID_PROCESSING'));
                      }else{
                        PMExt.error(_('ID_PROCESS_CATEGORY'),_('ID_CATEGORY_EXISTS')); // Crea mensajes de error.
                      }*/
                    },
                    failure: function(r,o){
                      viewport.getEl().unmask();
                    }
                  });
              }},
              {text: _('ID_CANCEL'), handler: CloseWindow}
        ]
      });
      
      newForm.getForm().reset();
      newForm.getForm().items.items[0].focus('',500);
      w = new Ext.Window({
        title: "Create new Odoo Step",
        autoHeight: true,
        width: 420,
        items: [newForm],
        id: 'w',
        modal: true
      });
      w.show();
       //Ext.MessageBox.alert("Alert", message);
      }
    });


    
    var btnEdit = new Ext.Action({
      id: "btnEdit",
      
      text: "Edit",
      iconCls: "button_menu_ext ss_sprite ss_pencil",
      disabled: true,
      
      handler: function() {
        Ext.MessageBox.alert("Alert", message);
      }
    });
    
    var btnDelete = new Ext.Action({
      id: "btnDelete",
      
      text: "Delete",
      iconCls: "button_menu_ext ss_sprite ss_delete",
      disabled: true,
      
      handler: function() {
        Ext.MessageBox.alert("Alert", message);
      }
    });
    // Do search function
    // infogrid es un Ext.grid.Gridpanel
    // store es un Ext.data.Grouping store,
    // con remoteSort:true
    DoSearch = function(){
      storeStep.load({params:{textFilter: txtSearch.getValue()}});
    }
    var btnSearch = new Ext.Action({
      id: "btnSearch",
      
      text: "Search",
      
      handler: function() {
        //Ext.MessageBox.alert("Alert", message);
        DoSearch();
        //pagingUser.moveFirst();
      }
    });
    
    var mnuContext = new Ext.menu.Menu({
      id: "mnuContext",
      
      items: [btnEdit, btnDelete]
    });
    
    var txtSearch = new Ext.form.TextField({
      id: "txtSearch",
      
      emptyText: "Enter search term",
      width: 150,
      allowBlank: true,
      
      listeners:{
        specialkey: function (f, e) {
          if (e.getKey() == e.ENTER) {
            //Ext.MessageBox.alert("Alert", message);
            DoSearch();
            //pagingUser.moveFirst();
          }
        }
      }
    });
    
    var btnTextClear = new Ext.Action({
      id: "btnTextClear",
      
      text: "X",
      ctCls: "pm_search_x_button",
      handler: function() {
        txtSearch.reset();
      }
    });
    
    var cboPageSize = new Ext.form.ComboBox({
      id: "cboPageSize",
      
      mode: "local",
      triggerAction: "all",
      store: storePageSize,
      valueField: "size",
      displayField: "size",
      width: 50,
      editable: false,
      
      listeners:{
        select: function (combo, record, index) {
          pageSize = parseInt(record.data["size"]);
          
          pagingUser.pageSize = pageSize;
          pagingUser.moveFirst();
        }
      }
    });
    
    var pagingUser = new Ext.PagingToolbar({
      id: "pagingUser",
      
      pageSize: pageSize,
      store: storeStep,
      displayInfo: true,
      displayMsg: "Displaying Odoo steps " + "{" + "0" + "}" + " - " + "{" + "1" + "}" + " of " + "{" + "2" + "}",
      emptyMsg: "No steps to display",
      items: ["-", "Page size:", cboPageSize]
    });
       
    var cmodel = new Ext.grid.ColumnModel({
      defaults: {
        width:50,
        sortable:true
      },
      columns:[{id: "ID", dataIndex: "ID", hidden: true},
               {header: "Name", dataIndex: "NAME", align: "left"},
               {header: "Model", dataIndex: "MODELO", width: 25, align: "center"},
               {header: "Method", dataIndex: "METODO", width: 25, align: "left"}
              ]
    });
    
    var smodel = new Ext.grid.RowSelectionModel({
      singleSelect: true,
      listeners: {
        rowselect: function (sm) {
          btnEdit.enable();
          btnDelete.enable();
        },
        rowdeselect: function (sm) {
          btnEdit.disable();
          btnDelete.disable();
        }
      }
    });
    
    var grdpnlUser = new Ext.grid.GridPanel({
      id: "grdpnlUser",
      
      store: storeStep,
      colModel: cmodel,
      selModel: smodel,
      
      columnLines: true,
      viewConfig: {forceFit: true},
      enableColumnResize: true,
      enableHdMenu: true, //Menu of the column
      
      tbar: [btnNew, "-", btnEdit, btnDelete, "-", "->", txtSearch, btnTextClear, btnSearch],
      bbar: pagingUser,
      
      style: "margin: 0 auto 0 auto;",
      width: 550,
      height: 450, 
      title: "Odoo Steps",      
      
      renderTo: "divMain",
      
      listeners:{
      }
    });

    
    //Initialize events
    storeStepProcess(pageSize, pageSize, 0);
    
    grdpnlUser.on("rowcontextmenu", 
      function (grid, rowIndex, evt) {
        var sm = grid.getSelectionModel();
        sm.selectRow(rowIndex, sm.isSelected(rowIndex));
      },
      this
    );
    
    grdpnlUser.addListener("rowcontextmenu", onMnuContext, this);
    
    cboPageSize.setValue(pageSize);
  }
}

//Close PopUp Window
CloseWindow = function(){
  Ext.getCmp('w').hide();
};

Ext.onReady(fullplugin.application.init, fullplugin.application);