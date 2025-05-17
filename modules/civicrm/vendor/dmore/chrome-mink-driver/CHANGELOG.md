Changelog
=========

## Unreleased


## 2.9.3

* Switch to phrity/websocket package - successor to textalk/websocket which is no longer maintained (#144, !167)
* Error handling for invalid form values (#140)
* Introduce code coverage in CI (!154, !160)
* Add `make` commands for development tasks (!159)
* Fix event dispatch and input type handling for all HTML input types (#92, #95, #111, #139, !166)
* Remove broken StreamReadException & canDevToolsConnectionBeEstablished timeout/retry logic (!168)
* Fix tests leaving orphaned tabs in Chrome (!165)
* Don't fake event timestamps when clicking elements (!164)
* Improve setValue() validation for unexpected inputs (#143, !171)
* Remove `docker` tag from CI jobs (!172)
* Refactor ChromeDriver::setValue (#125)

## 2.9.2

* Tests cover PHP versions 7.4 to 8.2 (!149)
* Fix variable syntax deprecations (!149)
* Fix headed mode in Chrome v111+ using HTTP PUT request method when opening a new tab per https://chromedevtools.github.io/devtools-protocol/ (!145, !146, !147)

## 2.9.1

* Use correct default values for domWaitTimeout and socketTimeout when unset (#133, !143)

## 2.9.0

* Throw DriverException if file to be uploaded is not found (#12, !128)
* ConnectionException / StreamReadException handling improvements (!133, #68, #99, #119)
* Test coverage for accented character value handling (#105)
* Fix for file upload handling (behat-chrome-extension#12, !128)
* Test improvements (#124, !129)
* Documentation improvements (!127)
* Coding standards fixes (!115, !132, !136)

## 2.8.1

* Cookie encoding bugfix (#86)
* Use upstream Mink test suite (#116)
* Tests cover PHP versions 7.4 to 8.1 (#108)
* Documentation improvements (!125)
* Coding standards fixes (!115)

## 2.8.0

* Improved support for Behat 1.8.x, fixes to CI (!93, #94, #104)
* Support obtaining event listeners for elements (!91)
* Correctly use configured DevTools connection URL (!90, #93)
* Re-throw dead connection exceptions (!88)
* Support `setValue()` on 'url' input types (!87)
* Support clearing and retrieval of messages from `console.log` (!86, #97)
* Support multiple file attachments (!83)
* Support non-HTML responses (!78)
* Updated Event dispatching for input change to add support for React components (!74)
* Full page and screen screenshot functionality (!72, !79)
* Improve exception message on Chrome error (!71)
* Handle experimental Page.navigatedWithinDocument (!70)

## 2.7.0

* Support `setValue()` on 'number' input types (!81)
* Correct types for `printToPDF()` method (!68)
* Add function handling for evaluated JS return values (!67, #67)
* PHP7.3 fixes and test coverage (!66)
* Add ext-json to requirements in composer.json (!64)
* Set Host header for compatibility with Chrome 66+ (!63)
* Non-strict comparison of radio button values (!62)
* Add method to retrieve all cookies (!61)
* Replace deprecated method to ignore SSL certificate errors (!60, #57)
* Remove `event.key` code to fix conflict with non-printable chars (!57)

## 2.6.4

* Fixed StreamReadException not being caught when browser fails to respond on stop

## 2.6.3

* Make DOM wait timeout configurable - [Nikita Nefedov](https://gitlab.com/nikita2206)
* Dispatch javascript keyup/keydown on top of devtools Input.dispatchKeyEvent - [Florent Ruard-Dumaine](https://gitlab.com/atalargo)
* Improved click handling for elements with non-zero border radius - [Carl Wiedemann](https://gitlab.com/c4rl)
* Remove headless chrome check from `captureScreenshot()` - [Eric Jenkins](https://gitlab.com/ericjenkins)
* Fixed handling of dialogs on click - [Arturas Smorgun](https://gitlab.com/asarturas)
* Improved error handling when chrome Connection cannot be established - [Peter Rehm](https://gitlab.com/peterrehm)

## 2.6.2

* Fixed wrongful assumption that chrome has crashed when it was waiting for a long page load.

## 2.6.1

* Fix unicode preg_replace - [PFlorent Ruard-Dumaine](https://gitlab.com/atalargo)
* Documentation for socketTimeout option - [Peter Rehm](https://gitlab.com/peterrehm)
* Reset document to document after visiting a new url - [Peter Rehm](https://gitlab.com/peterrehm)
* Improved error handling when Chrome Connection can not be established and provided hints. - [Peter Rehm](https://gitlab.com/peterrehm)
* Fixes undefined index 'value' for properties - [Mark Nielsen](https://gitlab.com/polothy)
* Fix XPaths for SVGs - [Mark Nielsen](https://gitlab.com/polothy)
* Resolve "Default socket timeout is inconsistent"  - [Sascha Grossenbacher](https://gitlab.com/saschagros)

## 2.6

* Add click on radio element when selected - [Peter Rehm](https://gitlab.com/peterrehm)
* Added support to capture screenshots and to render PDFs - [Peter Rehm](https://gitlab.com/peterrehm)
* Socket timeout defaults now to 10 seconds - [Matthew Hotchen](https://gitlab.com/mhotchen)
* Set pierce argument to TRUE if currently in an iframe  - [Sascha Grossenbacher](https://gitlab.com/saschagros)
* Added verification if an element can be focused  - [Sascha Grossenbacher](https://gitlab.com/saschagros)
* Fixed password fields not being focused before key presses

## 2.5

* Added option for overriding socket timeout - [Matthew Hotchen](https://gitlab.com/mhotchen)

## 2.4.3

* Fixed compatibility with Chrome 61 - [Matthew Hotchen](https://gitlab.com/mhotchen)

* PHP 7.2 compatibility - [Peter Rehm](https://gitlab.com/peterrehm)

## 2.4.2

* Removed dependency on symfony/options-resolver:3 due to conflicts with Symfony2 projects

## 2.4.1

* Added support for enabling certificate override [Arturas Smorgun](https://gitlab.com/asarturas)

* Fixed numeric passwords being treated as integers

## 2.4.0

* Fixed support for Chrome 62 - [Peter Rehm](https://gitlab.com/peterrehm)

* Implemented download behavior (Chrome 62+ only)  - [Peter Rehm](https://gitlab.com/peterrehm)

## 2.3.1

* Fixed 'Server sent invalid upgrade response' when switching windows, in some cases

## 2.3.0

* Fixed getWindowNames incompatibility with Selenium2Driver

* Fixed mouseover sometimes moving the mouse outside the element - [Mark Nielsen](https://gitlab.com/polothy)

* Fixed inability to switchToWindow for some tabs which were opened with window.open() -  [Mark Nielsen](https://gitlab.com/polothy)

* Throw DriverException instead of \Exception - [Mark Nielsen](https://gitlab.com/polothy)

* Throw NoSuchFrameException instead of generic \Exception when the frame is removed after being switched to - [Mark Nielsen](https://gitlab.com/polothy)

* Fixed clicking on an option tag which is inside an optgroup - [Mark Nielsen](https://gitlab.com/polothy)

* Fixed isVisible when the element is hidden using negative offsets or 'visibility: hidden' - [Mark Nielsen](https://gitlab.com/polothy)

* Fixed NoSuchElement exception when textbox is removed by javascript onchange - [Mark Nielsen](https://gitlab.com/polothy)

* Fixed browser resizing - [Mark Nielsen](https://gitlab.com/polothy)

* Added support for setting the value of a input type="search" - [Raphaël Droz](https://gitlab.com/drzraf)

* Added support for setting the value of an element with contenteditable=true - [Mark Nielsen](https://gitlab.com/polothy)

## 2.2.0

* Implemented isolation between multiple instances running against the same browser, if the browser is running with --headless

* Fixed isVisible when an element only has children which are floating, fixed, or absolute

* Fixed setValue on fields with limited length

* Fixed getStatusCode and getResponseHeaders timing out when the page has been loaded before the websocket was opened

* Fixed setValue for multibyte unicode

* Fixed some elements not receiving click

* Sped up animations and added sleep until they complete

* Fixed timeout when page loading takes longer than 5 seconds

* Fixed deadlock when a request fails

* Fixed deadlock when chrome crashes

* Fixed fields not showing autocomplete on setValue, due to unnecessary blur

* Fixed fatal error when restarting without --headless

## 2.1.1

* Fixed compatibility with 5.6 and 7.0

## 2.1.0

* Added support for switching to popups which chrome turned opened as tabs

* Improved findElementXpaths to get the shortest xpath possible

* Fixed xpath queries not always returning the elements in the correct order

* Fixed setValue not always triggering keyup/keydown

* Fixed popup blocker stopping popups triggered by click

* Fixed deadlock when javascript prompt/alert is shown

* Fixed double click not dispatching an event for the first click

* Fixed double click not bubbling

* Fixed page load timing out after 5 seconds

## 2.0.1

* Removed behat dependency

## 2.0.0

* Fixed screenshot feature (thanks https://gitlab.com/OmarMoper)

* Extracted behat extension to its own repository

## 1.1.3

* Fixed timeout when checking for the status code of a request served from cache

## 1.1.2

* PHP 5.6 support

* Fixed websocket timeout when visit() was not the first action after start() or reset()

## 1.1.1

* Licensed as MIT

## 1.1.0

* Added support for basic http authentication

* Added support for removing http-only cookies

* Added support for file upload

* Fixed getContent() returning htmldecoded text instead of the outer html as is

## 1.0.1

* Fixed back() and forward() timing out when the page is served from cache.
