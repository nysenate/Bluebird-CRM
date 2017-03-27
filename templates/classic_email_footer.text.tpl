
---
New York State Senate
www.nysenate.gov

{{foreach from=$senator.offices item=offinfo name=offices}}
{{$offinfo->name}}:
{{$offinfo->street}}
{{$offinfo->city}}, {{$offinfo->province}} {{$offinfo->postal_code}}
{{$offinfo->phone}}

{{/foreach}}

