(function(angular, $, _) {

  /**
   * The class CrmMosaicoIframe allows you to instantiate and manage a
   * full-screen IFRAME with embedded Mosaico runtime.
   */
  angular.module('crmMosaico').factory('CrmMosaicoIframe', function(crmUiAlert, $q, $timeout, $rootScope) {

    /**
     * @param Object newOptions
     *  - dimensions: function()
     *  - url: string (optional)
     *  - actions: Object
     *    - save: function(ko, viewModel)
     *    - test: function(ko, viewModel)
     *    - close: function(ko, viewModel) (an alias for "save")
     *  - model: Object. Must have "metadata+content" or "template"
     *    - metadata: string, JSON-encoded
     *    - content: string, JSON-encoded
     *    - template: string, relative file path
     */
    return function CrmMosaicoIframe(options){
      var cfg = {
        url: CRM.url('civicrm/mosaico/iframe', 'snippet=1'),
        dimensions: function resize() {
          var c = CRM.crmMosaico || {};
          var top = 0, left = 0, width = $(window).width(), height = $(window).height();
          if (c.topNav && $(c.topNav).length > 0) {
            top = $(c.topNav).outerHeight() + $(c.topNav).position().top;
            height -= top;
          }
          if (c.leftNav && $(c.leftNav).length > 0) {
            left = $(c.leftNav).width();
            width -= left;
          }
          return {position: 'fixed', left: left + 'px', top: top + 'px', width: width + 'px', height: height + 'px'};
        }
      };
      angular.extend(cfg, options);

      var model = cfg.model, actions = cfg.actions;
      var isVisible = false, $iframe = null, iframe = null;

      if (actions.save && actions.close) {
        throw "Error: Save and Close actions are mutually exclusive";
      }

      var oldOverflow = null;
      function scrollHide() {
        if (oldOverflow === null) {
          oldOverflow = $('body').css('overflow');
          $(document).on('dialogclose', scrollRefresh); // jQuery dialog bug
        }
        $('body').css('overflow', 'hidden');
      }
      function scrollRestore() {
        if (oldOverflow !== null) {
          $(document).off('dialogclose', scrollRefresh); // jQuery dialog bug
          $('body').css('overflow', oldOverflow);
        }
        oldOverflow = null;
      }
      function scrollRefresh() { $('body').css('overflow', 'hidden'); }

      function onResize() {
        if ($iframe) $iframe.css(cfg.dimensions());
      }

      this.render = function render() {
        $iframe = $('<iframe frameborder="0" class="ui-front">');
        $('body').append($iframe);
        onResize();
        $(window).on('resize', onResize);

        iframe = $iframe[0];
        iframe.setAttribute('src', cfg.url);

        return this;
      };

      // @return Promise<null>
      this.open = function open() {
        var dfr = $q.defer();

        if ($iframe) {
          dfr.resolve();
          return dfr.promise;
        }

        window.top.crmMosaicoIframe = function(newWindow, Mosaico, config, plugins) {
          plugins.push(function(viewModel) {
            mosaicoPlugin(newWindow.ko, viewModel);
          });

          var ok = true;
          if (model.content) {
            Mosaico.start(config, undefined, JSON.parse(model.metadata), JSON.parse(model.content), plugins);
          } else if (model.template) {
            Mosaico.start(config, model.template, undefined, undefined, plugins);
          } else {
            crmUiAlert({text: 'Cannot edit mailing'});
            ok = false;
          }

          $timeout(function(){
            if (ok) dfr.resolve();
            else dfr.reject();
          }, 150);
        };

        this.render();
        this.show();
        return dfr.promise;
      };

      this.hide = function hide() {
        isVisible = false;
        if ($iframe) {
          scrollRestore();
          $iframe.hide();
        }
        return this;
      };

      this.show = function show() {
        isVisible = true;
        if ($iframe) {
          scrollHide();
          onResize();
          $iframe.show();
        }
        return this;
      };

      this.destroy = function destroy() {
        this.hide();
        $(window).off('resize', onResize);
        if ($iframe) $iframe.remove();
        iframe = $iframe = null;
        return this;
      };

      // See https://github.com/voidlabs/mosaico/wiki/Mosaico-Plugins
      // Generally: Implement the in-dialog "Save" and "Test" buttons.
      function mosaicoPlugin(ko, viewModel) {
        // Clicking the default link isn't very useful in IFRAME context.
        viewModel.logoUrl = null;

        function mkCmd(name, callback) {
          var cmd = {
            name: name, // l10n happens in the template
            enabled: ko.observable(true)
          };
          cmd.execute = function() {
            cmd.enabled(false);
            $rootScope.$apply(function(){
              callback(ko, viewModel);
            });
            cmd.enabled(true);
          };
          return cmd;
        }

        if (actions.save) {
          viewModel.save = mkCmd("Save", actions.save);
        }
        if (actions.close) { // pretend like Mosaico has a "Close" action.
          viewModel.save = mkCmd("Close", actions.close);
        }
        if (actions.test) {
          viewModel.test = mkCmd("Test", actions.test);
        }
      }

    };
  });


})(angular, CRM.$, CRM._);
