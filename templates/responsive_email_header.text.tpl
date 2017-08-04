New York State Senate Senator {{$senator.full_name}}
{{if $senator.role}}
{{$senator.role}}
{{/if}}
({{','|implode:$senator.party}})  {{$senator.senate_district_ordinal}} Senate District
{{$senator.url}}

{{if $senator.social_media}}
Follow me on:
{{foreach from=$senator.social_media item=smsite}}
{{$smsite->name|capitalize}}: {{$smsite->url}}
{{/foreach}}
{{/if}}


