New York State Senate Senator {{$senator.full_name}}
({{', '|implode:$senator.party}}) {{$senator.senate_district_ordinal}} Senate District
{{$senator.url}}

Follow me on:
{{foreach from=$senator.social_media item=smsite name=socialicons}}
{{$smsite->name|capitalize}}: {{$smsite->url}}
{{/foreach}}


