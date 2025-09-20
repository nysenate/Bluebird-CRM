<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifier
 */

function smarty_modifier_join($values, $separator = '')
{
	if (is_array($separator)) {
		return join((string) ($values ?? ''), (array) $separator);
	}
	return join((string) ($separator ?? ''), (array) $values);
}
