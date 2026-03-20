{* HEADER *}

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}
<div class="content">

  <div class="crm-form-block">

    <div id="crm-main-content-wrapper" style="padding:1em">

      {*
          <crm-angular-js modules="nyssGreeting">
            <nyss-greeting-report message="Nate Hello"/>
          </crm-angular-js>
*}
      {if $count_individuals == 0}
        <p><strong>{ts escape='html'}All greetings are complete.{/ts}</strong></p>
      {else}
        <p><strong>{ts escape='html'}There are greetings that need to be generated.{/ts}</strong></p>
      {/if}
      <p style="display:inline-block;">{ts escape='html' plural='%count individuals with missing greetings' count=$count_individuals}%count individual with missing greetings{/ts}.</p>
      {help id="generate_greetings" title="Generate Greetings" file="CRM/NyssGreeting/Form/GenerateGreetings"}
    </div>
    {* FOOTER *}
    <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>

  </div>
</div>

