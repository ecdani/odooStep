# odooStep - An add-on to connect Process Maker with Odoo

This software is a final career project that intends to integrate a BPM tool (Process Maker) 
with the role of orchestration and a ERP software (Odoo) with the role of operations management. 
Both open source.

This software was developed to support Odoo 9 and Process Maker 3.1 as a Process Maker plugin.

# Installation and configuration

You can download the plugin [here](odooStep-1.tar).

And you can install in your ProcessMaker following this guidelines: [http://wiki.processmaker.com/3.0/Plugins](http://wiki.processmaker.com/3.0/Plugins)

Una vez instalado, se debe configurar en el menú _"Odoo Config"_ con los datos de la instalacion de Odoo.

![Configuration screen](/readme/config.png?raw=true "Configuration screen")

# Creating a step to connect with Odoo at a glance

By clicking on "Odoo Steps Creator" we access the management interface. Here we can see, create and edit the steps of Odoo.

![Odoo Steps Creator](/readme/gest.png?raw=true "Odoo Steps Creator")

If we click on "New" we will see the creation form.

![Creation form](/readme/edit.png?raw=true "Creation form")

The necessary fields are the following:

- **Method**: Of the XML-RPC Odoo API. You can consult [here] (https://www.odoo.com/documentation/9.0/api_integration.html).

- **Model**: Model of Odoo, These models can be consulted in the Odoo itself, activating the developer mode and navigating in the side menu to _"Database structure"_ → _"Models"_ from the main _"Settings"_ menu.

- **Parameters**: Paramtros of the method. You can consult [here] (https://www.odoo.com/documentation/9.0/api_integration.html). The format is like this:

_value, value, value ..._

_value, value, value ..._

- **Parameters KW**: Parameters key-value of the method. You can consult [here] (https://www.odoo.com/documentation/9.0/api_integration.html).
The format is like this:

_key: value, value ..._

_key: value ..._

- **Output**: A ProcessMaker variable previously created to save the return.

You can reference ProcessMaker variables in key-value parameters and parameters using the prefix "@@"

![Parameters example](/readme/params.png?raw=true "Parameters Example")

Once the step is created, it can be assigned to a task like any other step.

# Extending the plugin

The plugin does not have all the combinations of variable types of input and output encoded for the different methods.
For the cases not contemplated (which are many), the plugin can be easily extended both pre and postprocessors.

These functions must be created in the "SosApp.class.php" file within the "SosApp" class. The plugin detects the signature of these functions (such as Drupal hooks) and executes them when appropriate.

The format of the preprocessors is:

public function preprocess__metodo_ ($ p, $ kwp)

And of the postprocessors:

public function postprocess__metodo___type_ ($ output)

There are several of these functions already written in the class that can serve as an example.

# License

GNU Affero General Public License v3.0

You can check it [here](https://github.com/ecdani/odooStep/blob/master/LICENSE).

Please consider contributing to the code if it was helpful in your purpose.
