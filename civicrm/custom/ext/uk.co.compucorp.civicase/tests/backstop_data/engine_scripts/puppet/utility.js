'use strict';

module.exports = class CrmPage {
  constructor (engine, scenario, viewPort) {
    this.engine = engine;
    this.scenario = scenario;
    this.viewPort = viewPort;
  }

  /**
   * Waits and clicks every element that matches the target selector.
   *
   * @param {string} selector - the css selector of the target elements to
   * click.
   */
  async clickAll (selector) {
    await this.waitForSelectorAndEvaluate(selector, selector => {
      document.querySelectorAll(selector).forEach(element => element.click());
    });
  }

  /**
   * Waits for the Navigation to happens after some link (selector) is clicked.
   *
   * @param {string} selector - the css selector for the element to click and wait for navigation.
   */
  async clickAndWaitForNavigation (selector) {
    await this.engine.waitForSelector(selector);
    await Promise.all([
      this.engine.click(selector),
      this.engine.waitForNavigation()
    ]);
    await this.cleanups();
  }

  async cleanups () {
    await this.waitForWYSIWYG();
    await this.waitForDatePicker();
  }

  /**
   * Clones UIB Popover popup DOM node
   */
  async cloneUibPopover () {
    await this.engine.evaluate(() => {
      const uibPopover = document.querySelector('div[uib-popover-popup]');
      const uibPopoverClone = uibPopover.cloneNode(true);

      // Insert the new node before the reference node
      uibPopover.parentNode.insertBefore(uibPopoverClone, uibPopover.nextSibling);
    });
  }

  /**
   * Checks if element is visible on screen
   *
   * @param {string} selector - the css selector for the element to checkfor
   * @returns {boolean} if the element is visible on the screen
   */
  async isElementVisible (selector) {
    return this.engine.evaluate((selector) => {
      const e = document.querySelector(selector);

      if (!e) {
        return false;
      }

      const style = window.getComputedStyle(e);

      return style && style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0' && e.offsetHeight > 0;
    }, selector);
  }

  /**
   * Opens all the accordions on the page
   * Logic - to make sure that Accordion dont toggle (open and then close) multiple times, the code checks for the class
   * 'backstop-all-accordions-open' and if not added (which won't be added initially) it excutes the open logic and adds a class
   * preventing future toggling.
   */
  async openAllAccordions () {
    const isSubAccordionExist = !!(await this.engine.$('.collapsible-title'));

    // Clicking all accordion headers
    await this.clickAll('div.crm-accordion-wrapper.collapsed > div');

    if (isSubAccordionExist) {
      await this.clickAll('.collapsible-title');
    }

    try {
      await this.engine.waitFor('.blockUI.blockOverlay', { hidden: true });
      // wait for reedjustment of the modal after ajax content load after opening accordion
      await this.engine.waitFor(300);
    } catch (e) {
      console.log('Loaders still visible and timeout reached!');
    }

    await this.cleanups();
  }

  /**
   * Waits for Angular to load by checking #bootstrap-theme element
   */
  async waitForAngular () {
    await this.engine.waitForSelector('#bootstrap-theme');

    // remove drupal/civicrm error logging, which creates difference
    await this.removeElements('#console');
    // remove system error notification
    await this.removeElements('#crm-notification-container');
  }

  /**
   * Waits for all the loading placeholders to vanish
   *
   * @param {string} parentSelector
   *  - for contextual loading checks
   */
  async waitForLoadingComplete (parentSelector) {
    const timeout = 100000;
    parentSelector = parentSelector !== undefined ? parentSelector + ' ' : '';

    await this.engine.waitFor((parentSelector) => {
      const allLoadingElements = document.querySelectorAll(parentSelector + 'div[class*="civicase__loading-placeholder"]');

      return allLoadingElements.length === 0;
    }, { timeout: timeout }, parentSelector);
  }

  /**
   * Waits for the UI modal to load the form inside it.
   */
  async waitForUIModalLoad () {
    await this.engine.waitFor('.modal-dialog > form');
    await this.cleanups();
    await this.openAllAccordions();
    await this.engine.waitFor(1000);
  }

  /**
   * Waits for a selector before clicking
   *
   * @param {string} selector to wait for and click on
   */
  async waitForAndClick (selector) {
    await this.engine.waitFor(selector);
    await this.engine.click(selector);
  }

  /**
   * Waits for a selector before hovering
   *
   * @param {string} selector to wait for and hover on
   */
  async waitForAndHover (selector) {
    await this.engine.waitFor(selector);
    await this.engine.hover(selector);
  }

  /**
   * Waits for the date picker to be visible on the page
   */
  async waitForDatePicker () {
    const hasDatepicker = await this.isElementVisible('.hasDatepicker');

    if (hasDatepicker) {
      this.engine.waitForSelector('.fa-calendar');
    }
  }

  /**
   * Waits for the WYSIWYG to be visible on the page
   */
  async waitForWYSIWYG () {
    const isWysiwygVisible = await this.isElementVisible('.crm-form-wysiwyg');

    if (isWysiwygVisible) {
      await this.engine.waitFor('.cke .cke_contents', { visible: true });
    }
  }

  /**
   * Waits for the selector to be clearly visible on the screen.
   *
   * @param {string} selector - the css selector of the target elements to
   * look for.
   */
  async waitForVisibility (selector) {
    await this.engine.waitFor((selector) => {
      const uiBlock = document.querySelector(selector);

      return uiBlock.style.display === 'block';
    }, {}, selector);
  }

  /**
   * Waits for an element and then evaluates a function on the browser.
   *
   * @param {string} selector - the css selector for the element to wait
   * @param {Function} fn - the callback function to be executed in
   * the browser after the target element is ready.
   * for.
   */
  async waitForSelectorAndEvaluate (selector, fn) {
    try {
      await this.engine.waitFor(selector, { timeout: 8000 });
      await this.engine.evaluate(fn, selector);
    } catch (e) {
      console.log('Selector "' + selector + '" not found');
    }
  }

  /**
   * @param {string} selector - the css selector for the element to wait
   */
  async removeElements (selector) {
    try {
      await this.engine.evaluate((selector) => {
        CRM.$(selector).hide();
      }, selector);
    } catch (e) {
      console.log('Selector not found');
    }
  }
};
