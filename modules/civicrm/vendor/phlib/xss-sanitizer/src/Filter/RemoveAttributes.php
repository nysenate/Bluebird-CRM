<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\AttributeFinder;
use Phlib\XssSanitizer\FilterInterface;
use Phlib\XssSanitizer\TagFinder;

/**
 * @package Phlib\XssSanitizer
 */
class RemoveAttributes implements FilterInterface
{
    private TagFinder\ByAttribute $tagFinder;

    private AttributeFinder $attributeFinder;

    public function __construct(array $attributes = [])
    {
        // source: https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Event_Handlers
        $attributes = [
            'fscommand',
            'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut',
            'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload',
            'onbeforeupdate', 'onbegin', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu',
            'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete',
            'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragleave', 'ondragenter', 'ondragover',
            'ondragdrop', 'ondragstart', 'ondrop', 'onend', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish',
            'onfocus', 'onfocusin', 'onfocusout', 'onhashchange', 'onhelp', 'oninput', 'onkeydown', 'onkeypress',
            'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmediacomplete', 'onmediaerror', 'onmessage',
            'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup',
            'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onoffline', 'ononline', 'onoutofsync', 'onpaste',
            'onpause', 'onpopstate', 'onprogress', 'onpropertychange', 'onreadystatechange', 'onredo', 'onrepeat',
            'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onresume', 'onreverse', 'onrowsenter', 'onrowexit',
            'onrowdelete', 'onrowinserted', 'onscroll', 'onseek', 'onselect', 'onselectionchange', 'onselectstart',
            'onstart', 'onstop', 'onstorage', 'onsyncrestored', 'onsubmit', 'ontimeerror', 'ontrackchange', 'onundo',
            'onunload', 'onurlflip',
            'seeksegmenttime', ...$attributes,
        ];

        $this->tagFinder = new TagFinder\ByAttribute($attributes);
        $this->attributeFinder = new AttributeFinder($attributes);
    }

    /**
     * Filter unwanted attributes from tags
     *
     * This includes event handler attributes ('onload', 'onclick' etc.)
     * e.g. '<body onload="alert('XSS');">'
     */
    public function filter(string $str): string
    {
        $str = $this->tagFinder->findTags($str, function ($fullTag, $attributes): string {
            return $this->removeAttribute($fullTag, $attributes);
        });

        return $str;
    }

    /**
     * Removes unwanted attributes from a particular tag
     *
     * @param string $fullTag (e.g. '<a onclick="alert('XSS');">')
     * @param string $attributes (e.g. 'a onclick="alert('XSS');"')
     */
    private function removeAttribute(string $fullTag, string $attributes): string
    {
        $replacement = $this->attributeFinder->findAttributes($attributes, function (): string {
            return '';
        });

        return str_ireplace($attributes, $replacement, $fullTag);
    }
}
