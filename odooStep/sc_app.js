Ext.namespace("fullplugin");

fullplugin.application = {
  init: function () {
    storeStepProcess = function (n, r, i) {
      var myMask = new Ext.LoadMask(Ext.getBody(), { msg: "Load Odoo steps..." });
      myMask.show();

      Ext.Ajax.request({
        url: "sc_appAjax",
        method: "POST",
        params: { "option": "LST", "pageSize": n, "limit": r, "start": i },

        success: function (result, request) {
          storeStep.loadData(Ext.util.JSON.decode(result.responseText));
          myMask.hide();
        },
        failure: function (result, request) {
          myMask.hide();
          Ext.MessageBox.alert("Alert", "Failure Odoo steps load");
        }
      });
    };

    onMnuContext = function (grid, rowIndex, e) {
      e.stopEvent();
      var coords = e.getXY();
      mnuContext.showAt([coords[0], coords[1]]);
    };

    //Variables declared in html file
    var pageSize = parseInt(CONFIG.pageSize);
    var message = CONFIG.message;

    //stores
    var storeStep = new Ext.data.Store({
      proxy: new Ext.data.HttpProxy({
        url: "sc_appAjax",
        method: "POST"
      }),

      reader: new Ext.data.JsonReader({
        root: "resultRoot",
        totalProperty: "resultTotal",
        fields: [{ name: "ID" },
        { name: "PRO_UID" },
        { name: "NOMBRE" },
        { name: "METHOD" },
        { name: "MODEL" },
        { name: "PARAMETERS" },
        { name: "KW_PARAMETERS" },
        { name: "OUTPUT" }
        ]
      }),

      listeners: {
        beforeload: function (store) {
          this.baseParams = { "option": "LST", "pageSize": pageSize, "textfilter": txtSearch.getValue() };
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

    var btnNew = new Ext.Action({
      id: "btnNew",

      text: "New",
      iconCls: "button_menu_ext ss_sprite ss_add",

      handler: function () {
        newForm = new Ext.FormPanel({
          url: '../odooStep/sc_appAjax',
          frame: true,
          items: [
            { xtype: 'textfield', fieldLabel: 'Nombre', name: 'nombre', width: 250, maxLength: 100, allowBlank: false }, // query a la tabla process
            { xtype: 'textfield', fieldLabel: 'Proceso', name: 'proceso', width: 250, maxLength: 100, allowBlank: false },
            {
              xtype: 'combo',
              fieldLabel: 'Método',
              name: 'metodo',
              typeAhead: true,
              mode: 'local',
              store: comboMetodos,
              displayField: 'metodo',
              valueField: 'metodo',
              allowBlank: false,
              editable: false,
              triggerAction: 'all',
              emptyText: '',
              selectOnFocus: true
            },
            { xtype: 'textfield', fieldLabel: 'Modelo', name: 'modelo', width: 250, maxLength: 100, allowBlank: false },
            { xtype: 'textarea', fieldLabel: 'Parámetros', name: 'parametros', width: 250, maxLength: 100, allowBlank: false },
            { xtype: 'textarea', fieldLabel: 'Parámetros KW', name: 'parametros KW', width: 250, maxLength: 100, allowBlank: false },
            { xtype: 'textfield', fieldLabel: 'Salida', name: 'salida', width: 250, maxLength: 100, allowBlank: false },
          ],
          buttons: [
            {
              text: _('ID_SAVE'), handler: function () {
                //catName = catName.trim();
                //if (catName == '') { // VALIDADOR
                //Ext.Msg.alert(_('ID_WARNING'), _("ID_FIELD_REQUIRED", 'Nombre'));
                //return;
                //}
                viewport.getEl().mask(_('ID_PROCESSING'));
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
                  success: function (r, o) {
                    viewport.getEl().unmask();
                    PMExt.notify("Success", "Odoo Step created"); // Crea mininotificaciones en el esquinazo.
                    CloseWindow();
                    pagingStep.moveFirst();
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
                  failure: function (r, o) {
                    viewport.getEl().unmask();
                  }
                });
              }
            },
            { text: _('ID_CANCEL'), handler: CloseWindow }
          ]
        });

        newForm.getForm().reset();
        newForm.getForm().items.items[0].focus('', 500);
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

    SaveEditStepAction = function () {
      Ext.Ajax.request({
        url: 'sc_appAjax',
        params: {
          option: "UPDATESTEP",// LA ACCION
          id: editForm.getForm().findField('id').getValue(),
          newNombre: editForm.getForm().findField('nombre').getValue(),
          newProceso: editForm.getForm().findField('proceso').getValue(),
          newMetodo: editForm.getForm().findField('metodo').getValue(),
          newModelo: editForm.getForm().findField('modelo').getValue(),
          newParametros: editForm.getForm().findField('parametros').getValue(),
          newParametrosKW: editForm.getForm().findField('parametros KW').getValue(),
          newSalida: editForm.getForm().findField('salida').getValue()
        },
        success: function (r, o) {
          viewport.getEl().unmask();
          PMExt.notify("Success", "Odoo Step updated"); // Crea mininotificaciones en el esquinazo.
          CloseWindow();
          pagingStep.moveFirst();
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
        failure: function (r, o) {
          viewport.getEl().unmask();
          console.log('Fallo al salvar la edicion');
        }
      });
    }

    editForm = new Ext.FormPanel({
      url: '../odooStep/sc_appAjax',
      frame: true,
      items: [
        { xtype: 'textfield', name: 'id', hidden: true },
        { xtype: 'textfield', fieldLabel: 'Nombre', name: 'nombre', width: 250, maxLength: 100, allowBlank: false },
        // query a la tabla process
        { xtype: 'textfield', fieldLabel: 'Proceso', name: 'proceso', width: 250, maxLength: 100, allowBlank: false },
        //{xtype: 'textfield', fieldLabel: 'Método', name: 'metodo', width: 250, maxLength :100, allowBlank: false},
        {
          xtype: 'combo',
          fieldLabel: 'Método',
          name: 'metodo',
          typeAhead: true,
          mode: 'local',
          store: comboMetodos,
          displayField: 'metodo',
          valueField: 'metodo',
          allowBlank: false,
          editable: false,
          triggerAction: 'all',
          emptyText: '',
          selectOnFocus: true
        },
        { xtype: 'textfield', fieldLabel: 'Modelo', name: 'modelo', width: 250, maxLength: 100, allowBlank: false },
        { xtype: 'textarea', fieldLabel: 'Parámetros', name: 'parametros', width: 250, maxLength: 100, allowBlank: false },
        { xtype: 'textarea', fieldLabel: 'Parámetros KW', name: 'parametros KW', width: 250, maxLength: 100, allowBlank: false },
        { xtype: 'textfield', fieldLabel: 'Salida', name: 'salida', width: 250, maxLength: 100, allowBlank: false },
      ],
      buttons: [
        {
          xtype: "button",
          id: "btnUpdateSave",
          text: _("ID_SAVE"),
          handler: function (btn, ev) {
            Ext.getCmp("btnUpdateSave").setDisabled(true);

            SaveEditStepAction();
          }
        },
        {
          xtype: "button",
          id: "btnUpdateCancel",
          text: _("ID_CANCEL"),
          handler: function (btn, ev) {
            CloseWindow();
          }
        }
      ]
    });

    //Open Edit Group Form
    EditStepWindow = function () {
      var rowSelected = grdpnlStep.getSelectionModel().getSelected();
      /*var strName = stringReplace("&lt;", "<", rowSelected.data.CON_VALUE);
      strName = stringReplace("&gt;", ">", strName);

      editForm.getForm().findField('grp_uid').setValue(rowSelected.data.GRP_UID);
      editForm.getForm().findField('name').setValue(strName);
      var valueEditChangeInt = (rowSelected.data.GRP_STATUS == 'ACTIVE') ? '1' : '0';
      editForm.getForm().findField('status').setValue(valueEditChangeInt);*/

      editForm.getForm().findField('id').setValue(rowSelected.data.ID);
      editForm.getForm().findField('nombre').setValue(rowSelected.data.NOMBRE);
      editForm.getForm().findField('proceso').setValue(rowSelected.data.PRO_UID);
      editForm.getForm().findField('metodo').setValue(rowSelected.data.METHOD);
      editForm.getForm().findField('modelo').setValue(rowSelected.data.MODEL);
      editForm.getForm().findField('parametros').setValue(rowSelected.data.PARAMETERS);
      editForm.getForm().findField('parametros KW').setValue(rowSelected.data.KW_PARAMETERS);
      editForm.getForm().findField('salida').setValue(rowSelected.data.OUTPUT);


      Ext.getCmp("btnUpdateSave").setDisabled(false);

      w = new Ext.Window({
        autoHeight: true,
        width: 440,
        title: "Edit Odoo Step",
        closable: false,
        modal: true,
        id: 'w',
        items: [editForm]
      });
      w.show();
    };
    var btnEdit = new Ext.Action({
      id: "btnEdit",
      text: "Edit",
      iconCls: "button_menu_ext ss_sprite ss_pencil",
      disabled: true,
      handler: EditStepWindow
    });


    //Delete Button Action based on \processmaker\workflow\engine\templates\groups\groupsList.js
    DeleteButtonAction = function () {
      Ext.Msg.confirm(_('ID_CONFIRM'), _('ID_MSG_CONFIRM_DELETE_GROUP'),
        function (btn, text) {
          if (btn == "yes") {
            var rowSelected = grdpnlStep.getSelectionModel().getSelected();

            viewport.getEl().mask(_("ID_PROCESSING"));
            Ext.Ajax.request({
              url: "sc_appAjax",
              params: {
                option: "DELETESTEP",
                id: rowSelected.data.ID,
              },

              success: function (r, o) {
                viewport.getEl().unmask();
                DoSearch();
                btnEdit.disable();  //Disable Edit Button
                btnDelete.disable(); //Disable Delete Button
                PMExt.notify("Success", "Odoo Step deleted");
              },
              failure: function () {
                viewport.getEl().unmask();
              }
            });
          }
        }
      );
    };

    var btnDelete = new Ext.Action({
      id: "btnDelete",
      text: "Delete",
      iconCls: "button_menu_ext ss_sprite ss_delete",
      disabled: true,
      handler: DeleteButtonAction
    });

    DoSearch = function () {
      storeStep.load({ params: { textFilter: txtSearch.getValue() } });
    }

    var btnSearch = new Ext.Action({
      id: "btnSearch",
      text: "Search",
      handler: DoSearch
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

      listeners: {
        specialkey: function (f, e) {
          if (e.getKey() == e.ENTER) {
            DoSearch();
          }
        }
      }
    });

    var btnTextClear = new Ext.Action({
      id: "btnTextClear",
      text: "X",
      ctCls: "pm_search_x_button",
      handler: function () {
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

      listeners: {
        select: function (combo, record, index) {
          pageSize = parseInt(record.data["size"]);

          pagingStep.pageSize = pageSize;
          pagingStep.moveFirst();
        }
      }
    });

    var pagingStep = new Ext.PagingToolbar({
      id: "pagingStep",

      pageSize: pageSize,
      store: storeStep,
      displayInfo: true,
      displayMsg: "Displaying Odoo steps " + "{" + "0" + "}" + " - " + "{" + "1" + "}" + " of " + "{" + "2" + "}",
      emptyMsg: "No steps to display",
      items: ["-", "Page size:", cboPageSize]
    });

    var cmodel = new Ext.grid.ColumnModel({
      defaults: {
        width: 50,
        sortable: true
      },
      columns: [{ id: "ID", dataIndex: "ID", hidden: true },
      { header: "Name", dataIndex: "NOMBRE", align: "left" },
      { header: "Model", dataIndex: "MODEL", width: 25, align: "center" },
      { header: "Method", dataIndex: "METHOD", width: 25, align: "left" }
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

    var grdpnlStep = new Ext.grid.GridPanel({
      id: "grdpnlStep",

      store: storeStep,
      colModel: cmodel,
      selModel: smodel,

      columnLines: true,
      viewConfig: { forceFit: true },
      enableColumnResize: true,
      enableHdMenu: true, //Menu of the column

      tbar: [btnNew, "-", btnEdit, btnDelete, "-", "->", txtSearch, btnTextClear, btnSearch],
      bbar: pagingStep,

      style: "margin: 0 auto 0 auto;",
      width: 550,
      height: 450,
      title: "Odoo Steps",

      renderTo: "divMain",

      listeners: {
      }
    });
    
    var viewport = new Ext.Viewport({
    layout: 'fit',
    autoScroll: false,
    items: [
            grdpnlStep
            ]
    });

    //Initialize events
    storeStepProcess(pageSize, pageSize, 0);

    grdpnlStep.on("rowcontextmenu",
      function (grid, rowIndex, evt) {
        var sm = grid.getSelectionModel();
        sm.selectRow(rowIndex, sm.isSelected(rowIndex));
      },
      this
    );

    grdpnlStep.addListener("rowcontextmenu", onMnuContext, this);

    cboPageSize.setValue(pageSize);
  }
}

//Close PopUp Window
CloseWindow = function () {
  Ext.getCmp('w').hide();
};

Ext.onReady(fullplugin.application.init, fullplugin.application);