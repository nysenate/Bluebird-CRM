<?php

namespace DMore\ChromeDriver;

use Behat\Mink\Driver\CoreDriver;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebSocket\ConnectionException;

class ChromeDriver extends CoreDriver
{
    /**
     * @var ChromeBrowser
     */
    private $browser;

    /**
     * @var ChromePage
     */
    private $page;

    /**
     * @var bool Is driver started.
     */
    private $is_started = false;

    /**
     * @var string
     */
    private $api_url;

    /**
     * @var string
     */
    private $ws_url;

    /**
     * @var string
     */
    private $current_window;

    /**
     * @var string
     */
    private $main_window;

    /**
     * @var HttpClient
     */
    private $http_client;

    /**
     * @var string[]
     */
    private $request_headers = [];

    /**
     * @var string
     */
    private $base_url;

    /**
     * @var string The document node to run xpath queries on.
     * Can either be 'document' or valid javascript for an iframe's javascript
     */
    private $document = 'document';

    /**
     * @var int How many milliseconds we should wait for DOM to be ready after each action/transition.
     */
    private $domWaitTimeout;

    /**
     * Default value for $domWaitTimeout.
     *
     * @var int
     */
    public static $domWaitTimeoutDefault = 3000;

    /**
     * Default value for $socketTimeout.
     *
     * @var int
     */
    public static $socketTimeoutDefault = 10;

    /**
     * @var array
     */
    private $options;

    /**
     * ChromeDriver constructor.
     *
     * @param string $api_url
     * @param HttpClient $http_client
     * @param string $base_url
     * @param array $options
     */
    public function __construct(
        $api_url = 'http://localhost:9222',
        HttpClient $http_client = null,
        $base_url = null,
        $options = []
    ) {
        if (empty($base_url)) {
            throw new \InvalidArgumentException("Base URL can not be empty.");
        }
        if ($http_client == null) {
            $http_client = new HttpClient();
        }
        $this->http_client = $http_client;
        $this->api_url = $api_url;
        $this->ws_url = str_replace('http', 'ws', $api_url);
        $this->base_url = $base_url;
        $this->browser = new ChromeBrowser(
            $this->ws_url . '/devtools/browser',
            $options['socketTimeout'] ?? self::$socketTimeoutDefault
        );
        $this->browser->setHttpClient($http_client);
        $this->browser->setHttpUri($api_url);
        $this->domWaitTimeout = $options['domWaitTimeout'] ?? self::$domWaitTimeoutDefault;
        $this->options = $options;
    }

    public function start()
    {
        $this->browser->connect();
        $this->main_window = $this->browser->start();
        $this->connectToWindow($this->main_window);
        $this->is_started = true;

        // Only set download options in headless mode
        if (true === $this->browser->isHeadless()) {
            $downloadBehavior = $this->options['downloadBehavior'] ?? 'default';
            $downloadPath = $this->options['downloadPath'] ?? '/tmp/';
            if ($downloadBehavior !== 'default' || rtrim($downloadPath, '/') !== '/tmp') {
                $this->page->send(
                    'Page.setDownloadBehavior',
                    ['behavior' => $downloadBehavior, 'downloadPath' => $downloadPath]
                );
            }
        }

        if (isset($this->options['validateCertificate']) && $this->options['validateCertificate'] === false) {
            $this->page->send('Security.enable');
            $this->page->send('Security.setIgnoreCertificateErrors', ['ignore' => true]);
        }
    }

    /**
     * Flag for whether driver is started.
     *
     * @return Boolean
     */
    public function isStarted()
    {
        return $this->is_started;
    }

    /**
     * Ensure the driver is started.
     */
    public function ensureStarted()
    {
        if (!$this->isStarted()) {
            $this->start();
        }
    }

    /**
     * Stops driver.
     *
     * Once stopped, the driver should be started again before using it again.
     *
     * Calling any action on a stopped driver is an undefined behavior.
     * The only supported method call after stopping a driver is starting it again.
     *
     * Calling stop on a stopped driver is an undefined behavior. Driver
     * implementations are free to handle it silently or to fail with an
     * exception.
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the driver cannot be closed
     */
    public function stop()
    {
        try {
            $this->reset();
            foreach ($this->getWindowNames() as $key => $window_id) {
                if ($key == 0) {
                    continue;
                }
                $this->http_client->get($this->api_url . '/json/close/' . $window_id);
            }
            $this->browser->close();
        } catch (ConnectionException $exception) {
        } catch (DriverException $exception) {
        }

        $this->is_started = false;
    }

    /**
     * Resets driver state.
     *
     * This should reset cookies, request headers and basic authentication.
     * When possible, the history should be reset as well, but this is not enforced
     * as some implementations may not be able to reset it without restarting the
     * driver entirely. Consumers requiring a clean history should restart the driver
     * to enforce it.
     *
     * Once reset, the driver should be ready to visit a page.
     *
     * Calling any action before visiting a page is an undefined behavior.
     *
     * The only supported method calls on a fresh driver are
     * - visit()
     * - setRequestHeader()
     * - setBasicAuth()
     * - reset()
     * - stop()
     *
     * Calling reset on a stopped driver is an undefined behavior.
     */
    public function reset()
    {
        $this->ensureStarted();
        $this->document = 'document';
        $this->deleteAllCookies();
        $this->connectToWindow($this->main_window);
        $this->page->reset();
        $this->request_headers = [];
        $this->sendRequestHeaders();
    }

    /**
     * Visit specified URL.
     *
     * @param string $url
     *   URL of the page
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the operation cannot be done
     */
    public function visit($url)
    {
        $this->ensureStarted();
        $this->page->visit($url);
        $this->document = 'document';
        $this->page->waitForLoad();
        $this->waitForDom();
    }

    /**
     * Returns current URL address.
     *
     * @return string
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the operation cannot be done
     */
    public function getCurrentUrl()
    {
        $this->waitForDom();
        return $this->evaluateScript('window.location.href');
    }

    /**
     * Reloads current page.
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the operation cannot be done
     */
    public function reload()
    {
        $this->page->reload();
        $this->page->waitForLoad();
    }

    /**
     * Moves browser forward 1 page.
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the operation cannot be done
     */
    public function forward()
    {
        $this->runScript('window.history.forward()');
        $this->waitForDom();
        $this->page->waitForLoad();
    }

    /**
     * Moves browser backward 1 page.
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the operation cannot be done
     */
    public function back()
    {
        $this->runScript('window.history.back()');
        $this->waitForDom();
        $this->page->waitForLoad();
    }

    /**
     * {@inheritdoc}
     */
    public function setBasicAuth($user, $password)
    {
        $this->ensureStarted();
        if ($user === false) {
            $this->unsetRequestHeader('Authorization');
        } else {
            $this->setRequestHeader('Authorization', 'Basic ' . base64_encode($user . ':' . $password));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function switchToWindow($name = null)
    {
        if (null === $name) {
            $this->connectToWindow($this->main_window);
        } else {
            foreach ($this->page->getTabs() as $tab) {
                if ($tab['targetId'] == $name || $tab['title'] == $name) {
                    $this->connectToWindow($tab['targetId']);
                    return;
                }
            }
            try {
                $this->runScript("window.latest_popup = window.open('', '{$name}');");
                $condition = "window.latest_popup.location.href != 'about:blank';";
                $this->wait(2000, $condition);
                $script = "[window.latest_popup.document.title, window.latest_popup.location.href]";
                list($title, $href) = $this->evaluateScript($script);

                foreach ($this->getWindowNames() as $id) {
                    $info = $this->page->send('Target.getTargetInfo', ['targetId' => $id])['targetInfo'];
                    if ($info['type'] === 'page' && $info['url'] == $href && $info['title'] == $title) {
                        $this->switchToWindow($id);
                        return;
                    }
                }
            } catch (\Exception $e) {
            }
            try {
                // Last effort, connect to each window and compare its window name.
                $currentWindow = $this->getCurrentWindow();
                foreach ($this->page->getTabs() as $tab) {
                    $this->connectToWindow($tab['targetId']);
                    $windowName = $this->evaluateScript('window.name');

                    if ($windowName === $name) {
                        return;
                    }
                }
                // Failed to find it, try to reconnect to the original window.
                $this->connectToWindow($currentWindow);
            } catch (\Exception $e) {
            }

            throw new DriverException("Couldn't find window {$name}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function switchToIFrame($name = null)
    {
        if ($name == null) {
            $this->document = 'document';
        } else {
            $xpath = "//IFRAME[@id='{$name}' or @name='{$name}']";
            $script = <<<JS
        window.active_iframe = document.evaluate("{$xpath}", {$this->document}.body).iterateNext();
        window.active_iframe != null;
JS;

            if (!$this->evaluateScript($script)) {
                throw new DriverException("No frame with id or name '{$name}' was found.");
            }
            $this->document = "window.active_iframe.contentWindow.document";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHeader($name, $value)
    {
        $this->ensureStarted();
        $this->request_headers[$name] = $value;
        $this->sendRequestHeaders();
    }

    /**
     * @param $name
     */
    public function unsetRequestHeader($name)
    {
        if (array_key_exists($name, $this->request_headers)) {
            unset($this->request_headers[$name]);
            $this->sendRequestHeaders();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseHeaders()
    {
        return $this->page->getResponse()['headers'];
    }

    /**
     * Sets cookie.
     *
     * @param string $name
     * @param string $value
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the operation cannot be done
     */
    public function setCookie($name, $value = null)
    {
        if ($value === null) {
            foreach ($this->page->send('Network.getAllCookies')['cookies'] as $cookie) {
                if ($cookie['name'] == $name) {
                    if ($this->browser->getVersion() >= 63) {
                        $parameters = ['name' => $name, 'url' => 'http://' . $cookie['domain'] . $cookie['path']];
                        $this->page->send('Network.deleteCookies', $parameters);
                    } else {
                        $parameters = ['cookieName' => $name, 'url' => 'http://' . $cookie['domain'] . $cookie['path']];
                        $this->page->send('Network.deleteCookie', $parameters);
                    }
                }
            }
        } else {
            $url = $this->base_url . '/';
            $value = rawurlencode($value);
            $this->page->send('Network.setCookie', ['url' => $url, 'name' => $name, 'value' => $value]);
        }
    }

    /**
     * Returns cookie by name.
     *
     * @param string $name
     *
     * @return string|null
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the operation cannot be done
     */
    public function getCookie($name)
    {
        $result = $this->page->send('Network.getCookies');

        foreach ($result['cookies'] as $cookie) {
            if ($cookie['name'] == $name) {
                return rawurldecode($cookie['value']);
            }
        }
        return null;
    }

    /**
     * Returns all cookies.
     *
     * @return array
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the operation cannot be done
     */
    public function getCookies()
    {
        $result = $this->page->send('Network.getCookies');

        return $result['cookies'];
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->page->getResponse()['status'];
    }

    /**
     * Returns last response content.
     *
     * @return string
     *
     * @throws \Behat\Mink\Exception\DriverException
     *  When the operation cannot be done
     */
    public function getContent()
    {
        try {
            return $this->getHtml('//html');
        } catch (ElementNotFoundException $e) {
            return '';
        }
    }

    /**
     * Capture a fullscreen screenshot of the current window.
     *
     * @return string screenshot of MIME type image/* depending
     *                on driver (e.g., image/png, image/jpeg)
     *
     * @throws \Behat\Mink\Exception\DriverException
     *  When the operation cannot be done
     */
    public function getScreenshot()
    {
        $metrics = $this->page->send('Page.getLayoutMetrics');
        $weight = ceil($metrics['contentSize']['width']);
        $height = ceil($metrics['contentSize']['height']);
        $this->setVisibleSize($weight, $height);
        $screenshot = $this->page->send(
            'Page.captureScreenshot',
            ['clip' => ['x' => 0, 'y' => 0, 'width' => $weight, 'height' => $height, 'scale' => 1]]
        );
        return base64_decode($screenshot['data']);
    }

    /**
     * Capture a screenshot of the entire page.
     *
     * @return string screenshot of MIME type image/* depending
     *                on driver (e.g., image/png, image/jpeg)
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the operation cannot be done
     */
    public function getFullPageScreenshot()
    {
        $metrics = $this->page->send('Page.getLayoutMetrics');
        $width = ceil($metrics['contentSize']['width']);
        $height = ceil($metrics['contentSize']['height']);

        // Overwrite clip for full page at all times.
        $clip = ['x' => 0, 'y' => 0, 'width' => $width, 'height' => $height, 'scale' => 1];
        $this->page->send(
            'Emulation.setDeviceMetricsOverride',
            ['mobile' => false, 'deviceScaleFactor' => 1, 'width' => $width, 'height' => $height,]
        );

        $screenshot = $this->page->send('Page.captureScreenshot', ['clip' => $clip]);
        return base64_decode($screenshot['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function getWindowNames()
    {
        $names = [];
        $tabs = (array)$this->page->getTabs();
        foreach ($tabs as $tab) {
            $names[] = $tab['targetId'];
        }
        return $names;
    }

    /**
     * Return the name of the currently active window.
     *
     * @return string the name of the current window
     *
     * @throws \Behat\Mink\Exception\DriverException
     *   When the operation cannot be done
     */
    public function getWindowName()
    {
        return $this->current_window;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentWindow()
    {
        return $this->current_window;
    }

    /**
     * Finds elements with specified XPath query.
     *
     * @param string $xpath
     * @return string[] The XPath of the matched elements
     * @throws ElementNotFoundException
     */
    protected function findElementXpaths($xpath)
    {
        $this->waitForDom();
        $expression = $this->getXpathExpression($xpath);
        $expression .= <<<JS
    function getPathTo(element) {
        if (typeof element.id == 'string' && element.id != '' && document.getElementById(element.id) === element) {
            return '//' + element.tagName + '[@id="'+element.id+'"]';
        }
        if (element === {$this->document}.body ||
            element === {$this->document}.head ||
            element === {$this->document}.documentElement
        ) {
            return '//' + element.tagName;
        }

        var ix = 0;
        var siblings = element.parentNode.childNodes;
        for (var i = 0; i < siblings.length; i++) {
            var sibling = siblings[i];
            if (sibling === element)
                return getPathTo(element.parentNode) + '/' + element.tagName + '[' + (ix + 1) + ']';
            if (sibling.nodeType===1 && sibling.tagName===element.tagName)
                ix++;
        }
    }
    var result = [];
    while (element = xpath_result.iterateNext()) {
        result.push(getPathTo(element));
    };
    result
JS;

        $values = $this->evaluateScript($expression);

        // Cannot XPath directly into an SVG, workaround is to select the element from the result of the original XPath.
        foreach ($values as $key => $value) {
            if (stripos($value, 'svg[') !== false) {
                $values[$key] = sprintf('(%s)[%d]', $xpath, $key + 1);
            }
        }

        return $values;
    }

    /**
     * Returns element's tag name by it's XPath query.
     *
     * @param string $xpath
     * @return string
     * @throws ElementNotFoundException
     */
    public function getTagName($xpath)
    {
        return $this->getElementProperty($xpath, 'tagName');
    }

    /**
     * Returns element's text by it's XPath query.
     *
     * @param string $xpath
     * @return string
     * @throws ElementNotFoundException
     */
    public function getText($xpath)
    {
        $text = $this->getElementProperty($xpath, 'innerText');
        $text = trim(preg_replace('/\s+/u', ' ', $text), ' ');
        return $text;
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml($xpath)
    {
        return $this->getElementProperty($xpath, 'innerHTML');
    }

    /**
     * {@inheritdoc}
     */
    public function getOuterHtml($xpath)
    {
        return $this->getElementProperty($xpath, 'outerHTML');
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($xpath, $name)
    {
        $name = addslashes($name);
        return $this->getElementProperty($xpath, "getAttribute('{$name}');");
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($xpath)
    {
        $expression = $this->getXpathExpression($xpath);
        $expression .= <<<JS
    element = xpath_result.iterateNext();
    var value = null

    if (element.tagName == 'INPUT' && element.type == 'checkbox') {
        value = element.checked ? element.value : null;
    } else if (element.tagName == 'INPUT' && element.type == 'radio') {
        var name = element.getAttribute('name');
        if (name) {
            var fields = window.document.getElementsByName(name),
                i, l = fields.length;
            for (i = 0; i < l; i++) {
                var field = fields.item(i);
                if (field.form === element.form && field.checked) {
                    value = field.value;
                    break;
                }
            }
        }
    } else if (element.tagName == 'SELECT' && element.multiple) {
        value = []
        for (var i = 0; i < element.options.length; i++) {
            if (element.options[i].selected) {
                value.push(element.options[i].value);
            }
        }
    } else {
        value = element.value;
    }
    value
JS;

        return $this->evaluateScript($expression);
    }

    /**
     * @param $xpath
     * @return bool
     * @throws ElementNotFoundException
     */
    protected function isTextTypeInput($xpath): bool
    {
        // See ChromeDriverInputEventsTest for list of all W3C input types and rationale for which we count as `text`
        // Note that any unknown type (e.g. `<input type=made_up>`) is already coalesced by the browser so Chrome will
        // return `element.type === 'text'` for these elements.
        $is_text_field = <<<'JS'
            (
                element.tagName == 'INPUT'
                && ['text', 'search', 'tel', 'url', 'email', 'password', 'number'].includes(element.type)
            )
            || (element.tagName == 'TEXTAREA')
            || (element.hasAttribute('contenteditable') && element.getAttribute('contenteditable') != 'false')
            JS;

        if (!$this->runScriptOnXpathElement($xpath, $is_text_field)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($xpath, $value)
    {
        if (!$this->isTextTypeInput($xpath)) {
            $this->setTextTypeValue($xpath, $value);
        } else {
            $this->setNonTextTypeValue($xpath, $value);
        }
    }

    /**
     * @param $xpath
     * @param $value
     * @return void
     * @throws DriverException
     * @throws ElementNotFoundException
     * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
     */
    protected function setTextTypeValue($xpath, $value)
    {
        if (\is_array($value) || \is_bool($value)) {
            throw new DriverException('Textual and file form fields don\'t support array or boolean values');
        }

        if (\is_null($value)) {
            throw new DriverException('Textual and file form fields don\'t support null values');
        }

        $current_value = $this->getValue($xpath);
        $script = 'if (element.offsetParent !== null)  { element.focus(); return true; } else { return false;  }';
        if (!$this->runScriptOnXpathElement($xpath, $script)) {
            throw new DriverException('Element is not visible and can not be focused');
        }

        // Remove the current value if present (nb an empty contenteditable returns `null` as current value)
        for ($i = 0; $i < strlen($current_value ?? ''); $i++) {
            $parameters = ['type' => 'rawKeyDown', 'nativeVirtualKeyCode' => 8, 'windowsVirtualKeyCode' => 8];
            $this->page->send('Input.dispatchKeyEvent', $parameters);
            $this->page->send('Input.dispatchKeyEvent', ['type' => 'keyUp']);
            $parameters = ['type' => 'rawKeyDown', 'nativeVirtualKeyCode' => 46, 'windowsVirtualKeyCode' => 46];
            $this->page->send('Input.dispatchKeyEvent', $parameters);
            $this->page->send('Input.dispatchKeyEvent', ['type' => 'keyUp']);
        }

        // Then add the new value
        for ($i = 0; $i < mb_strlen($value); $i++) {
            $char = mb_substr($value, $i, 1);
            // For 'normal' chars, the `text` devtools property & the `key` event property are the desired character
            $text = $key = $char;
            if ($char === "\n") {
                // For newlines, the `text` and `key` have special values. This is also the case for other special
                // (control etc) keys, but newline is the only one that can also be added as part of the string value
                // of a text field (e.g. a textarea or contenteditable).
                $text = chr(13);
                $key = 'Enter';
            }
            $this->page->send('Input.dispatchKeyEvent', ['type' => 'keyDown', 'text' => $text, 'key' => $key]);
            $this->page->send('Input.dispatchKeyEvent', ['type' => 'keyUp', 'key' => $key]);
        }
        usleep(5000);

        try {
            // Remove the focus from the element if the field still has focus, in order to trigger the change event
            // if the element supports it.
            //
            // By doing this instead of simply triggering the change event for the given xpath we ensure that:
            // a) the change event will not be triggered twice for the same element if it has lost focus in the meantime
            //    (e.g. if the original input element has custom javascript behaviour that focuses another control
            //    during data entry, such as an autocomplete / typeahead).
            // b) the change event will not be triggered for contenteditable elements (which do not natively fire
            //    `change`).
            // c) the change event will bubble up the document the same as native change events.
            //
            // If the element has lost focus already then there is nothing to do as this will already have caused
            // the triggering of the change event for that element and/or we assume that clientside code that focused
            // an alternate element has taken responsibility for triggering events on input / change.
            $this->runScriptOnXpathElement($xpath, 'if (element === document.activeElement) {element.blur();}');
        } catch (ElementNotFoundException $e) {
            // Ignore, sometimes input elements can get removed from a document after they are modified.
            // For example, editing a title inline and sending a newline character at the end
            // which submits the inline edit and saves the changes.
        }
    }

    /**
     * @param  $xpath
     * @param  $value
     * @throws ElementNotFoundException
     * @throws \Exception
     */
    private function setNonTextTypeValue($xpath, $value)
    {
        $fieldType = $this->getElementProperty($xpath, 'type');

        if (!\is_string($value) && in_array($fieldType, ['file', 'radio'])) {
            throw new DriverException('Only string values can be used for a ' . $fieldType . ' input.');
        }

        if (\is_bool($value) && in_array($fieldType, ['select', 'select-one', 'submit', 'color', 'date', 'time'])) {
            throw new DriverException('Boolean values cannot be used for a ' . $fieldType . ' element.');
        }

        if ($value === [] && in_array($fieldType, ['color', 'date', 'time'])) {
            throw new DriverException(sprintf('Empty array value cannot be used for a "%s" element.', $fieldType));
        }

        if (in_array($fieldType, ['submit', 'image', 'button', 'reset'])) {
            throw new DriverException(sprintf('Cannot set value on "%s" element.', $xpath));
        }

        if (\is_null($value)) {
            throw new DriverException('Non-text fields don\'t support null values');
        }

        $json_value = is_numeric($value) ? $value : json_encode($value);
        $expression = <<<JS
    var expected_value = $json_value;
    var result = 0;
    var trigger_change = true;
    var last_value = element.value;
    element.scrollIntoViewIfNeeded();
    element.focus();
    if (element.tagName == 'INPUT' && element.type == 'radio') {
        var name = element.name
        var fields = window.document.getElementsByName(name),
            i, l = fields.length;
        for (i = 0; i < l; i++) {
            var field = fields.item(i);
            if (field.form === element.form) {
                if (field.value == expected_value) {
                    field.checked = true;
                    field.click();
                } else {
                    field.checked = false;
                }
            }
        }
    } else if (element.tagName == 'INPUT' && element.type == 'checkbox') {
        if (element.checked != expected_value) {
            element.click();
        }
        trigger_change = false;
    } else if (element.tagName == 'SELECT') {
        if (element.multiple && typeof expected_value != 'object') {
            expected_value = [expected_value]
        }
        for (var i = 0; i < element.options.length; i++) {
            if ((element.multiple && expected_value.includes(element.options[i].value)) 
                || element.options[i].value == expected_value) {
                element.options[i].selected = true;
            } else {
                element.options[i].selected = false;
            }
        }
    } else if (element.tagName == 'INPUT' && element.type == 'file') {
    } else {
        element.value = expected_value
    }
    if (trigger_change) {
        var event = new Event("change", { target: element, bubbles: true });
        // React 15
        event.simulated = true;
        // React 16
        var tracker = element._valueTracker;
        if (tracker) {
            tracker.setValue(last_value);
        }
        element.dispatchEvent(event);
    }
    element.blur();
    null
JS;

        $result = $this->runScriptOnXpathElement($xpath, $expression);
    }

    /**
     * {@inheritdoc}
     */
    public function check($xpath)
    {
        $this->expectCheckbox($xpath);
        $this->setValue($xpath, true);
    }

    /**
     * {@inheritdoc}
     */
    public function uncheck($xpath)
    {
        $this->expectCheckbox($xpath);
        $this->setValue($xpath, false);
    }

    /**
     * {@inheritdoc}
     */
    public function isChecked($xpath)
    {
        return $this->getElementProperty($xpath, 'checked');
    }

    /**
     * {@inheritdoc}
     */
    public function selectOption($xpath, $value, $multiple = false)
    {
        $this->expectSelectOrRadio($xpath);
        if ($multiple) {
            $value = array_merge((array)$value, $this->getValue($xpath));
        }
        return $this->setValue($xpath, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function click($xpath)
    {
        $this->mouseOver($xpath);

        if ($this->runScriptOnXpathElement($xpath, 'element.tagName == "OPTION"')) {
            $this->setValue($xpath . '/ancestor::select[1]', $this->runScriptOnXpathElement($xpath, 'element.value'));
        } else {
            $escaped = addslashes($xpath);
            $script = <<<JS
(function() {
    var rect = element.getBoundingClientRect();
    var initialX = Math.ceil(rect.left);
    var initialY = Math.ceil(rect.top);
    var maxX = Math.floor(rect.left + rect.width);
    var maxY = Math.floor(rect.top + rect.height);
    var midX = Math.floor((initialX + maxX) / 2);
    var midY = Math.floor((initialY + maxY) / 2);
    for (x = midX; x >= initialX; x--) {
        for (y = midY; y >= initialY; y--) {
            var pointElement = document.elementFromPoint(x, y);
            if (element === pointElement || element.contains(pointElement)) {
                return [x, y];
            }
        }
    }
    element.click();
    return null;
})();
JS;

            $result = $this->runScriptOnXpathElement($xpath, $script);

            if ($result !== null) {
                list($left, $top) = $result;
                $this->page->send('Input.dispatchMouseEvent', ['type' => 'mouseMoved', 'x' => $left, 'y' => $top]);

                $parameters = [
                    'type' => 'mousePressed',
                    'x' => $left,
                    'y' => $top,
                    'button' => 'left',
                    'clickCount' => 1,
                ];
                $this->page->send('Input.dispatchMouseEvent', $parameters);
                $parameters = [
                    'type' => 'mouseReleased',
                    'x' => $left,
                    'y' => $top,
                    'button' => 'left',
                    'clickCount' => 1,
                ];
                $this->page->send('Input.dispatchMouseEvent', $parameters);
            }
            usleep(50000);
            $this->waitForDom();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverException
     * @throws ElementNotFoundException
     */
    public function attachFile($xpath, $path)
    {
        if (!file_exists($path)) {
            throw new DriverException("ChromeDriver was unable to find file '{$path}' to attach it.");
        }

        $script = <<<JS
    if (element == undefined || element.tagName != 'INPUT' || element.type != 'file') {
        throw new Error("Element not found");
    }
    element.name
JS;

        $name = $this->runScriptOnXpathElement($xpath, $script, 'file input');

        $node_id = null;
        $parameters = [
            'pierce' => $this->document !== 'document',
        ];
        foreach ($this->page->send('DOM.getFlattenedDocument', $parameters)['nodes'] as $element) {
            if (!empty($element['attributes'])) {
                $num_attributes = count($element['attributes']);
                for ($key = 0; $key < $num_attributes; $key += 2) {
                    if ($element['attributes'][$key] == 'name' && $element['attributes'][$key + 1] == $name) {
                        $this->page->send(
                            'DOM.setFileInputFiles',
                            ['nodeId' => $element['nodeId'], 'files' => (array)$path]
                        );
                        return;
                    }
                }
            }
        }

        throw new ElementNotFoundException($this, 'file', 'xpath', $xpath);
    }

    /**
     * {@inheritdoc}
     */
    public function doubleClick($xpath)
    {
        $this->click($xpath);
        $this->triggerMouseEvent($xpath, 'dblclick');
    }

    /**
     * {@inheritdoc}
     */
    public function rightClick($xpath)
    {
        $this->mouseOver($xpath);
        $this->triggerEvent($xpath, 'contextmenu');
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible($xpath)
    {
        $script = <<<JS
    if (element.tagName == 'OPTION') {
        element = element.closest('select');
    }
    if (!(element.offsetHeight > 0 || element.offsetParent != null)) {
        return false;
    }
    element.scrollIntoViewIfNeeded();
    var rec = element.getBoundingClientRect();

    return !(rec.right < 0 || rec.bottom < 0 || window.getComputedStyle(element).visibility == "hidden");
JS;

        return $this->runScriptOnXpathElement($xpath, $script);
    }

    /**
     * {@inheritdoc}
     */
    public function isSelected($xpath)
    {
        return $this->runScriptOnXpathElement($xpath, '!!element.selected', 'select');
    }

    /**
     * {@inheritdoc}
     */
    public function mouseOver($xpath)
    {
        $this->runScriptOnXpathElement($xpath, 'element.scrollIntoViewIfNeeded()');
        list($left, $top) = $this->getCoordinatesForXpath($xpath);
        $this->page->send('Input.dispatchMouseEvent', ['type' => 'mouseMoved', 'x' => $left + 1, 'y' => $top + 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function focus($xpath)
    {
        $this->triggerEvent($xpath, 'focus');
    }

    /**
     * {@inheritdoc}
     */
    public function blur($xpath)
    {
        $this->triggerEvent($xpath, 'blur');
    }

    /**
     * {@inheritdoc}
     */
    public function keyPress($xpath, $char, $modifier = null)
    {
        $this->triggerKeyboardEvent($xpath, $char, $modifier, 'keypress');
    }

    /**
     * {@inheritdoc}
     */
    public function keyDown($xpath, $char, $modifier = null)
    {
        $this->triggerKeyboardEvent($xpath, $char, $modifier, 'keydown');
    }

    /**
     * {@inheritdoc}
     */
    public function keyUp($xpath, $char, $modifier = null)
    {
        $this->triggerKeyboardEvent($xpath, $char, $modifier, 'keyup');
    }

    /**
     * {@inheritdoc}
     */
    public function dragTo($sourceXpath, $destinationXpath)
    {
        list($left, $top) = $this->getCoordinatesForXpath($sourceXpath);
        $this->page->send('Input.dispatchMouseEvent', ['type' => 'mouseMoved', 'x' => $left + 1, 'y' => $top + 1]);
        $parameters = ['type' => 'mousePressed', 'x' => $left + 1, 'y' => $top + 1, 'button' => 'left'];
        $this->page->send('Input.dispatchMouseEvent', $parameters);

        list($left, $top) = $this->getCoordinatesForXpath($destinationXpath);
        $this->page->send('Input.dispatchMouseEvent', ['type' => 'mouseMoved', 'x' => $left + 1, 'y' => $top + 1]);
        $parameters = ['type' => 'mouseReleased', 'x' => $left + 1, 'y' => $top + 1, 'button' => 'left'];
        $this->page->send('Input.dispatchMouseEvent', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function executeScript($script)
    {
        $this->evaluateScript($script);
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateScript($script)
    {
        if (substr($script, 0, 8) === 'function') {
            $script = '(' . $script . ')';
            if (substr($script, -2) == ';)') {
                $script = substr($script, 0, -2) . ')';
            }
        }

        $result = $this->runScript($script)['result'];

        if (array_key_exists('subtype', $result) && $result['subtype'] === 'error') {
            if ($result['className'] === 'SyntaxError' && strpos($result['description'], 'Illegal return') !== false) {
                return $this->evaluateScript('(function() {' . $script . '}());');
            }
            if (preg_match('/Cannot read property .document. of null/', $result['description']) === 1) {
                throw new NoSuchFrameException('The iframe no longer exists');
            }
            throw new DriverException($result['description']);
        }

        if ($result['type'] == 'object' && array_key_exists('subtype', $result)) {
            if ($result['subtype'] == 'null') {
                return null;
            } elseif ($result['subtype'] == 'array' && $result['className'] == 'Array' && $result['objectId']) {
                return $this->fetchObjectProperties($result);
            } else {
                return [];
            }
        } elseif ($result['type'] == 'object' && $result['className'] == 'Object') {
            return $this->fetchObjectProperties($result);
        } elseif ($result['type'] == 'undefined') {
            return null;
        }

        if (!array_key_exists('value', $result)) {
            return null;
        }

        return $result['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function wait($timeout, $condition)
    {
        $max_iterations = ceil($timeout / 10);
        $iterations = 0;

        do {
            $result = $this->evaluateScript($condition);
            if ($result || $iterations++ == $max_iterations) {
                break;
            }
            usleep(10000);
        } while (!$this->page->hasJavascriptDialog());
        return (bool)$result;
    }

    /**
     * {@inheritdoc}
     */
    public function resizeWindow($width, $height, $name = null)
    {
        $this->setVisibleSize($width, $height);
        $this->executeScript("window.outerWidth = $width;window.outerHeight = $height;");
    }

    /**
     * Sets the browser window size.
     *
     * @param int $width Set the window width, measured in pixels
     * @param int $height Set the window height, measured in pixels
     */
    public function setVisibleSize($width, $height)
    {
        $this->page->send(
            'Emulation.setDeviceMetricsOverride',
            [
                'width' => $width,
                'height' => $height,
                'deviceScaleFactor' => 0,
                'mobile' => false,
                'fitWindow' => false,
            ]
        );
        $this->page->send(
            'Emulation.setVisibleSize',
            [
                'width' => $width,
                'height' => $height,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function maximizeWindow($name = null)
    {
        if (true === $this->browser->isHeadless()) {
            list($width, $height) = $this->evaluateScript('[screen.width, screen.height]');
            $this->setVisibleSize($width, $height);
        } else {
            $this->page->send('Browser.setWindowBounds', ['windowId' => 1, 'bounds' => ['windowState' => 'maximized']]);
        }
        $this->executeScript("window.outerWidth = screen.width;window.outerHeight = screen.height;");
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm($xpath)
    {
        $this->runScriptOnXpathElement($xpath, 'element.submit()', 'form');
    }

    public function acceptAlert($text = '')
    {
        $this->page->send('Page.handleJavaScriptDialog', ['accept' => true, 'promptText' => $text]);
        $this->waitForDom();
    }

    public function dismissAlert()
    {
        $this->page->send('Page.handleJavaScriptDialog', ['accept' => false]);
        $this->waitForDom();
    }

    /**
     * Get all console messages since start or last clear.
     *
     * @return array
     */
    public function getConsoleMessages()
    {
        return $this->page->getConsoleMessages();
    }

    /**
     * Clear the console messages.
     */
    public function clearConsoleMessages()
    {
        $this->page->clearConsoleMessages();
    }

    /**
     * Clear browser cookies.
     */
    protected function deleteAllCookies()
    {
        $this->page->send('Network.clearBrowserCookies');
    }

    /**
     * @param  $xpath
     * @return string
     */
    protected function getXpathExpression($xpath)
    {
        $xpath = addslashes($xpath);
        $xpath = str_replace("\n", '\\n', $xpath);
        // phpcs:ignore Generic.Files.LineLength
        return "var xpath_result = document.evaluate(\"{$xpath}\", {$this->document}, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE);";
    }

    protected function getElementProperty($xpath, $property)
    {
        return $this->runScriptOnXpathElement($xpath, 'element.' . $property);
    }

    /**
     * @param  $xpath
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    protected function expectSelectOrRadio($xpath)
    {
        $script = <<<JS
    element.tagName == 'SELECT' || (element.tagName == 'INPUT' && element.type == 'radio')
JS;
        if (!$this->runScriptOnXpathElement($xpath, $script)) {
            throw new ElementNotFoundException($this, 'select or radio', 'xpath', $xpath);
        }
    }

    /**
     * @param  $xpath
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    protected function expectCheckbox($xpath)
    {
        $script = <<<JS
    element.tagName == 'INPUT' && element.type == 'checkbox'
JS;
        if (!$this->runScriptOnXpathElement($xpath, $script)) {
            throw new ElementNotFoundException($this, 'checkbox', 'xpath', $xpath);
        }
    }

    /**
     * @param  $xpath
     * @param  $event
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    protected function triggerMouseEvent($xpath, $event)
    {
        $script = <<<JS
    if (element) {
        element.dispatchEvent(new MouseEvent('$event', { bubbles: true }));
    }
    element != null
JS;

        $this->runScriptOnXpathElement($xpath, $script);
    }

    /**
     * @param  $xpath
     * @param  $event
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    protected function triggerEvent($xpath, $event)
    {
        $script = <<<JS
    if (element) {
        element.dispatchEvent(new Event('$event'));
    }
    element != null
JS;

        $this->runScriptOnXpathElement($xpath, $script);
    }

    /**
     * @param  $xpath
     * @param  $char
     * @param  $modifier
     * @param  $event
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    protected function triggerKeyboardEvent($xpath, $char, $modifier, $event)
    {
        if (is_string($char)) {
            $char = ord($char);
        }
        $options = [
            'ctrlKey' => $modifier == 'ctrl' ? 'true' : 'false',
            'altKey' => $modifier == 'alt' ? 'true' : 'false',
            'shiftKey' => $modifier == 'shift' ? 'true' : 'false',
            'metaKey' => $modifier == 'meta' ? 'true' : 'false',
        ];

        $script = <<<JS
    if (element) {
        element.focus();
        var event = document.createEvent("Events");
        event.initEvent("$event", true, true);
        event.keyCode = $char;
        event.which = $char;
        event.ctrlKey = {$options['ctrlKey']};
        event.shiftKey = {$options['shiftKey']};
        event.altKey = {$options['altKey']};
        event.metaKey = {$options['metaKey']};

        element.dispatchEvent(event);
    }
    element != null;
JS;

        $this->runScriptOnXpathElement($xpath, $script);
    }

    /**
     * @param  $xpath
     * @param  $script
     * @param null $type
     * @return array
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     * @throws \Exception
     */
    protected function runScriptOnXpathElement($xpath, $script, $type = null)
    {
        $expression = $this->getXpathExpression($xpath);
        $expression .= <<<JS
    var element = xpath_result.iterateNext();
    if (null == element) {
        throw new Error("Element not found");
    }
JS;
        $expression .= $script;
        try {
            $result = $this->evaluateScript($expression);
        } catch (\Exception $exception) {
            if (strpos($exception->getMessage(), 'Element not found') !== false) {
                throw new ElementNotFoundException($this, $type, 'xpath', $xpath);
            }
            throw $exception;
        }
        return $result;
    }

    /**
     * @param  $xpath
     * @return array
     */
    protected function getCoordinatesForXpath($xpath)
    {
        $expression = $this->getXpathExpression($xpath);
        $expression .= <<<JS
    var element = xpath_result.iterateNext();
    rect = element.getBoundingClientRect();
    [rect.left, rect.top, rect.width, rect.height]
JS;

        list($left, $top, $width, $height) = $this->evaluateScript($expression);
        return [ceil($left), ceil($top), floor($width), floor($height)];
    }

    /**
     * @param string $xpath
     * @return array
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function getEventListenersForXpath($xpath)
    {
        $xpath = addslashes($xpath);
        $xpath = str_replace("\n", '\\n', $xpath);
        // Query the element first to obtain the Remote.ObjectID which is required
        // parameter when obtaining the list of registered event listeners.
        $script = <<<JS
        document.evaluate(
            "{$xpath}",
            {$this->document},
            null,
            XPathResult.FIRST_ORDERED_NODE_TYPE,
            null
        ).singleNodeValue;
JS;
        $result = $this->runScript($script)['result'];
        if ($result['type'] != 'object' || $result['subtype'] != 'node') {
            throw new ElementNotFoundException($this, null, 'xpath', $xpath);
        }

        // Get list of event listeners registered.
        $result = $this->page->send(
            'DOMDebugger.getEventListeners',
            [
                'objectId' => $result['objectId'],
            ]
        );

        return $result['listeners'];
    }

    /**
     * @param  $script
     * @return null
     */
    protected function runScript($script)
    {
        $this->page->waitForLoad();
        return $this->page->send('Runtime.evaluate', ['expression' => $script]);
    }

    /**
     * @param  $result
     * @return array
     */
    protected function fetchObjectProperties($result)
    {
        $parameters = ['objectId' => $result['objectId'], 'ownProperties' => true];
        $properties = $this->page->send('Runtime.getProperties', $parameters)['result'];
        $return = [];
        foreach ($properties as $property) {
            if ($property['name'] !== '__proto__' && $property['name'] !== 'length') {
                $value = $property['value'];
                if (
                    !empty($value['type']) && $value['type'] == 'object'
                    && !empty($value['className'])
                    && in_array($value['className'], ['Array', 'Object'])
                ) {
                    $return[$property['name']] = $this->fetchObjectProperties($value);
                } else {
                    if (
                        $value['type'] === 'number' && !array_key_exists('value', $value)
                        && array_key_exists('unserializableValue', $value) && $value['unserializableValue'] === '-0'
                    ) {
                        $return[$property['name']] = 0;
                    } elseif (
                        $value['type'] === 'function' && !array_key_exists('value', $value)
                        && array_key_exists('description', $value)
                    ) {
                        $return[$property['name']] = $value['description'];
                    } elseif (!array_key_exists('value', $value)) {
                        throw new DriverException('Property value not set');
                    } else {
                        $return[$property['name']] = $value['value'];
                    }
                }
            }
        }
        return $return;
    }

    /**
     * @param  $window_id
     * @throws \Behat\Mink\Exception\DriverException
     */
    protected function connectToWindow($window_id)
    {
        if ($window_id === $this->current_window) {
            return;
        }

        $debuggerUrl = null;
        $windows = json_decode($this->http_client->get($this->api_url . '/json/list'), true);

        foreach ($windows as $window) {
            if ($window['id'] == $window_id) {
                $this->page = new ChromePage(
                    $window['webSocketDebuggerUrl'],
                    $this->options['socketTimeout'] ?? self::$socketTimeoutDefault
                );
                $this->page->connect();
                $this->current_window = $window_id;
                $this->document = 'document';
                return;
            }
        }

        throw new DriverException('No such window ' . $window_id);
    }

    protected function sendRequestHeaders()
    {
        $this->page->send('Network.setExtraHTTPHeaders', ['headers' => $this->request_headers ?: new \stdClass()]);
    }

    protected function waitForDom()
    {
        if (!$this->page->hasJavascriptDialog()) {
            $this->wait($this->domWaitTimeout, 'document.readyState == "complete"');
            $this->page->waitForLoad();
        }
    }

    /**
     * For more information see https://chromedevtools.github.io/devtools-protocol/tot/Page/#method-printToPDF
     *
     * @param string $filename
     * @param bool $landscape
     * @param bool $displayHeaderFooter
     * @param bool $printBackground
     * @param int $scale
     * @param float $paperWidth
     * @param float $paperHeight
     * @param float $marginTop
     * @param float $marginBottom
     * @param float $marginLeft
     * @param float $marginRight
     * @param string $pageRanges
     * @param bool $ignoreInvalidPageRanges
     * @param string $headerTemplate
     * @param string $footerTemplate
     * @throws \Exception
     */
    public function printToPDF(
        $filename,
        $landscape = false,
        $displayHeaderFooter = false,
        $printBackground = false,
        $scale = 1,
        $paperWidth = 8.5,
        $paperHeight = 11.0,
        $marginTop = 1.0,
        $marginBottom = 1.0,
        $marginLeft = 1.0,
        $marginRight = 1.0,
        $pageRanges = '',
        $ignoreInvalidPageRanges = false,
        $headerTemplate = '',
        $footerTemplate = ''
    ) {
        if (false === $this->browser->isHeadless()) {
            throw new \RuntimeException('Page.printToPDF is only available in headless mode.');
        }

        $options = [
            'landscape' => $landscape,
            'displayHeaderFooter' => $displayHeaderFooter,
            'printBackground' => $printBackground,
            'scale' => $scale,
            'paperWidth' => $paperWidth,
            'paperHeight' => $paperHeight,
            'marginTop' => $marginTop,
            'marginBottom' => $marginBottom,
            'marginLeft' => $marginLeft,
            'marginRight' => $marginRight,
            'pageRanges' => $pageRanges,
            'ignoreInvalidPageRanges' => $ignoreInvalidPageRanges,
            'headerTemplate' => $headerTemplate,
            'footerTemplate' => $footerTemplate
        ];

        $response = $this->page->send('Page.printToPDF', $options);

        if (false === array_key_exists('data', $response) || false === $pdfData = base64_decode($response['data'])) {
            throw new \Exception('PDF could not be created.');
        }

        file_put_contents($filename, $pdfData);
    }

    /**
     * For more information see https://chromedevtools.github.io/devtools-protocol/tot/Page/#method-captureScreenshot
     *
     * @param string $filename
     * @param array $options
     * @throws \Exception
     */
    public function captureScreenshot($filename, $options = [])
    {
        $response = $this->page->send('Page.captureScreenshot', $options);

        if (false === array_key_exists('data', $response) || false === $imageData = base64_decode($response['data'])) {
            throw new \Exception('Screenshot could not be created.');
        }

        file_put_contents($filename, $imageData);
    }
}
