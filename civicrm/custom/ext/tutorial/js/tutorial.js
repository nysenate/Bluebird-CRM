(function($, _, ts) {
  CRM.vars.tutorial = CRM.vars.tutorial || {};
  CRM.vars.tutorial.items = CRM.vars.tutorial.items || {};
  CRM.vars.tutorial.menuItems = [];

  var route, poll, supportMenuName,
    tutorials = CRM.vars.tutorial.items;

  $(document).on('crmLoad', '#civicrm-menu', function() {
    if (tutorials) {
      supportMenuName = CRM.menubar.getItem('Support') ? 'Support' : (CRM.menubar.getItem('Help') ? 'Help' : 'Home');
      // For dynamic pages using hash-based routing, show/hide tutorials when route changes
      $(window).on('hashchange', loadTutorials);
      loadTutorials();
      $('#civicrm-menu').on('click', 'a[href="#tutorial-view"]', function(e) {
        e.preventDefault();
        startTutorial($(this).parent().data('name').split(':')[1]);
      });
    }
  });

  // Copied from hopscotch:getStepTargetHelper
  function getTarget(target) {
    var parts = target.split(' '),
      context = document;
    // Get element from within iframe
    if ($(parts[0]).is('iframe')) {
      context = $(parts[0]).contents();
      target = parts.slice(1).join(' ');
    }
    var result = $(target, context);
    return result.length ? result[0] : null;
  }

  function loadTutorials() {
    var autoStartTutorial,
      viewMenuItems = [],
      editMenuItems = [],
      hash = (window.location.hash || '').substr(1),
      pos = CRM.menubar.getItem('tutorial_add') ? -2 : -1;
    // Ignore query after route
    if (hash.indexOf('?') > -1) {
      hash = hash.split('?')[0];
    }
    if (hash === route) {
      return;
    }
    route = hash;
    if (poll) {
      clearInterval(poll);
      poll = null;
    }

    function compare(tutorialUrl) {
      var tutorialRoute = tutorialUrl.split('#')[1] || '';
      if (tutorialRoute === hash || !tutorialRoute) {
        return tutorialRoute === hash;
      }
      tutorialRoute = _.trim(tutorialRoute, '/!');
      var existingRoute = _.trim(hash, '/!'),
        search = _.escapeRegExp(tutorialRoute).replace(/\\\*/g, '.*'),
        searchExp = new RegExp(search);
      return searchExp.test(existingRoute);
    }

    _.each(CRM.vars.tutorial.menuItems, function(item) {
      CRM.menubar.removeItem('tutorial_view:' + item);
      CRM.menubar.removeItem('tutorial_edit:' + item);
    });
    CRM.vars.tutorial.menuItems = [];
    _.each(tutorials, function(tutorial, id) {
      if (compare(tutorial.url)) {
        CRM.vars.tutorial.menuItems.push(id);
        viewMenuItems.push({
          label: tutorial.title,
          name: 'tutorial_view:' + id,
          url: '#tutorial-view',
          icon: 'crm-i fa-play',
          separator: viewMenuItems.length ? null: 'top'
        });
        if (CRM.checkPerm('administer CiviCRM')) {
          editMenuItems.push({
            label: ts('Edit %1', {1: tutorial.title}),
            name: 'tutorial_edit:' + id,
            url: '#tutorial-edit',
            icon: 'crm-i fa-pencil-square',
            separator: editMenuItems.length ? null : 'top'
          });
        }
        if (tutorial.auto_start && !tutorial.viewed) {
          autoStartTutorial = tutorial;
        }
      }
    });
    if (CRM.vars.tutorial.menuItems.length) {
      CRM.menubar.addItems(pos, supportMenuName, viewMenuItems.concat(editMenuItems));
    }
    // Poll the dom at intervals to see if the element of the first step is present
    if (autoStartTutorial) {
      poll = setInterval(function() {
        if (getTarget(autoStartTutorial.steps[0].target)) {
          startTutorial(autoStartTutorial.id);
          clearInterval(poll);
          poll = null;
        }
      }, 500);
    }
  }

  function startTutorial(id) {
    hopscotch.endTour()
     .resetDefaultI18N()
     .resetDefaultOptions();

    var defaults = {
      showPrevButton: true,
      i18n: {
        nextBtn: ts('Next'),
        doneBtn: ts('Done'),
        prevBtn: ts('Back')
      }
    };
    defaults.onClose = defaults.onEnd = endTutorial;

    tutorials[id].viewed = true;
    var tutorial = _.extend(defaults, _.cloneDeep(tutorials[id]));

    // Place icons in the step number circle if provided
    tutorial.i18n.stepNums = _.map(tutorial.steps, function(step, i) {
      return step.icon ? '<i class="crm-i ' + step.icon + '"></i>' : i + 1;
    });

    hopscotch.startTour(tutorial);
    CRM.api3('Tutorial', 'mark', {id: id});
  }

  function endTutorial() {
    var supportMenu = $('[data-name="' + supportMenuName + '"]', '#civicrm-menu');
    if (supportMenu.length) {
      window.setTimeout(function() {
        hopscotch.startTour({
          id: 'tutorial-closed',
          steps: [
            {
              target: $(supportMenu)[0],
              placement: 'bottom',
              nextOnTargetClick: true,
              content: ts('View this tutorial again or find more ways to learn and get help from this menu.')
            }
          ],
          i18n: {
            doneBtn: ts('Got it'),
            stepNums: ['<i class="crm-i fa-info"></i>']
          }
        });
      }, 200);
    }
  }

})(CRM.$, CRM._, CRM.ts('org.civicrm.tutorial'));
