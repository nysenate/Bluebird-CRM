
---
New York State Senate
www.nysenate.gov

{{foreach from=$senator.offices item=offinfo name=offices}}
{{$offinfo->name}}:
{{$offinfo->street}}
{{if $offinfo->additional}}
{{$offinfo->additional}}
{{/if}}
{{$offinfo->city}}, {{$offinfo->province}} {{$offinfo->postal_code}}
{{$offinfo->phone}}
{{if $offinfo->fax}}
{{$offinfo->fax}}
{{/if}}
{{/foreach}}

