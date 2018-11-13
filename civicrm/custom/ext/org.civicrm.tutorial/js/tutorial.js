(function($, _, ts) {
  var TEXT_NODE = 3,
    route,
    poll;

  CRM.vars.tutorial.insertIntoMenu = function(tutorial, id) {
    var viewMenu = $('.menu-item a[href$="#tutorial-start"]').closest('li'),
      editMenu = $('.menu-item a[href$="#tutorial-edit"]').closest('li'),
      viewLink = viewMenu.first().find('a'),
      editLink = editMenu.first().find('a');
    if (viewMenu.find('a[data-tutorial="' + id + '"]').length) {
      viewLink = viewMenu.find('a[data-tutorial="' + id + '"]');
      editLink = editMenu.find('a[data-tutorial="' + id + '"]');
    } else if (viewLink.attr('data-tutorial')) {
      viewLink = viewMenu.first().clone().insertAfter(viewMenu.last()).hover(hoverMenu).find('a');
      if (editMenu.length) {
        editLink = editMenu.first().clone().insertAfter(editMenu.last()).hover(hoverMenu).find('a');
      }
    }
    viewLink
      .attr('data-tutorial', id)
      .off('click')
      .click(clickStart)
      .contents()
      .filter(function() {
        return this.nodeType === TEXT_NODE;
      })
      .replaceWith(tutorial.title);
    editLink
      .attr('data-tutorial', id)
      .contents()
      .filter(function() {
        return this.nodeType === TEXT_NODE;
      })
      .replaceWith(ts('Edit') + ' "' + tutorial.title + '"');
    viewLink.closest('li').show();
    editLink.closest('li').show();
  };

  $('#civicrm-menu').ready(function() {
    var viewMenu = $('.menu-item a[href$="#tutorial-start"]').closest('li'),
      editMenu = $('.menu-item a[href$="#tutorial-edit"]').closest('li'),
      tutorials = CRM.vars && CRM.vars.tutorial && CRM.vars.tutorial.items;
    if (viewMenu.length) {
      if (!tutorials) {
        viewMenu.hide();
        editMenu.hide();
      } else {
        _.each(tutorials, CRM.vars.tutorial.insertIntoMenu);
        $(window).on('hashchange', checkHash);
        checkHash();
      }
    }
  });

  /**
   * Mimic the clunky hover effect in the menu
   */
  function hoverMenu(e) {
    $(this).toggleClass('active', e.type === 'mouseenter');
  }

  /**
   * For dynamic pages using hash-based routing, show/hide tutorials when route changes
   */
  function checkHash() {
    var hash = (window.location.hash || '').substr(1),
      autoStartTutorial = null;
    // Ignore query after route
    if (hash && hash.indexOf('?') > -1) {
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
    _.each(CRM.vars.tutorial.items, function(tutorial, id) {
      var match = (tutorial.url.split('#')[1] || '') === hash;
      $('.menu-item a[data-tutorial="' + id + '"]').closest('li').toggle(match);
      if (match && tutorial.auto_start && !tutorial.viewed) {
        autoStartTutorial = tutorial;
      }
    });
    // Poll the dom at intervals to see if the element of the first step is present
    if (autoStartTutorial) {
      poll = setInterval(function() {
        if ($(autoStartTutorial.steps[0].target).length) {
          startTutorial(autoStartTutorial.id);
          clearInterval(poll);
          poll = null;
        }
      }, 500);
    }
  }

  function clickStart(e) {
    e.preventDefault();
    startTutorial($(this).data('tutorial'));
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

    CRM.vars.tutorial.items[id].viewed = true;
    var tutorial = _.extend(defaults, _.cloneDeep(CRM.vars.tutorial.items[id]));

    // Place icons in the step number circle if provided
    tutorial.i18n.stepNums = _.map(tutorial.steps, function(step, i) {
      return step.icon ? '<i class="crm-i ' + step.icon + '"></i>' : i + 1;
    });

    hopscotch.startTour(tutorial);

    CRM.api3('Tutorial', 'mark', {id: id});
  }

  function endTutorial() {
    var supportMenu = $('.menumain a[href$="#tutorial-start"]').closest('.menumain');
    if (supportMenu.length) {
      window.setTimeout(function() {
        hopscotch.startTour({
          id: 'tutorial-closed',
          steps: [
            {
              target: $(supportMenu)[0],
              placement: 'bottom',
              nextOnTargetClick: true,
              content: ts('To see this tutorial again, or for more ways to learn and get help, open the Support menu.')
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
