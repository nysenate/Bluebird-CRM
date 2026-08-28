<div id="bootstrap-theme" ng-app="contactlayout">

  <contact-layout-editor></contact-layout-editor>

  <div style="display:none;">
    <input id="cse-icon-picker" title="{ts escape='htmlattribute'}Choose Icon{/ts}"/>
  </div>
</div>
{* Since css files don't support translatable strings *}
{literal}
  <style>
    #cse-block-container .cse-layout-col .block-multiple:not(.collapsed):after {
      content: '+ {/literal}{ts}Multiple{/ts}{literal}';
    }
  </style>
{/literal}
