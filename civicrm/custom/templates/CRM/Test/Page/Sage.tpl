{foreach name=outer from=$tests item=test}
	<h3>{$test.name}</h3>
	<div class="status">
		{assign var='output' value=$test.out}
		{foreach from=$output key=key item=val}
			<strong>{$key}</strong>: {$val}<br/>
		{/foreach}
	</div>
{/foreach}
