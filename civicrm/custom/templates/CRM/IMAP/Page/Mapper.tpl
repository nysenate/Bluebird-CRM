<div class="crm-content-block">
	<script>
		{literal}
		cj(document).ready(function(){
			{/literal}
    		first_names = {$first_names};
    		last_names = {$last_names};
    		cities = {$city_names};
    		{literal}

    		imapper_options = {
				'matchContains': false,
				'matchCase': false,
				'matchSubset': true, //Override the
				'minChars': 2,
				'scroll': true,
    		};

    		cj('#city').autocomplete(cities,imapper_options);
			cj('#first_name').autocomplete(first_names,imapper_options);
			cj('#last_name').autocomplete(last_names,imapper_options);

		});
		{/literal}
	</script>
	<div class="imapper-filter">
		<form>
		{*
		{$form.street_address.label}{$form.street_address.html}<br/>
		*}
		<div>{$form.city.label}{$form.city.html}</div>
		<div>{$form.first_name.label}{$form.first_name.html}</div>
		<div>{$form.last_name.label}{$form.last_name.html}</div>
		{$form.state.label}{$form.state.html}<br/>
		</form>
	</div>
	<div class="imapper-messages">
        {foreach from=$messages item=message}
        	<div class="imapper-message" id="imapper_message_{$message.uid}">
        		<div class="imapper-date">{$message.date}</div>
        		<div class="imapper-subject">{$message.subject}</div>
        		<div class="imapper-clear"></div>
        	</div>
        {/foreach}
    </div>
	{*
    <div class="imapper-contacts">
    	{foreach from=$contacts item=contact}
    		<div class="imapper-contact" id="imapper_contact_{$contact.id}">
    			<div class="imapper-name">Name: {$contact.display_name}</div>
    		</div>
		{/foreach}
    </div>
	*}
</div>