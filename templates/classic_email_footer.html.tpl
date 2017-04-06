    </div>
    </td>
    </tr>
    <tr>
    <td align="center" valign="top">
    <table style="color:#707070; font-size:12px; line-height:125%;" border="0" cellpadding="20px" cellspacing="0" width="100%">
      <tr>
{{foreach from=$senator.offices item=offinfo name=offices}}
      <td valign="top" width="{{math equation="100/x" x=$smarty.foreach.offices.total format="%d"}}%"><strong>{{$offinfo->name}}:</strong>
      <br/>{{$offinfo->street}}
{{if $offinfo->additional}}
      <br/>{{$offinfo->additional}}
{{/if}}
      <br/>{{$offinfo->city}}, {{$offinfo->province}} {{$offinfo->postal_code}}
      <br/><a href="tel:{{$offinfo->phone}}" target="_blank" style="text-decoration:none;">{{$offinfo->phone}}</a>
      </td>
{{/foreach}}
      </tr>
    </table>
    </td>
    </tr>
    <tr style="background-color:#D8E2EA;">
    <td><a href="http://www.nysenate.gov/" target="_blank"><img src="http://{{$bbcfg.servername}}/data/{{$bbcfg.shortname}}/pubfiles/images/template/footer.png" alt="New York State Senate seal"/></a></td>
    </tr>
  </table>
  </td>
  </tr>
</table>
</center>
</body>
</html>
