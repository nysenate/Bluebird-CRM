# APIv3

CiviCRM's APIv3 framework provides a way for consumers to manage data and call services.  APIv3 can be called in many ways
(such as PHP, REST, and CLI). For a general introduction, see [APIv3 Intro](https://docs.civicrm.org/dev/en/latest/api/).

This extension defines a few new APIs:

* `Job.mosaico_migrate`: If you need to perform an automated migration from v1.x to v2.x, use this API to copy all
  v1.x templates to v2.x.
* `Job.mosaico_purge`: If you need to perform an automated migration from v1.x to v2.x, use this API to clear out the
  old v1.x templates.
* `MosaicoTemplate.*`: This API provides access to the user-configurable templates.  It supports all standard CRUD
  actions (`get`, `create`, `delete`etc). Its data-structure closely adheres to Mosaico's canonical storage format.
* `MosaicoTemplate.replaceurls`: When a database is restored in a server with a different URL, templates will need to be updated. The `replaceurls` method facilitates that migration task:

```
cv api MosaicoTemplate.replaceurls from_url="http://old.server.org" to_url="https://new.server.org"
```

* `MosaicoBaseTemplate.get`: This API provides access to the *base templates*. A base template (such as `versafix-1`)
  defines the HTML blocks that are available for drag/drop in the Mosaico palette. Note: This API is *read-only*.
  To define custom templates, see the section on "Base templates".

# Base templates

A base template defines the HTML blocks that are available for drag/drop in the Mosaico palette. The standard distribution
of Mosaico includes a few base templates, such as `versafix-1`.

The upstream Mosaico project provides a tutorial on how to develop a custom base template:

https://github.com/voidlabs/mosaico/blob/master/templates/tutorial/mosaico-tutorial.md

The new template is essentially a folder with a couple standard files.  Once you've developed these files, you'll need
a way to *deploy* the folder, such as:

* __Drop-in folder__: On any site, create a folder dedicated to custom base templates.  By default, this is
  `[civicrm.files]/mosaico_tpl`.  (`[civicrm.files]` is a variable that resolves somewhere under the CMS's data
  folder.) For example, if you deployed a template `foobar` on a typical Drupal 7 site, the full path to the template HTML
  might be `/var/www/sites/default/files/civicrm/mosaico_tpl/foobar/template-foobar.html`.  (The folder name can be
  customized in "Administer => CiviMail => Mosaico Settings".)
* __Extension__: Create a CiviCRM extension and put the template in it. Use the [hook system](https://docs.civicrm.org/dev/en/latest/hooks/) to register the template via `hook_civicrm_mosaicoBaseTemplates`. For example, this snippet shows how an extension named `mymodule` can register a base-template named `foobar`:
  ```php
  <?php
  use CRM_Mymodule_ExtensionUtil as E;
  function mymodule_civicrm_mosaicoBaseTemplates(&$templates) {
    $templates['foobar'] = [
      'name' => 'foobar',
      'title' => 'Foo Bar',
      'path' => E::url('foobar/template-foobar.html'),
      'thumbnail' => E::url('foobar/edres/_full.png'),
    ];
  }
  ```

# Delivery

After designing a mailing, email messages are composed and delivered through FlexMailer.  To programmaticaly tap into the
composition and delivery process, see the [FlexMailer developer docs](https://docs.civicrm.org/flexmailer/en/latest/).

# Hooks

See the CiviCRM documentation for more general information about [hooks](https://docs.civicrm.org/dev/en/latest/hooks/).

## hook_civicrm_mosaicoConfig

This hook can be implemented to modify the default mosaico WYSIWYG configuration. This is useful if you want to restrict the buttons available on the editing toolbar. The current configuration is passed in as a variable, which can then be modified.

Example - remove some buttons from the toolbar, customise a configuration setting:
```
function example_civicrm_mosaicoConfig(&$config) {
  $config['tinymceConfig']['forced_root_block'] = FALSE;
  $config['tinymceConfigFull']['plugins'] = ['link paste lists code civicrmtoken'];
  $config['tinymceConfigFull']['toolbar1'] = 'bold italic removeformat | link unlink | civicrmtoken | pastetext code';
}
```

## hook_civicrm_mosaicoStyleUrlsAlter(&$styleUrls)

`$styleUrls` is an array of URLs for stylesheets to be loaded in the Mosaico composer interface. Alter (add, remove, change) array elements as needed.

Example from  [Campaign Advocacy](https://github.com/twomice/civicrm-campaignadvocacy):

```php
function campaignadv_civicrm_mosaicoStyleUrlsAlter(&$styleUrls) {
  $res = CRM_Core_Resources::singleton();

  // Include our own abridged styles from jquery-ui 'smoothness' theme, as
  // required for our jquery-ui dialog, but which don't conflict with Mosaico.
  $styleUrls[] = $res->getUrl('campaignadv', 'css/jquery-ui-smoothness-partial.css', TRUE);
}
```

## hook_civicrm_mosaicoScriptUrlsAlter(&$scriptUrls)

`$scriptUrls` is an array of URLs for JavaScript files to be loaded in the Mosaico composer interface. Alter (add, remove, change) array elements as needed.

Example from  [Campaign Advocacy](https://github.com/twomice/civicrm-campaignadvocacy):

```php
function campaignadv_civicrm_mosaicoScriptUrlsAlter(&$scriptUrls) {
  // Include our own JS (this url generates dynammic JS containing apprpopriate settings per session).
  $url = CRM_Utils_System::url('civicrm/campaignadv/mosaico-js', '', TRUE, NULL, NULL, NULL, NULL);
  $scriptUrls[] = $url;
}
```

## hook_civicrm_mosaicoPlugin(&$plugins)

This hook can be implemented to add custom JS plugins to the Mosaico object during initialization, see [here](https://github.com/voidlabs/mosaico/wiki/Mosaico-Plugins) on how plugins work in Mosaico.

Example usage:
```
/**
 * Implements hook_civicrm_mosaicoPlugin().
 */
function example_civicrm_mosaicoPlugin(&$plugins) {
    $plugins[] = _example_alert_plugin();
}
  
/**
 * Example plugin to display alert on init and dispose.
 */  
function _example_alert_plugin(){
    $plugin = <<< JS
        function(vm) {
            alert("test-plugin");
            return {
                    init: function(vm){alert("init");}, 
                    dispose:  function(vm){alert("dispose");}
                }  
        }
    JS;

    return $plugin;
}
```
