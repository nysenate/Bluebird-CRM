(function(angular, $, _) {

  /**
   * The class CrmMosaicoSyncMonitor monitors the Mosaico IFRAME and decides when to fire synchronization events.
   * It follows these rules:
   *
   * - The user alternates between periods of being "idle" (no clicking) or "active" (clicking).
   * - Most clicks are interpreted as activity.
   * - Some clicks (arrow keys, home, end, mouse-scrolling) do not count as activity.
   * - Fire `onSync()` after a (moderately) short period of idleness (eg `idleTimeout=5000`).
   * - Fire `onSync()` after a (moderately) long period of activity (eg `activeTimeout=60000`).
   * - Do not fire `onSync()` while there is active input (mouse-down or key-down).
   */
  angular.module('crmMosaico').factory('CrmMosaicoSyncMonitor', function($interval) {

    // var verbose = console.log;
    var verbose = function() {};

    /**
     * @param Object options
     * - interval (int, ms): How frequently to poll and decide about whether to sync.
     * - idleTimeout (int, ms): After $X period of idle usage, fire a sync.
     * - activeTimeout (int, ms): After $X period of active usage, fire a sync.
     * - onSync (function): The function to call periodically.
     */
    return function CrmMosaicoSyncMonitor(options) {
      var defaults = {
        interval: 500,
        idleTimeout: 4 * 1000,
        activeTimeout: 2 * 60 * 1000,
        onSync: function() {}
      };
      angular.extend(this, defaults, options);

      var monitor = this, intervalHandle = null, startCount = 0;
      var isKeyDown, isMouseDown, lastInput, lastSync;

      function ding(e) {
        lastInput = (new Date()).getTime();
        verbose('CrmMosaicoSyncMonitor: activity: ', lastInput, e.type);
      }
      function onMouseDown(e) { isMouseDown = true; ding(e); }
      function onMouseUp(e) { isMouseDown = false; ding(e); }
      function onKeyDown(e) { isKeyDown = true; ding(e); }
      function onKeyUp(e) {
        isKeyDown = false;
        // ignore home/end/left/right/up/down keys
        if (e.keyCode < 35 || e.keyCode > 40) {
          ding(e);
        }
      }

      function onPoll() {
        if (isKeyDown || isMouseDown) {
          verbose('CrmMosaicoSyncMonitor: onPoll: defer pending input:', isKeyDown, isMouseDown);
          return;
        }
        if (lastInput === null) {
          return;
        }
        var now = (new Date()).getTime();
        if (now > lastSync + monitor.activeTimeout || now > lastInput + monitor.idleTimeout) {
          lastSync = now;
          lastInput = null;
          verbose('CrmMosaicoSyncMonitor: onPoll: fire', now);
          monitor.onSync();
        }
      }

      this.start = function start($iframe) {
        switch (++startCount) {
          case 1:
            return $iframe.load(function () {_start($iframe);});
          default:
            return _start($iframe);
        }
      };

      function _start($iframe) {
        verbose('CrmMosaicoSyncMonitor: start');
        intervalHandle = $interval(onPoll, monitor.interval);

        isKeyDown = isMouseDown = false;
        lastInput = null;
        lastSync = 0;

        var tgt = $iframe[0].contentDocument;
        tgt.addEventListener('mousedown', onMouseDown, true);
        tgt.addEventListener('mouseup', onMouseUp, true);
        tgt.addEventListener('keydown', onKeyDown, true);
        tgt.addEventListener('keyup', onKeyUp, true);
      }

      this.stop = function stop($iframe) {
        verbose('CrmMosaicoSyncMonitor: stop');
        $interval.cancel(intervalHandle);
        intervalHandle = null;

        var tgt = $iframe[0].contentDocument;
        tgt.removeEventListener('mousedown', onMouseDown, true);
        tgt.removeEventListener('mouseup', onMouseUp, true);
        tgt.removeEventListener('keydown', onKeyDown, true);
        tgt.removeEventListener('keyup', onKeyUp, true);
      };
    };
  });

})(angular, CRM.$, CRM._);
