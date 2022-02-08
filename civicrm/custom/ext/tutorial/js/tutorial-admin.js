(function($, _, ts) {
  var tutorial,
    oldIndex,
    currentStep = 0,
    ENTER_KEY = 13,
    saved = true,
    newTutorial = {},
    resourcesLoaded,
    supportMenuName,
    templates = {
      admin_main_tpl: null,
      admin_step_tpl: null
    },
    stepDefaults = {
      target: '',
      title: '',
      placement: 'bottom',
      content: '',
      icon: null
    };

  $(document).on('crmLoad', '#civicrm-menu', function() {
    supportMenuName = CRM.menubar.getItem('Support') ? 'Support' : (CRM.menubar.getItem('Help') ? 'Help' : 'Home');
    $('#civicrm-menu')
      .on('click', 'a[href="#tutorial-view"]', close)
      .on('click', 'a[href="#tutorial-edit"],a[href="#tutorial-add"]', function(e) {
        e.preventDefault();
        editTour($(this).parent().data('name').split(':')[1]);
      });
    CRM.menubar.addItems(-1, supportMenuName, [{
      label: ts('Create new tutorial'),
      name: 'tutorial_add',
      url: '#tutorial-add',
      icon: 'crm-i fa-plus-circle',
      separator: 'top'
    }]);
  });

  function setDefaults(id) {
    currentStep = 0;
    tutorial = id && CRM.vars.tutorial.items && CRM.vars.tutorial.items[id] || newTutorial;
    var defaults = {
      id: null,
      title: ts('Learn about this screen'),
      viewed: true,
      saved: true,
      auto_start: false,
      steps: [],
      groups: [],
      domain: null,
      source: null
    };
    _.each(defaults, function(val, key) {
      if (typeof tutorial[key] === 'undefined') {
        tutorial[key] = val;
      }
    });
    if (!tutorial.id) {
      tutorial.url = defaultUrl();
    }
    if (!tutorial.steps.length) {
      addStep();
    }
    setSaved(tutorial.saved);
  }

  function setSaved(val) {
    tutorial.saved = val;
    $('#civitutorial-admin-save').prop('disabled', val);
    $('#civitutorial-admin').toggleClass('tutorial-saved', val);
  }

  function addStep() {
    var step = _.extend({}, stepDefaults);
    tutorial.steps.push(step);
    return step;
  }

  function createStep() {
    var num = tutorial.steps.length;
    var step = addStep();
    setSaved(false);
    renderStep(step, num);
    refreshAccordions(-1);
  }

  function deleteStep() {
    var $step = $(this).closest('.civitutorial-step'),
      index = $step.index();
    tutorial.steps.splice(index, 1);
    $step.remove();
    setSaved(false);
    refreshAccordions();
  }

  function refreshAccordions(activeIndex) {
    $('#civitutorial-steps').accordion('refresh').find('h5').off('keydown');
    if (typeof activeIndex === 'number') {
      $('#civitutorial-steps').accordion('option', 'active', activeIndex);
    }
  }

  function defaultUrl() {
    var path = CRM.vars.tutorial.urlPath,
      searchParams = new URLSearchParams(window.location.search),
      hash = !window.location.hash || window.location.hash === '#' ? '' : window.location.hash,
      queryWhitelist = ['action'],
      query = '';
    _.each(queryWhitelist, function(param) {
      if (searchParams.get(param)) {
        query += (query ? '&' : '?') + param + '=' + searchParams.get(param);
      }
    });
    if (hash && hash.indexOf('?') > -1) {
      hash = hash.split('?')[0];
    }
    if (hash) {
      hash = hash.replace(/\/[0-9]+/g, '/*');
    }
    return path + query + hash;
  }

  function close() {
    $('#civitutorial-admin, #civitutorial-overlay').remove();
    $('body').removeClass('civitutorial-admin-open');
  }

  function cancel() {
    hopscotch.endTour();
    close();
    if (!tutorial.saved) {
      hopscotch.startTour({
        id: 'admin-unsaved',
        steps: [
          {
            target: '#civicrm-menu li[data-name="' + supportMenuName + '"]',
            placement: 'bottom',
            nextOnTargetClick: true,
            title: ts('Unsaved Changes.'),
            content: ts('Your tutorial has not been saved. You can get back to editing it from this menu; if you leave or refresh this page your changes will be lost.')
          }
        ],
        i18n: {
          doneBtn: ts('Ok'),
          stepNums: ['<i class="crm-i fa-info"></i>']
        }
      });
    }
  }

  function save(e) {
    e.preventDefault();
    setSaved(true);
    CRM.api3('Tutorial', 'create', tutorial, true).done(postSave);
  }

  function postSave(saved) {
    $('#civitutorial-admin-delete').prop('disabled', false);
    tutorial.domain = null;
    if (!tutorial.id && saved.id) {
      tutorial.id = saved.id;
      newTutorial = {};
      CRM.menubar.addItems(-2 - CRM.vars.tutorial.menuItems.length, supportMenuName,[{
        label: tutorial.title,
        name: 'tutorial_view:' + saved.id,
        url: '#tutorial-view',
        icon: 'crm-i fa-play',
        separator: CRM.vars.tutorial.menuItems.length ? null: 'top'
      }]);
      CRM.menubar.addItems(-2, supportMenuName, [{
        label: ts('Edit %1', {1: tutorial.title}),
        name: 'tutorial_edit:' + saved.id,
        url: '#tutorial-edit',
        icon: 'crm-i fa-pencil-square',
        separator: CRM.vars.tutorial.menuItems.length ? null : 'top'
      }]);
      CRM.vars.tutorial.items[saved.id] = tutorial;
      CRM.vars.tutorial.menuItems.push(saved.id);
    } else if (saved.id) {
      CRM.menubar.updateItem({
        label: tutorial.title,
        name: 'tutorial_view:' + saved.id
      });
      CRM.menubar.updateItem({
        label: ts('Edit %1', {1: tutorial.title}),
        name: 'tutorial_edit:' + saved.id
      });
    }
  }

  function deleteTutorial() {
    CRM.confirm({
      title: tutorial.source ? ts('Revert Tutorial') : ts('Delete Tutorial'),
      message: tutorial.source?
        ts('Local changes to this tutorial will be deleted and the original copy from %1 will be restored.', {'1': tutorial.source}) :
        ts('This tutorial will be completely removed. This action cannot be undone.')
    }).on('crmConfirm:yes', function() {
      close();
      hopscotch.endTour();
      var params = {id: tutorial.id};
      if (tutorial.source) {
        CRM.api3([['Tutorial', 'delete', params], ['Tutorial', 'get', params]], true).done(function(result) {
          newTutorial = {};
          CRM.vars.tutorial.items[params.id] = result[1].values[params.id];
          CRM.vars.tutorial.items[params.id].viewed = true;
        });
      } else {
        delete CRM.vars.tutorial.items[params.id];
        _.pull(CRM.vars.tutorial.menuItems, params.id);
        CRM.menubar.removeItem('tutorial_view:' + params.id);
        CRM.menubar.removeItem('tutorial_edit:' + params.id);
        CRM.api3('Tutorial', 'delete', params, true);
      }
    });
  }

  function openPreview() {
    hopscotch.endTour();
    if (tutorial.steps[currentStep]) {
      var step = _.cloneDeep(tutorial.steps[currentStep]);
      step.title = step.title || ' ';
      step.content = step.content || ' ';
      if (step.target) {
        hopscotch.startTour({
          id: 'preview-tour-step-' + currentStep,
          steps: [step],
          i18n: {stepNums: [step.icon ? '<i class="crm-i ' + step.icon + '"></i>' : currentStep + 1]}
        });
      }
    }
  }

  function selectTarget(e) {
    hopscotch.endTour();
    e.stopImmediatePropagation();
    $('body')
      .addClass('civitutorial-select-target')
      .on('click.tutorialAdmin', onTargetClick);
  }

  function doneSelecting() {
    $('body')
      .removeClass('civitutorial-select-target')
      .off('.tutorialAdmin');
  }

  function onTargetClick(e) {
    doneSelecting();
    if ($(e.target).closest('#civitutorial-admin').length < 1) {
      var element = $(document.elementFromPoint(e.clientX, e.clientY)),
        container = '';
      if (element.is('iframe')) {
        container = 'iframe' + (element.attr('id') ? '#' + element.attr('id') : '') + ' ';
        var offset = element.offset();
        element = $(element[0].contentWindow.document.elementFromPoint(e.clientX - offset.left, e.clientY - offset.top));
      }
      var target = pickBestTarget(element);
      e.preventDefault();
      $('.civitutorial-step-content').eq(currentStep).find('[name=target]').val(container + target).change();
    }
  }

  function getSelectorClass($target) {
    var result = '',
      classString = _.trim($target.attr('class') || ''),
      classes = classString ? classString.split(' ') : [];
    classes = _.filter(classes, function(name) {
      var prefix = name.substring(0, 3);
      return prefix !== 'ng-' && prefix !== 'ui-';
    });
    return classes.length ? '.' + classes.join('.') : '';
  }

  function pickBestTarget($target, child) {
    var id, selector,
      select2 = $target.closest('.select2-container'),
      classes = getSelectorClass($target),
      name = $target.attr('name');
    child = child || '';
    if ($target.is('#civicrm-menu *')) {
      return '#civicrm-menu li[data-name="' + $target.closest('li[data-name]').data('name') + '"]';
    } else if (select2.length) {
      return pickBestTarget(select2.parent(), ' .select2-container');
    } else if ($target.is('[id] > a')) {
      return pickBestTarget($target.parent());
    } else if ($target.attr('id')) {
      return '#' + $target.attr('id') + child;
    } else if ((name || classes) && !$target.is('span, strong, i, b, em, p, hr')) {
      id = $target.closest('[id]').attr('id');
      selector = (id ? '#' + id + ' ' : '') + (name ? "[name='" + name + "']" : classes);
      if ($(selector).index($target) > 0) {
        selector += ':eq(' + $(selector).index($target) + ')';
      }
      return selector + child;
    } else {
      return pickBestTarget($target.parent());
    }
  }

  function updateFieldVal($field, values) {
    var val,
      fieldName = $field.attr('name');
    if ($field.is(':checkbox')) {
      val = $field.is(':checked');
    } else if ($field.is('[contenteditable]')) {
      val = $field.html();
    } else {
      val = $field.val();
    }
    if ($field.is('.crm-form-entityref')) {
      val = val ? val.split(',') : [];
    }
    values[fieldName] = val;
    if (fieldName === 'target' || fieldName === 'placement') {
      openPreview();
    }
    setSaved(false);
  }

  function updateIcon() {
    var val = $('.civitutorial-step').eq(currentStep).find('[name=icon]').val(),
      icon = val ? '<i class="crm-i ' + val + '"></i>' : '';
    $('.civitutorial-step').eq(currentStep).find('.civitutorial-step-icon').html(icon);
    $('.hopscotch-bubble-number').html(icon || currentStep+1);
  }

  function sortStart(e, ui) {
    oldIndex = $(ui.item).index();
  }

  function sortStop(e, ui) {
    var item = tutorial.steps[oldIndex],
      newIndex = $(ui.item).index();
    if (newIndex !== oldIndex) {
      tutorial.steps.splice(oldIndex, 1);
      tutorial.steps.splice(newIndex, 0, item);
    }
    currentStep = $('.civitutorial-step-title.ui-accordion-header-active', '#civitutorial-steps').closest('.civitutorial-step').index();
    updateIcon();
    refreshAccordions();
  }

  function renderStep(step, num) {
    $('#civitutorial-steps')
      .append(templates.admin_step_tpl(_.extend({num: num+1}, stepDefaults, step)))
      .find('.crm-icon-picker').not('.iconpicker-widget').crmIconPicker();
  }

  function loadResources() {
    if (!resourcesLoaded) {
      var cssLoaded = $.Deferred(),
        requests = [
          cssLoaded,
          $().crmIconPicker ? $.Deferred().resolve() : CRM.loadScript(CRM.config.resourceBase + 'js/jquery/jquery.crmIconPicker.js')
        ],
        cssFile = document.createElement('link');
      cssFile.type = 'text/css';
      cssFile.rel = 'stylesheet';
      cssFile.onload = cssLoaded.resolve;
      cssFile.href = CRM.vars.tutorial.basePath + 'css/tutorial-admin.css?' + CRM.config.resourceCacheCode;
      $.each(templates, function(file) {
        var request = $.Deferred();
        requests.push(request);
        $.get(CRM.vars.tutorial.basePath + 'html/' + file + '.html?' + CRM.config.resourceCacheCode)
          .done(function(html) {
            templates[file] = _.template(html, {imports: {ts: ts}});
            request.resolve();
          });
      });
      $('body')[0].appendChild(cssFile);
      resourcesLoaded = $.when.apply($, requests);
    }
    return resourcesLoaded;
  }

  function editTour(id) {
    close();
    hopscotch.endTour();
    $('body').append('<form id="civitutorial-admin" class="crm-container"></form><div id="civitutorial-overlay"></div>');
    setDefaults(id);
    loadResources().done(function() {
      $('#civitutorial-admin')
        .css('padding-top', '' + ($('#civicrm-menu').height() + 12) + 'px')
        .html(templates.admin_main_tpl(tutorial))
        .submit(save);
      $('#civitutorial-admin-close').click(cancel);
      $('#civitutorial-admin-delete').click(deleteTutorial);
      $('#civitutorial-add-step').click(createStep);
      $('#civitutorial-field-groups').crmEntityRef({
        entity: 'Group',
        api: {id_field: 'name', params: {is_hidden: 0, is_active: 1}},
        select: {placeholder: ts('Groups'), multiple: true, allowClear: true, minimumInputLength: 0}
      });
      $('input[id^="civitutorial-field"]').change(function() {
        updateFieldVal($(this), tutorial);
      });
      _.each(tutorial.steps, renderStep);
      $('#civitutorial-steps')
        .on('change input', ':input[name], [contenteditable]', function() {
          var name = $(this).attr('name'),
            index = $(this).closest('.civitutorial-step').index();
          if (index === currentStep && (name === 'title' || name === 'content')) {
            $('.hopscotch-bubble-container .hopscotch-' + name).html(name === 'title' ? $(this).html() : $(this).val());
          }
          updateFieldVal($(this), tutorial.steps[index]);
        })
        .on('change', '[name=icon]', updateIcon)
        .on('keydown', '[contenteditable]', function(e) {
          if (e.which === ENTER_KEY) {
            e.preventDefault();
            $(this).blur();
          }
        })
        .on('click focus', '[name=target]', selectTarget)
        .on('keydown', '[name=target]', doneSelecting)
        .on('click', '.civitutorial-step-remove', deleteStep)
        .on('accordionbeforeactivate', function(e, ui) {
          currentStep = $(ui.newHeader).closest('.civitutorial-step').index();
          openPreview();
        })
        .sortable({
          axis: 'y',
          handle: '.civitutorial-step-title',
          cancel: '.civitutorial-step-remove, [contenteditable]',
          start: sortStart,
          update: sortStop
        })
        .accordion({
          icons: false,
          header: '.civitutorial-step-title'
        }).find('h5').off('keydown');
      $('body').addClass('civitutorial-admin-open');
    });
    openPreview();
  }

})(CRM.$, CRM._, CRM.ts('org.civicrm.tutorial'));
